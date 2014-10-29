githubManagement
================

TODO
----
- respond on github webkhook
- create github hook with api
- import builder simplifié
- locals files are in a folder on the test server

Architecture
------------
- `builder` : default project builder
- `hooks/default.php` : default web hook handler
- `hooks/projects` : project specific web hook handlers
- `locals` : projects locals files
- `repos-clones` : temporary repos clones (clones for building then copying to `web`)
- `tools` : tools (scripts, etc.)
- `web` : web projects exports (ie the test web server root)