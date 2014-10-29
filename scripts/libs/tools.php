<?php

////////////////////////////////////////////////////////////////
require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/../config.php";

////////////////////////////////////////////////////////////////
function getGithub($path) {
	$request = Requests::get(GITHUB_API_URL.$path, array("Accept"=>GITHUB_API_VERSION_HEADER,"Authorization"=>"token ".GITHUB_API_TOKEN));
	if($request->status_code != 200) {
		return false;
	}
	else {
		return (json_decode($request->body));
	}
}

////////////////////////////////////////////////////////////////
function postGithub($path, $data) {
	$request = Requests::post(GITHUB_API_URL.$path, array("Accept"=>GITHUB_API_VERSION_HEADER,"Authorization"=>"token ".GITHUB_API_TOKEN), $data);
	if($request->status_code != 200) {
		return false;
	}
	else {
		return (json_decode($request->body));
	}
}

?>