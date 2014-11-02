githubManagement
================

Features
--------
- Github projects management and processing
- Relies on Github user roles and access system
- Small API to create tags on master, respond to Githib web hooks, etc.
- Processors : per project configuration of standard (clone, build, send, etc.) and custom processors to run against a tag hook
- Miscs : loging

Architecture
------------
- `api` : the public API
- `configs` : projects config files
- `envs-assets` : projects env specific assets
- `locks`: the locks folder
- `processors` : the processors
- `clones` : temporary repos clones (clones for building then copying to `web`)

Users management
----------------
- Relies on Github user roles and access system
- Users must have a valid Github account
- To be able to clone and create tags (with the API or not), a user must be granted access to the repo on Github

Triggering
----------
- Tag name syntax : `.*--deploy-ENV.*`
- ENVS are defined in `$PROCESSOR_AVAILABLE_ENVS` in `api/config.php`
- Wheter the tag is created from the API or from a local repo (and then pushed), the processing will be triggered
- For the tag to trigger processor, a webhook must be set on the repo. Our small API can take care of it. cf [Route 'Init processing'](#routes)

Processors
----------
- The available processors are located in `processors`
- Processors docs can be found in the headers of the `processors/*` files
- The `main` processor is called first and calls the next processors configured in `configs/owner/repo.json->processors`

API
---
### Overview
- The API is used to respond to the Github webhook
- It can be used as well to init the webhook config or create tags (without clone the project !)
- Most API calls require a Github user access token : https://github.com/settings/applications#personal-access-tokens
- `GITHUB_MASTER_TOKEN` user must have access to all repos

### Routes
- List repos : 
	- `GET /repos`
	- List all managed repos
- Create a tag :
	- `POST /repos/:owner/:repo/tag` :
	- Post params : tag-revision, tag-name, tag-message
	- Get param : github-token (the github token of the user)
	- Example : cf `tests/api-tests.php`
- Init processing : 
	- `POST /repos/:owner/:repo/hook/init`
	- Post params : none
	- Get param : github-token (the github token of the user) (use `GITHUB_MASTER_TOKEN` if called from PHP for administration purpose)
	- Example : cf `tests/api-tests.php`
- Github hook : 
	- `POST /repos/:owner/:repo/hook/:token`
	- Called by github on tag create

Tests
-----
- API tests : `http://public.url.to.the.api.folder.com/tests/api-tests.php`
- Processors tests : `php tests/processors-test.php`
- In production mode, use a htaccess or remove this folder
- Debug tip : `tail -f processing-owner-repo.log` and `tail -f api.log` 
- Debut tip 2 : some `exec` error will go in the apache log : `tail -f /var/log/apache2/error_log`

Notes
-----
- Not yet compatible with Github entreprise
- Deployment is purposly simple. Other methods could involve branching and pushing tags to specific branches.

Install requirements
--------------------
- `curl -sS https://getcomposer.org/installer | php`
- `mv composer.phar /usr/local/bin/composer`
- Apache, mod_rewrite
- PHP 5.3

Install
-------
- `cd api && composer install`
- `mv api/config.php.sample api/config.php`
- edit the `api/config.php` file :
	- change `API_PRIVATE_KEY` to a random string
	- set `APACHE_HOME` to an existing folder and make sure Apache can write in it
	- set `ENV_PATH` so it contains all binaries that might be called from processors (rsync, scp, git), or build files (npm, grunt, etc.)
	- `API_URL` is where your api is publicly http available
	- set `DEBUG` to `false` in production mode
- Test : `http://public.url.to.the.api.folder.com`

TODO
----
- better capture exec ouput un tools->run()
- log cleanup