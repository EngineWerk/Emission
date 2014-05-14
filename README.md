Emission
====
[![Build Status](https://travis-ci.org/EngineWerk/Emission.svg?branch=master)](https://travis-ci.org/EngineWerk/Emission)

Fast store and share files easily.
Take advantage of LAN bandwidth for fast file sharing.  

###Features
- Large files (~GB) upload via browser
- Resumable upload (using [Resumable.js](https://github.com/23/resumable.js))
- Upload images via clipboard paste (Ctrl+v)

###Requirements
PHP >= 5.3.3

###Installation
Clone repository.

    git clone https://github.com/EngineWerk/Emission.git emission
    
Install dependencies via [composer](https://getcomposer.org/download/).

    composer install

You will be asked to provide:

- Database connection parameters
- Google OAuth 2.0 parameters (Web application Client ID, Client secret). Create with [Google Cloud Console](https://cloud.google.com/console/project)

Follow:

- Symfony`s [permissions setup](http://symfony.com/doc/current/book/installation.html#configuration-and-setup)
- webserver [configuration](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html)
    
Create database tables.

    app/console dotrine:schema:update --force

Generate user invitation code.

    app/console invitation:add user@acme.com