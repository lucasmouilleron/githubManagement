<?php

////////////////////////////////////////////////////////////////
require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/../config.php";
date_default_timezone_set("Europe/Paris");

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
function getAPIURLOld()
{
	$base_dir  = preg_replace("/libs$/", "", __DIR__);
	$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	$doc_root  = preg_replace("!{$_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']);
	return rtrim($protocol.str_replace("//","/",$_SERVER["SERVER_NAME"].":".$_SERVER['SERVER_PORT']."/".rtrim(preg_replace("!^{$doc_root}!", '', $base_dir), "/")),"/");
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
function checkHookSignature($payload, $signature) {
	$test = hash_hmac("sha1",$payload,API_PRIVATE_KEY);
	$signature = str_replace("sha1=", "", $signature);
	if($signature != $test) {
		throw new Exception("signatures do not match");
	}
}

////////////////////////////////////////////////////////////////
function getProcessorName($file) {
	return preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($file));
}

////////////////////////////////////////////////////////////////
function getEnvFromTagName($possibleEnvs, $tagName) {
	foreach ($possibleEnvs as $possibleEnv) {
		if(contains($tagName,PROCESSOR_DEPLOY_PREFIX.$possibleEnv)) return $possibleEnv;
	}
	return false;
}

////////////////////////////////////////////////////////////////
function getNeedDBFromTagName($tagName) {
	return contains($tagName,PROCESSOR_DEPLOY_DB);
}

////////////////////////////////////////////////////////////////
function lock($owner,$repo) {
	@mkdir(implodePath(LOCKS_PATH,$owner));
	file_put_contents(implodePath(LOCKS_PATH,$owner,$repo), "locked");
	registerAutoUnlock($owner,$repo);
}

////////////////////////////////////////////////////////////////
function unlock($owner,$repo) {
	unlink(implodePath(LOCKS_PATH,$owner,$repo));
}

////////////////////////////////////////////////////////////////
function isLocked($owner,$repo) {
	return file_exists(implodePath(LOCKS_PATH,$owner,$repo));
}

////////////////////////////////////////////////////////////////
function registerAutoUnlock($owner,$repo) {
	function shutdown($owner,$repo) {unlock($owner,$repo);}
	register_shutdown_function("shutdown",$owner,$repo);
}

////////////////////////////////////////////////////////////////
function run($command) {
	$output = array();
	$code = -1;
	$args = func_get_args();
	if(count($args)>1) $command = implode(" ",$args);
	ob_start();
	$moreOutput = exec($command,$output,$code);
	$moremoreoutput = ob_get_clean();
	$ouput[]=$moreOutput;
	$ouput[]=$moremoreoutput;
	return array("code"=>$code,"output"=>$output,"success"=>($code==0));
}

////////////////////////////////////////////////////////////////
function notify($to, $subject, $message) {
	if(is_array($to)) $to = implode(", ", $to);
	return mail($to, "[".SYSTEM_NAME."] - ".$subject, $message);
}

////////////////////////////////////////////////////////////////
function appendToLog($logger,$level,$message) {
	$args = func_get_args();
	$logger = array_shift($args);
	$level = array_shift($args);
	$message = messageForLogFromArgs($args);
	$message = date("Y/m/d H:i:s")." - [".$level."] - ".$message."\r\n";
	file_put_contents(LOG_PATH."/".$logger.".log", $message, FILE_APPEND);
}

////////////////////////////////////////////////////////////////
function appendToLogAndNotify($to,$logger,$level,$message) {
	$args = func_get_args();
	$to = array_shift($args);
	$logger = array_shift($args);
	$level = array_shift($args);
	$message = messageForLogFromArgs($args);
	appendToLog($logger,$level,$message);
	notify($to,$logger."/".$level,$message);
}

////////////////////////////////////////////////////////////////
function fatalAndNotify($to,$logger,$message) {
	$args = func_get_args();
	$to = array_shift($args);
	$logger = array_shift($args);
	$message = messageForLogFromArgs($args);
	appendToLogAndNotify($to,$logger,LG_FATAL,$message);
	fatal();
}

////////////////////////////////////////////////////////////////
function messageForLogFromArgs($args) {
	array_walk($args, function(&$val){if(is_object($val) || is_array($val)) {$val = json_encode($val);}});
	$message = implode(" / ", $args);
	return str_replace("\n"," ",$message);
}

////////////////////////////////////////////////////////////////
function fatal() {
	exit(1);
}

////////////////////////////////////////////////////////////////
function getUnsetItems($items) {
	$unsets = array();
	foreach (func_get_args() as $item) {
		if(!isset($item)) $unsets[]=printVarName($item);
	}
	return $unsets;
}

////////////////////////////////////////////////////////////////
function dump($var) {
	return var_export($var,true);
}

////////////////////////////////////////////////////////////////
function implodeBits() {
	$bits = func_get_args();
	$glue = array_shift($bits);
	return implode($glue, $bits);
}

////////////////////////////////////////////////////////////////
function implodePath() {
	return implode("/", func_get_args());
}

////////////////////////////////////////////////////////////////
function implodeSpace() {
	return implode(" ", func_get_args());
}

////////////////////////////////////////////////////////////////
function readJSONFile($filePath) {
	if(!file_exists($filePath)) return false;
	return json_decode(file_get_contents($filePath));
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

////////////////////////////////////////////////////////////////
function contains($str, $needle) {
	return (strpos($str,$needle) !== FALSE);
}

////////////////////////////////////////////////////////////////
function printVarName($var) {
	foreach($GLOBALS as $var_name => $value) {
		if ($value === $var) {
			return $var_name;
		}
	}
	return false;
}

////////////////////////////////////////////////////////////////
function copydir($source,$destination)
{
	if(!is_dir($destination)){
		$oldumask = umask(0); 
		mkdir($destination, 01777); 
		umask($oldumask);
	}
	$dir_handle = @opendir($source);
	if($dir_handle === false) return false;
	while ($file = readdir($dir_handle)) 
	{
		if($file!="." && $file!=".." && !is_dir("$source/$file"))
			if(!@copy("$source/$file","$destination/$file")) return false;
		if($file!="." && $file!=".." && is_dir("$source/$file"))
			if(!copydir("$source/$file","$destination/$file")) return false;
	}
	closedir($dir_handle);
	return true;
}

////////////////////////////////////////////////////////////////
function listFiles($folder, $removedFolders=false, $removeRoot=false, $removeDotted=true) {
	$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST,RecursiveIteratorIterator::CATCH_GET_CHILD);
	$paths = array($folder);
	foreach ($iter as $path => $dir) {
		$paths[] = $path;
	}
	$finalPaths = array();
	foreach($paths as $path) {
		if($removedFolders && is_dir($path)) continue;
		if($removeDotted && startsWith(basename($path),".")) continue;
		if($removeRoot) $path = str_replace($folder, "", $path);
		$finalPaths[]= $path;
	}
	return $finalPaths;
}

////////////////////////////////////////////////////////////////
function removeExtensions($files) {
	array_walk($files, function(&$val){$val = preg_replace('/\.[^.]+$/','',$val);});
	return $files;
}

?>