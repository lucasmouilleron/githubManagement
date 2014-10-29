githubManagement
================

TODO
----
- respond on github webkhook
- create github hook with api
- import builder simplifié
- locals files are in a folder on the test server

Install requirements
--------------------
- `curl -sS https://getcomposer.org/installer | php`
- `mv composer.phar /usr/local/bin/composer`

Install
-------
- `composer install`
- `mv scripts/config.php.sample scripts/config.php`

Architecture
------------
- `builder` : default project builder
- `hooks/default.php` : default web hook handler
- `hooks/projects` : project specific web hook handlers
- `locals` : projects locals files
- `repos-clones` : temporary repos clones (clones for building then copying to `web`)
- `scripts` : scripts
- `web` : web projects exports (ie the test web server root)