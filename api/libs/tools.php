<?php

////////////////////////////////////////////////////////////////
require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/../config.php";

////////////////////////////////////////////////////////////////
function getGithub() {
	$params = func_get_args();
	$token = array_shift($params);
	$routePatams = array();
	 foreach ($params as $param) {
        if(!is_array($param)) $routePatams[]=$param;
        else break;
    }
    $path = implode("/", $routePatams);
	if(DEBUG) printLine("HTTP getting URL : ".GITHUB_API_URL.$path);
	$request = Requests::get(GITHUB_API_URL.$path, array("Accept"=>GITHUB_API_VERSION_HEADER,"Authorization"=>"token ".$token), array("verify"=>false));
	$status = true;
	if($request->status_code != 200) $status = false;
	return array("status"=>$status,"content"=>json_decode($request->body));
}

////////////////////////////////////////////////////////////////
function postGithub() {
	$params = func_get_args();
	$token = array_shift($params);
	$data = array();
	$routePatams = array();
	 foreach ($params as $param) {
        if(!is_array($param) && !startsWith($param, "{")) $routePatams[]=$param;
        else {
        	$data = $param; break;
        }
    }
    $path = implode("/", $routePatams);
	$request = Requests::post(GITHUB_API_URL.$path, array("Accept"=>GITHUB_API_VERSION_HEADER,"Authorization"=>"token ".$token), $data, array("verify"=>false));
	$status = true;
	if($request->status_code != 200 && $request->status_code != 201) $status = false;
	return array("status"=>$status,"content"=>json_decode($request->body));
}

/////////////////////////////////////////////////////////////
function getAPIURL()
{
    $base_dir  = preg_replace("/libs$/", "", __DIR__);
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $doc_root  = preg_replace("!{$_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']);
    return rtrim($protocol.str_replace("//","/",$_SERVER["SERVER_NAME"]."/".rtrim(preg_replace("!^{$doc_root}!", '', $base_dir), "/")),"/");
}

 /////////////////////////////////////////////////////////////////
function getGithubToken($app) 
{
    $token = null;
    $env = $app->environment;
    $extraParams = array();
    parse_str($env->offsetGet("QUERY_STRING"), $extraParams);
    if($env->offsetGet("HTTP_".strtoupper(API_GITHUB_TOKEN_NAME)) != null ) 
    {
        $token = $env->offsetGet("HTTP_".strtoupper(API_GITHUB_TOKEN_NAME));
    }
    else if (array_key_exists(API_GITHUB_TOKEN_NAME,$extraParams)) {
        $token = $extraParams[API_GITHUB_TOKEN_NAME];
    }
    return $token;
}

////////////////////////////////////////////////////////////////
function createTokenForProject($owner,$repo) {
	return \JWT::encode($owner.$repo,API_JWT_PRIVATE_KEY);
}

////////////////////////////////////////////////////////////////
function checkTokenForProject($owner,$repo,$token) {
	$tokenDecoded = \JWT::decode($token, API_JWT_PRIVATE_KEY);
	if($tokenDecoded != $owner.$repo) throw new Exception("bad");
	return \JWT::encode($owner.$repo,API_JWT_PRIVATE_KEY);
}

////////////////////////////////////////////////////////////////
function implodeBis() {
	$bits = func_get_args();
	$glue = array_shift($bits);
	return implode($glue, $bits);
}

////////////////////////////////////////////////////////////////
function implodePath() {
	return implode("/", func_get_args());
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