<?php

////////////////////////////////////////////////////////////////
require_once __DIR__."/libs/tools.php";

////////////////////////////////////////////////////////////////
// PARAMS INIT
////////////////////////////////////////////////////////////////
$owner = GITHUB_DEFAULT_OWNER;
$repo = null;
$hookPath = HOOKS_DEFAULT_HOOK;

if(count(@$argv)>1) $repo = $argv[1];
if(count(@$argv)>2) $owner = $argv[2];
if(isset($_GET["owner"])) $owner = $_GET["owner"];
if(isset($_GET["repo"])) $owner = $_GET["repo"];
if(!isset($repo) || !isset($owner)) die("Wrong parameters, usage : php ".basename(__FILE__)." REPO* OWNER".PHP_EOL);

////////////////////////////////////////////////////////////////
// IGNITION
////////////////////////////////////////////////////////////////
if(file_exists(HOOKS_PATH."/projects/".$repo.".php")) $hookPath = "projects/".$repo.".php";

$data = '{"name": "web", "active": true, "events": ["push"], "config": {"url": "'.HOOKS_URL.$hookPath.'", "content_type": "json"}}';
$result = postGithub("repos",$owner,$repo,"hooks",$data);

if($result === false) die("Can't add hook".PHP_EOL);
printLine("Hook created for repo ".$owner."/".$repo." : ".json_encode($result));

?>