Emission
========================

Fast store and share files easily.
Take advantage of LAN bandwidth for fast file sharing.  

Features
========
- Large files (~GB) upload via browser
- Chunked, resumable upload (using [Resumable.js](https://github.com/23/resumable.js))
- Upload images via clipboard paste (Ctrl+v)

Requirements
========
PHP >= 5.3.3

Installation
========
Clone repository.

    git clone https://github.com/EngineWerk/Emission.git emission
    
Install dependencies via composer.

    composer install

You will be asked to provide:

- Database connection parameters
- Google OAuth 2.0 parameters (Web application Client ID, Client secret). Create with [Google Cloud Console](https://cloud.google.com/console/project)
    
Create database tables.

    app/console dotrine:schema:update --force

Generate user invitation codes.

    app/console invitation:add user@acme.com