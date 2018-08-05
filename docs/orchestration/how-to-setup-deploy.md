# How to set  up your own CI or deploy using Kubernetes
While our deployment and infrastructure are defined as a code. You can use it for your own projects CI and deployment
with minimum effort.

## Prerequisites
Even though manifests and deployment is part of a repository. There are some prepositions that need to be ready
for deployment or continuous integration.

### CI
CI builds all branches from the repository.

Right now we use [CircleCI](https://circleci.com/) as our CI that's why you can find the `.circleci` folder in your project.

If you want to use different CI, it should not be a problem, you just need to rewrite `.circleci/config.yml` to format that your CI understands,
commands themselves should not be different from one written in our config.

#### Configure environments variables
In our `.circleci/config.yml` we got a couple of variables that are individual for each project.

| Environment variable           | Explanation                                                                            
| ------------------------------ | ------------ 
| **$DOCKER_USERNAME**           | your login to docker hub
| **$DOCKER_PASSWORD**           | your password to docker hub
| **$KUBERNETES_CONFIG_FILE**    | content of kube config ~/.kube/config located in the home folder of user running kubernetes processes
| **$DEVELOPMENT_SERVER_DOMAIN** | domain name of your server where you want to deploy your application

Set these variables as env variables in your CI

### Node server
The application needs to be deployed somewhere that is why we need a server that will carry all of our application containers.

We highly recommend contacting some cluster provider which will configure that for you. If you want to configure it
by yourself follow these instructions.

#### Install node server
Our server is running CentOS 7. Following commands are for centOS and may be different on other distributions.

Install repositories required by docker and kubernetes:

```
yum install -y yum-utils device-mapper-persistent-data lvm2
```

Install Docker required by Kubernetes to work and enable it as a service:

Note: *Enabling Docker as a service causes that Docker is always started even after the restart of the system*
```
yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo

yum install -y docker-ce

systemctl enable docker && systemctl start docker
```

Kubernetes works with iptables rules for setting up traffic between pods. 
That's why there is a need to turn off some security processes to assure that Kubernetes will work properly.

Disable security processes that are in conflict with Kubernetes.

```
setenforce 0

swapoff -a 
```

Install Kubernetes and tools for controlling it (Kubelet, Kubectl, Kubeadm):
```
yum install -y kubelet kubeadm kubectl --disableexcludes=kubernetes
```

Enable Kubelet as a service so it starts with system reboot
```
systemctl enable kubelet && systemctl start kubelet
```
As was said before, Kubernetes works with iptables so we need to clean already created rules in iptables that can be in conflict
with Kubernetes.

Fix possible IP tables issues:
```
cat <<EOF >  /etc/sysctl.d/k8s.conf
net.bridge.bridge-nf-call-ip6tables = 1
net.bridge.bridge-nf-call-iptables = 1
EOF
sysctl --system
```

Create a cluster on your server and define IP range for pods.
```
kubeadm init --pod-network-cidr=192.168.0.0/16
```

Allow your user to use `kubectl` commands.
Choose user that will be running all Kubernetes processes on your server. Please make sure that this
user matches with user used for logging to ssh in `.circleci/config.yml`. For example we created for this
purposes user called `www-data` so we will use him as a example:

```
mkdir -p /home/www-data/.kube
cp -i /etc/kubernetes/admin.conf /home/www-data/.kube/config
chown www-data:www-data /home/www-data/.kube/config
```

Start Calico networking plugin for enabling communication between pods and nodes.
```
kubectl apply -f https://docs.projectcalico.org/v3.1/getting-started/kubernetes/installation/hosted/kubeadm/1.7/calico.yaml
```

Make our server a master node:
```
kubectl taint nodes --all node-role.kubernetes.io/master-
```

#### Bring traffic to pods
Every node needs an entrypoint which knows where are pods located and knows how to bring traffic to them.

There are many solutions for that in [official documentation of Kubernetes](https://kubernetes.io/docs/concepts/), but we highly recommend to implement Ingress Controller. Which listens to domain names, same as for example nginx and forward traffic
to containers.

There is nice [article](https://akomljen.com/kubernetes-nginx-ingress-controller/) about implementing Ingress Controller
