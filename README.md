githubManagement
================

TODO
----
- respond on github webkhook
- import builder simplifié
- locals files are in a folder on the test server

Install requirements
--------------------
- `curl -sS https://getcomposer.org/installer | php`
- `mv composer.phar /usr/local/bin/composer`

Install
-------
- `cd api && composer install`
- `mv api/config.php.sample api/config.php`

Architecture
------------
- `builder` : default project builder
- `hooks/default.php` : default web hook handler
- `hooks/projects` : project specific web hook handlers (hooks must be named `owner__repo.php`)
- `locals` : projects locals files
- `repos-clones` : temporary repos clones (clones for building then copying to `web`)
- `api` : the public API
- `web` : web projects exports (ie the test web server root)