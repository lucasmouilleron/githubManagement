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
- `processors` : the processors
- `configs` : projects config files
- `locals` : projects locals files
- `repos-clones` : temporary repos clones (clones for building then copying to `web`)
- `api` : the public API
- `locks`: the locks folder

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
### Main
- The main processor, always called first
- Variables availables to sub processors are listed in the commented header section

### Lock
- Forbids concurrent processings untill the current one is finished
- Auto released when the processing is finished

### Clone
- Clones (or pull + reset) the repo to the `repos-clones` folder

### Locals
- Copies project local files from `locals/owner/repo/ENV` to `repos-clones/owner/repo/$localsPath`
- Convenient if some parameters are diffrent from one env to the other
- In this case, isolate these parameters in some files which are copied depending on what ENV is targeted

### Build
- Runs a build file for the project
- The build file is an executable script or app (must be runnable)

### Deploy-files
- Sends the files to the remove env ENV
- The `$deployFolder` is sent to the `$envBasePath`
- Make sure you sent your public key to the remove ENV (for rsync to run not interactively)

### Deploy-db
- Sends and runs the DB on the remote env ENV
- For flexibility reason, the string `--db` must be included in the tag name for the processor to run
- Make sure you sent your public key to the remove ENV (for scp to run not interactively)

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
	- `API_URL` is where your api is publicly http available

TODO
----
- log cleanup