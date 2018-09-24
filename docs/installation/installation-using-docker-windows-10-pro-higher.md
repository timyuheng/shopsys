# Installation Using Docker for Windows 10 Pro and higher
*Virtualization technology (e.g. Docker, Vagrant) is generally significantly slower on Windows than on UNIX operating systems. Running Shopsys Framework on Windows via Docker can cause performance issues such as page load taking a several seconds (~20s on Windows with 4GB RAM and Intel i5; ~0,5s on Linux or Mac OS). We are still trying to improve this situation so please stay tuned.*

## Supported systems
- Windows 10 Pro
- Windows 10 Enterprise
- Windows 10 Education

## Requirements
* [GIT](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
* [PHP](http://php.net/manual/en/install.windows.php)
* [Composer](https://getcomposer.org/doc/00-intro.md#installation-windows)
* [Docker for Windows](https://docs.docker.com/docker-for-windows/install/)
* [Docker-sync for Windows](http://docker-sync.io/)

## Steps
### 1. Create new project from Shopsys Framework sources
After WSL installation, use linux console for each command.
```
composer create-project shopsys/project-base --stability=beta --no-install --keep-vcs
cd project-base
```

*Notes:* 
- *The `--no-install` option disables installation of the vendors - this will be done later in the Docker container.*
- *The `--keep-vcs` option initializes GIT repository in your project folder that is needed for diff commands of the application build and keeps the GIT history of `shopsys/project-base`.*
- *The `--stability=beta` option enables you to install the project from the last beta release. Default value for the option is `stable` but there is no stable release yet.*

### 2. Create docker-compose.yml and docker-sync.yml file
Create `docker-compose.yml` from template [`docker-compose-win.yml.dist`](../../project-base/docker/conf/docker-compose-win.yml.dist).
```
cp docker/conf/docker-compose-win.yml.dist docker-compose.yml
```

Create `docker-sync.yml` from template [`docker-sync-win.yml.dist`](../../project-base/docker/conf/docker-sync-win.yml.dist).
```
cp docker/conf/docker-sync-win.yml.dist docker-sync.yml
```

#### Set the Github token in your docker-compose.yml file
Shopsys Framework includes a lot of dependencies installed via Composer.
During `composer install` the GitHub API Rate Limit is reached and it is necessary to provide GitHub OAuth token to overcome this limit.
This token can be generated on [Github -> Settings -> Developer Settings -> Personal access tokens](https://github.com/settings/tokens/new?scopes=repo&description=Composer+API+token)
Save your token into the `docker-compose.yml` file.
Token is located in `services -> php-fpm -> build -> args -> github_oauth_token`.

### 3. Grant Docker access to your files
- Right click Docker icon in your system tray and choose `Settings...`
- From left menu choose `Shared Drives`
- Set your system drive including Shopsys Framework repository as `Shared` (check the checkbox)
- Click on `Apply`
- You will be prompted for your Windows credentials

### 4. Compose Docker container
On Windows you need to synchronize folders using docker-sync.
Before starting synchronization you need to create a directory for persisting Vendor data so you won't lose it when the container is shut down.
```
mkdir -p vendor
docker-sync start
```

Then rebuild and start containers
```
docker-compose up -d
```

### 5. Setup the application
[Application setup guide](installation-using-docker-application-setup.md)
