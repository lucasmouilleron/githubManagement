<?php

////////////////////////////////////////////////////////////////
require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/config.php";

////////////////////////////////////////////////////////////////
function getGithub($path) {
	$request = Requests::get(GITHUB_API_URL."/".$path, array("Accept"=>GITHUB_API_VERSION_HEADER), array());
	var_dump($request);
}

?>