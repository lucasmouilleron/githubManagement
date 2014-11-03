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

Install requirements
--------------------
- `curl -sS https://getcomposer.org/installer | php`
- `mv composer.phar /usr/local/bin/composer`
- Apache, mod_rewrite
- PHP 5.3
- Unix system (some processors use `cp`, `rsync`, `ssh`, etc.)

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

Notes
-----
- Not yet compatible with Github Entreprise (maybe some API differences)
- This management method is agnostic of pull requests and branches. It helps deploying the master branch when you want (see [Triggering](#triggering)
- For the record, example of pull requests process : 
	- From contributor / employee / contractor :
		- Fork the repo (if pull only acess, otherwise, clone)
		- Create a branch for a feature : `git branch -b the-feature`
		- Commit and push branch to remote : `git add . && git commit -m "message" && git push`
		- Create pull request : `git pull-request`
		- Wait for feedback
	- From maintainer : 
		- Merge the branch : `git fetch && git checkout master && git merge the-feature`
		- Push it back  : `git branch -d the-feature && git push`
	- Contributor and maintainer can be the same person. No need for pull-requests in this case
	- On Github Entreprise, it is possible to give some users pull only access. They contribute to the repo with pull-requests only

Docs
----
- [Using branches](https://www.atlassian.com/git/tutorials/using-branches/git-branch)
- [Pull requests goog practices](http://codeinthehole.com/writing/pull-requests-and-other-good-practices-for-teams-using-github/)
- [Github Flow](https://guides.github.com/introduction/flow/index.html)
- [Hib tool](https://hub.github.com) (generate pull requests links)

TODO
----
- better capture exec ouput un tools->run()
- log cleanup