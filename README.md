Emission
====
Web browser file sharing application.

[![Build Status](https://travis-ci.org/EngineWerk/Emission.svg?branch=master)](https://travis-ci.org/EngineWerk/Emission)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/798573d8-39d8-4133-8056-3c457a65f3e6/mini.png)](https://insight.sensiolabs.com/projects/798573d8-39d8-4133-8056-3c457a65f3e6)

###Features
- Large files (~GB) upload
- Resumable upload (using [Resumable.js](https://github.com/23/resumable.js))
- Upload images via clipboard paste (Ctrl+v)

###Requirements
PHP >= 5.6.29

###Installation
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
    
Generate user invitation code.

    app/console invitation:add user@acme.com

###Virtual Machine for testing / development
[VirtualBox - Vagrant](https://github.com/EngineWerk/EmissionVM)

Create directory vm-emission, clone Vagrant setup into

    git clone https://github.com/EngineWerk/EmissionVM.git

then clone Emission code

    git clone https://github.com/EngineWerk/Emission.git


