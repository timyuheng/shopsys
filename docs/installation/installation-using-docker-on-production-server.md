# Installation Using Docker on Production Server

This guide shows how to install and configure production server applications needed for running [shopsys/project-base](https://github.com/shopsys/project-base).
We use docker containers for `php` applications and natively installed applications like `postgres`, `elasticsearch`, `redis` for persisting data without loosing them after `project-base` deployments.

## Server Setup

CentOS is very common OS for production servers so we use it on our production server.

### Docker

First we install Docker.
```
yum install docker-ce
```
We set docker to run as a service.
```
systemctl enable docker
systemctl start docker
```

### Firewall

It is very important to have our containers inaccessible from outside.
For that purpose we need to update `firewalld` configuration with these commands,
because Docker overrides `firewalld` and publishes ports on the server by default.
```
firewall-cmd --permanent --direct --add-chain ipv4 filter DOCKER
firewall-cmd --permanent --direct --add-rule ipv4 filter DOCKER 0 ! -s 127.0.0.1 -j RETURN
```

### Nginx

Let's presume that we want to have our site running on `HTTPS` protocol and everything that concerns domain and its `certificates` is already setup.
[Nginx atricle](https://www.nginx.com/blog/nginx-https-101-ssl-basics-getting-started/#HTTPS) provides us with some helpful information about the setup.
Only thing that is missing is to connect the domain to application that runs in docker containers via port 8000 on 127.0.0.1 ip address.
First we need to allow Nginx to connect to sockets by executing a command in shell console.
 ```
setsebool httpd_can_network_connect on -P
```
Then we add location block into `/etc/nginx/conf.d/<YOUR_DOMAIN_HERE>.conf` into server block so the config looks like this.
 ```
server {
    listen 443 http2 ssl;

    server_name <YOUR_DOMAIN_HERE>;

    root /usr/share/nginx/html;

    ssl_certificate /etc/ssl/linux_cert+ca.pem;
    ssl_certificate_key /etc/ssl/<YOUR_DOMAIN_HERE>.key;

    ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
    ssl_prefer_server_ciphers on;
    ssl_ciphers 'EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH';

    location / {
       proxy_set_header Host $host;
       proxy_set_header X-Real-IP $remote_addr;
       proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
       proxy_set_header X-Forwarded-Proto $https;
       proxy_pass http://127.0.0.1:8000;
    }
}

server {
    listen 80;
    server_name <YOUR_DOMAIN_HERE>;

    return 301 https://<YOUR_DOMAIN_HERE>$request_uri;
}
```

### Database

We need PostgresSQL in version 10.5 installed. To get this done we need to add repository to our server and then install PostgresSQL server with PostgresSQL client.
```
rpm -Uvh https://yum.postgresql.org/10/redhat/rhel-7-x86_64/pgdg-centos10-10-2.noarch.rpm
yum install postgresql10-server postgresql10 postgresql10-contrib
```
Next we initialise PostgresSQL database.
 ```
/usr/pgsql-10/bin/postgresql-10-setup initdb
```
We need to allow access to database from docker network that will operate on 192.168.0.0 subnet by adding one line into the config file.
```
echo host all all 192.168.0.1/16 md5 >> /var/lib/pgsql/10/data/pg_hba.conf
```
We edit configuration file `/var/lib/pgsql/10/data/postgresql.conf` of postgresql to match application needs based on our [postgres.conf](../../project-base/docker/postgres/postgres.conf).
We also allow to establish connection via localhost and 192.168.0.0 subnet by modifying `postgresql.conf`.
```
listen_addresses = '127.0.0.1, 192.168.0.1'
```
Now we register and launch PostgresSQL server as a service.
```
systemctl start postgresql-10
systemctl enable postgresql-10
```
Next with help of default postgres administration user we create new database user with login root. You will be prompted to enter password for newly created user root.
```
su - postgres -c "createuser --createdb --superuser --pwprompt root"
```
Now we need to allow connection between docker containers and database via local network and PostgresSQL port.
```
echo <<EOT | cat >> /usr/lib/firewalld/services/postgresql.xml
<?xml version=""1.0"" encoding=""utf-8""?>
<service>
  <short>PostgreSQL</short>
  <description>PostgreSQL Database Server</description>
  <port protocol=""tcp"" port=""5432""/>
  <destination ipv4=""192.168.0.1/16""/>
</service>
EOT
firewall-cmd --permanent --zone=public --add-service=postgresql
firewall-cmd --reload
```

### Redis 4.0

For storing cache and sessions we need to [install](https://redis.io/download#installation) Redis server.
In addition we want redis server to operate also on 192.168.0.0 subnet so we will modify configuration file that is set by default in folder `/etc/redis/`.
```
bind 127.0.0.1 192.168.0.1
```
After configuration change, configuration need to be reloaded by service restart.
```
service redis_6379 restart
```
Now we just need to allow communication between docker containers and Redis server.
```
"echo <<EOT | cat >> /usr/lib/firewalld/services/redis.xml
<?xml version=""1.0"" encoding=""utf-8""?>
<service>
  <short>Redis</short>
  <description>Cache tool.</description>
  <port protocol=""tcp"" port=""6379""/>
  <destination ipv4=""192.168.0.1/16""/>
</service>
EOT"
firewall-cmd --permanent --zone=public --add-service=redis
firewall-cmd --reload
```

### Elasticsearch

First we need to install Java SDK environment.
```
yum install java-1.8.0-openjdk
```
Next we [install](https://www.elastic.co/guide/en/elasticsearch/reference/current/rpm.html) elasticsearch and allow connecting to it via local network.
```
echo <<EOT | cat >> /usr/lib/firewalld/services/elasticsearch.xml
<?xml version=""1.0"" encoding=""utf-8""?>
<service>
  <short>Elasticsearch</short>
  <description>Elasticsearch is a distributed, open source search and analytics engine, designed for horizontal scalability, reliability, and easy management.</description>
  <port protocol=""tcp"" port=""9300""/>
  <port protocol=""tcp"" port=""9200""/>
  <destination ipv4=""192.168.0.0/16 ""/>
</service>
EOT
firewall-cmd --permanent --zone=public --add-service=elasticsearch
firewall-cmd --reload
```
We will also make elasticsearch server listen on 192.168.0.0 subnet by modifying `/etc/elasticsearch/elasticsearch.yml`.
```
network.host: 0.0.0.0
service elasticsearch restart
```

### Docker Image Building

We want to deploy changes that we made in git repository to production server. We deploy the `php-fpm` container that encapsulates `project-base` repository.
First we need to clone `project-base` repository with the specific tag or commit hash or HEAD.
```
git clone https://github.com/shopsys/project-base.git
git checkout $commit_hash
```
Then we setup environment for building the image with the correct data for production server.
Now we create configuration file for domains.
```
echo $'domains:
    -   id: 1
        name: <YOUR_DOMAIN_NAME_HERE>
        locale: en
' > app/config/domains.yml
```
For each domain we need to create config with domain url.
```
echo $'domains_urls:
    -   id: 1
        url: https://<YOUR_DOMAIN_HERE>
' >  app/config/domains_urls.yml
```
We want to have https domain, so we also need to modify [nginx.conf](../../project-base/docker/nginx/nginx.conf)
```
sed -i -r 's/(fastcgi_param HTTPS )off/\1on/' project-base/docker/nginx/nginx.conf
```
After the `project-base` is setup correctly, we launch the build of php-fpm container by docker build command.
```
docker build \
    -f ./docker/php-fpm/Dockerfile-production \
    -t production-php-fpm \
    --compress \
    --build-arg www_data_uid=1000 --build-arg www_data_gid=1000 \
    --build-arg github_oauth_token=PERSONAL_ACCESS_TOKEN_FROM_GITHUB \
    .
```
Replace the `PERSONAL_ACCESS_TOKEN_FROM_GITHUB` string by the token generated on [Github -> Settings -> Developer Settings -> Personal access tokens](https://github.com/settings/tokens/new?scopes=repo&description=Composer+API+token).
There are also settings for user id and group id under who the ownership of files will be because php-fpm execution needs workers that are not allowed to runs under root user group.
With `f` parameter we set path to Dockerfile that builds image.
With `t` parameter we set the name of built image.

If we are building the image on different server than production server, we can push built image into docker repository of production server via ssh.
We use `-oStrictHostKeyChecking=no` argument to have ssh connection without the prompt that asks about adding target server record into `known_hosts` ssh configuration.
We also want to establish connection to the server without prompting for password so we will use [key exchange method](http://sshkeychain.sourceforge.net/mirrors/SSH-with-Keys-HOWTO/SSH-with-Keys-HOWTO-4.html).
```
docker save production-php-fpm | gzip | ssh -oStrictHostKeyChecking=no -i <PRIVATE_KEY_PATH> root@<YOUR_DOMAIN_HERE> 'gunzip | docker load'
```

### Deployment On Production Server

#### First Setup Deploy

Now we need to copy [`docker-compose.prod.yml.dist`](../../project-base/docker/conf/docker-compose.prod.yml.dist) into folder on the production server as `docker-compose.yml`. 
After the image is in the repository of the production server we create docker containers and run phing target `build-new`,
to build application for production with clean DB and base data.
We use parameter `-p` to specify the name of the project and prefix for the volumes so these will be easily accessible.
There are named volumes created under path `/var/lib/docker/volumes/` and one persisted folder `production-content` in `PATH_TO_DIRECTORY_WHERE_IS_DOCKER_COMPOSE_YML_STORED` for all uploaded images and generated files that should not be removed.
We create persited folder with correct owner and symlink to web folder for static files
```
cd PATH_TO_DIRECTORY_WHERE_IS_DOCKER_COMPOSE_YML_STORED (e.g. /var/www/html)
mkdir /var/www/production-content
chown -R $(id -un): /var/www/production-content
ln -s /var/lib/docker/volumes/production_web-volume/_data/ web
```
and start containers with docker-compose.
```
docker-compose -p production up -d
docker-compose -p production exec php-fpm ./phing build-new
```

Now the docker container should be running.
We want to setup scheduler for execution of cron jobs by adding one line into `/etc/crontab` file.
Cron job is executed every 5 minutes in `php-fpm` container.
```
*/5 * * * * /usr/bin/docker exec production-php-fpm php phing cron
```

We also want to secure our administration by changing passwords for `superadmin` and `admin` users by command
```
docker-compose -p production exec php-fpm php bin/console shopsys:administrator:change-password superadmin
docker-compose -p production exec php-fpm php bin/console shopsys:administrator:change-password admin
```

Now we go to `/admin/dashboard/` and fulfill all requests that are demanding for us by red colored links.

#### Next Deploys

To preserve created data we need to use phing target `build` that consists of two commands.
* `build-deploy-part-1-db-independent` no maintenance page is needed during execution of this command
* `build-deploy-part-2-db-dependent` maintenance page is needed if there exist new database migrations

```
cd PATH_TO_DIRECTORY_WHERE_IS_DOCKER_COMPOSE_YML_STORED (e.g. /var/www/html)
docker-compose -p production up  -d
docker-compose -p production exec php-fpm ./phing build
```

## Conclusion

We have now running `project-base` based on docker containers and natively installed applications for storing persisted data on production server.
There is no risk of loosing data with new deploys of the `project-base`.
