<?php

////////////////////////////////////////////////////////////////
require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/config.php";

////////////////////////////////////////////////////////////////
function getGithub() {
	$routePatams = array();
	 foreach (func_get_args() as $param) {
        if(!is_array($param)) $routePatams[]=$param;
        else break;
    }
    $path = implode("/", $routePatams);
	if(DEBUG) printLine("HTTP getting URL : ".GITHUB_API_URL.$path);
	$request = Requests::get(GITHUB_API_URL.$path, array("Accept"=>GITHUB_API_VERSION_HEADER,"Authorization"=>"token ".GITHUB_API_TOKEN), array("verify"=>false));
	if($request->status_code != 200) {
		if(DEBUG) printLine("HTTP error : ".$request->body);
		return false;
	}
	else {
		return (json_decode($request->body));
	}
}

////////////////////////////////////////////////////////////////
function postGithub() {
	$data = array();
	$routePatams = array();
	 foreach (func_get_args() as $param) {
        if(!is_array($param) && !startsWith($param, "{")) $routePatams[]=$param;
        else {
        	$data = $param; break;
        }
    }
    $path = implode("/", $routePatams);
	if(DEBUG) printLine("HTTP posting URL : ".GITHUB_API_URL.$path);
	$request = Requests::post(GITHUB_API_URL.$path, array("Accept"=>GITHUB_API_VERSION_HEADER,"Authorization"=>"token ".GITHUB_API_TOKEN), $data, array("verify"=>false));
	if($request->status_code != 200 && $request->status_code != 201) {
		if(DEBUG) printLine("HTTP error : ".$request->body);
		return false;
	}
	else {
		return (json_decode($request->body));
	}
}

////////////////////////////////////////////////////////////////
function printLine($msg) {
	print $msg.PHP_EOL;
}

////////////////////////////////////////////////////////////////
function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}

?>