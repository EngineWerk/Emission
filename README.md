Emission
====
Web browser file sharing application.

[![Build Status](https://travis-ci.org/EngineWerk/Emission.svg?branch=master)](https://travis-ci.org/EngineWerk/Emission)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/798573d8-39d8-4133-8056-3c457a65f3e6/mini.png)](https://insight.sensiolabs.com/projects/798573d8-39d8-4133-8056-3c457a65f3e6)

### Features
- Large files (~GB) upload
- Resumable upload (using [Resumable.js](https://github.com/23/resumable.js))
- Upload images via clipboard paste (Ctrl+V). Make screen shoot on OSX and keep it in clipboard cmd+ctrl+shift+3.

### Requirements
PHP >= 5.6.29

### Running on local system
Clone repository.

    git clone https://github.com/EngineWerk/Emission.git emission
    
Install dependencies via [composer](https://getcomposer.org/download/).

    cd ./emission
    composer install

You will be asked to provide:

- Database connection parameters
- Google OAuth 2.0 parameters (Web application Client ID, Client secret). Create with [Google Cloud Console](https://cloud.google.com/console/project) (required unless you don`t want to enable OAuth login)

Follow:

- Symfony`s [permissions setup](http://symfony.com/doc/current/book/installation.html#configuration-and-setup)
- webserver [configuration](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html)
    
Run database migrations.

    php app/console doctrine:migrations:migrate

Create new user.

    php app/console fos:user:create
    
Change user password

    php app/console fos:user:change-password

## Running within Virtual Machine

Clone repository.

    git clone https://github.com/EngineWerk/Emission.git emission
    
Run Virtual machine

    cd emission
    vagrant up # wait for box download and provision
    vagrant ssh    
    cd /var/www/emission
    composer install
    
    chmod a+X app/console
    
    php app/console doctrine:migrations:migrate --no-interaction
    php app/console fos:user:create vagrant vagrant@localhost vagrant --super-admin
    php app/console fos:user:activate vagrant
