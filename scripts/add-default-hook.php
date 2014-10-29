<?php

////////////////////////////////////////////////////////////////
require_once __DIR__."/libs/tools.php";

////////////////////////////////////////////////////////////////
$owner = GITHUB_DEFAULT_OWNER;
$repo = null;

// RUN FROM COMMAND LINE
if(isset($argv)) {
	if(count($argv)>1) $repo = $argv[1];
	if(count($argv)>2) $owner = $argv[2];
}

// RUN FROM HTTP
if(isset($_GET["owner"])) $owner = $_GET["owner"];
if(isset($_GET["repo"])) $owner = $_GET["repo"];

////////////////////////////////////////////////////////////////
$result = getGithub("/repos/".$owner."/".$repo);
var_dump($result);

?>