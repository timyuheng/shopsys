# Deploy your application using a code in your repository
In the past, the infrastructure of application and its deployment was completely decoupled from the development of the application.
The application was most of the time written by developers in one company and infrastructure with the deployment of the app was done by another company.

This led to many problems.

Developers did not exactly know on which infrastructure is their application running and their local infrastructure was different from 
one on the production server, this lead to the application not running correctly on production environment while being flawless on developers local computer.
 
And this was not bad only for developers of the application. If a customer decided to move to a higher performance server,
the whole infrastructure needed to be reconfigured to another server.

## Kubernetes
Kubernetes can use our docker infrastructure and images and deploy them as a whole onto servers. Since it uses docker images
used for local developments, you can be always sure that the application is deployed functional.

You can read how to setup kubernetes [here](./how-to-setup-deploy.md)

Our `docker-compose` infrastructure is written into kubernetes manifests in project base [k8s](https://github.com/shopsys/shopsys/tree/master/project-base/k8s) folder