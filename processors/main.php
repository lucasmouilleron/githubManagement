<?php 

////////////////////////////////////////////////////////////////
require_once __DIR__."/../api/libs/tools.php";

////////////////////////////////////////////////////////////////
// VARIABLES AVAILABLE
// $owner
// $repo
// $tagName
// $tagSHA
// $commitSHA

////////////////////////////////////////////////////////////////
// ADDITIONAL VARIABLES AVAILABLE IN SUB PROCESSORS
// $logger : the logger name
// $notifyDests : the notify recievers
// $notifyMessages : array of messages to be sent by the notify processor
// $env : the destination env
// $projectCfg : the project config (json -> object)
// $fullRepo : the full repo name (owner/repo)
// $repoClonePath : the cloned repo path
// $repoCloneContainerPath : the parent of the cloned repo path

////////////////////////////////////////////////////////////////
// FUNCTIONS AVAILABLE
// All PHP
// + api/libs/tools.php

////////////////////////////////////////////////////////////////
// INIT
////////////////////////////////////////////////////////////////
@mkdir(LOG_PATH);
@mkdir(LOCKS_PATH);
@mkdir(REPOS_CLONES_PATH);
putenv("HOME=/");
global $PROCESSOR_AVAILABLE_ENVS;
$logger = implodeBits("-","processing",$owner,$repo);
$fullRepo = implodePath($owner,$repo);
$notifyDests = MAIN_EMAIL;
$notifyMessages = array();

$env = getEnvFromTagName($PROCESSOR_AVAILABLE_ENVS,$tagName);
if($env == false) {appendToLog($logger,LG_INFO,"Destination env is not defined, not a processor tag",$tagName);fatal();}
$projectCfg = readJSONFile(implodePath(CONFIGS_PATH,$owner,$repo.".json"));
if($projectCfg == false) fatalAndNotify($notifyDests,$logger,"Config file not found");

$envProcess = @$projectCfg->processorsConfigs->envConfigs->{$env}->process;
if(!isset($envProcess) || !$envProcess) fatalAndNotify($notifyDests,$logger,"Processing is not activated");

$repoClonePath = implodePath(REPOS_CLONES_PATH,$owner,$repo);
$repoCloneContainerPath = implodePath(REPOS_CLONES_PATH,$owner);
if(isset($projectCfg->processorsConfigs->notifyRecipients)) $notifyDests = $projectCfg->processorsConfigs->notifyRecipients;

////////////////////////////////////////////////////////////////
// RUN PROCESSORS
////////////////////////////////////////////////////////////////
appendToLog($logger,LG_INFO,"Begining processing","owner",$owner,"repo",$repo,"tagName",$tagName,"tagSHA",$tagSHA,"commitSHA",$commitSHA,"env",$env);
$notifyMessages[]=implodeBits(" / ","Processed : ","owner",$owner,"repo",$repo,"tagName",$tagName,"tagSHA",$tagSHA,"commitSHA",$commitSHA,"env",$env);

$processors = $projectCfg->processors;
if(!isset($processors)) $processors = array();
foreach ($processors as $processor) {
    $processorPath = implodePath(PROCESSORS_PATH,$processor.".php");
    if(!file_exists($processorPath)) {
        appendToLog($logger,LG_ERROR, "Processor not found",$processor);
        continue;
    }
	appendToLog($logger,LG_INFO,"Running processor",$processor);
	include $processorPath;
	appendToLog($logger,LG_INFO,"Processor finished",$processor);
}
if(DEBUG) appendToLog($logger,LG_INFO,"No more processors to run");

////////////////////////////////////////////////////////////////
// BYE !
////////////////////////////////////////////////////////////////
appendToLog($logger,LG_INFO,"Processing finished");

?>