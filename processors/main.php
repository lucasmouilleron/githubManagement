<?php 

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
// $env : the destination env
// $projectCfg : the project config (json -> object)
// $repoClonePath : the cloned repo path
// $repoCloneContainerPath : the parent of the cloned repo path

////////////////////////////////////////////////////////////////
// TESTS
////////////////////////////////////////////////////////////////
$owner = "lucasmouilleron";
$repo = "testDeploy";
$tagName = "--process-test1234--db";
$tagSHA = "03d8ff99aba7a8f381a351d3c0c30aae13a99996";
$commitSHA = "22165d62c84e3ea9de305cbc7cb9ddd4b45c3932";

////////////////////////////////////////////////////////////////
// INIT
////////////////////////////////////////////////////////////////
global $PROCESSOR_AVAILABLE_ENVS;
$logger = implodeBits("-","processing",$owner,$repo);
$notifyDests = MAIN_EMAIL;

$env = getEnvFromTagName($PROCESSOR_AVAILABLE_ENVS,$tagName);
if($env == false) {appendToLog($notifyDests,$logger,LG_ERROR,"Destination env is not defined, not a processor tag");fatal();}
$projectCfg = readJSONFile(implodePath(CONFIGS_PATH,$owner,$repo.".json"));
if($projectCfg == false) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Config file not found");

$envProcess = @$projectCfg->processorsConfigs->envConfigs->{$env}->process;
if(!isset($envProcess) || !$envProcess) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Processing is not activated");

$repoClonePath = implodePath(REPOS_CLONES_PATH,$owner,$repo);
$repoCloneContainerPath = implodePath(REPOS_CLONES_PATH,$owner);
if(isset($projectCfg->processorsConfigs->notifyRecipients)) $notifyDests = $projectCfg->processorsConfigs->notifyRecipients;

////////////////////////////////////////////////////////////////
// RUN PROCESSORS
////////////////////////////////////////////////////////////////
appendToLog($logger,LG_INFO,"Begining processing","owner:",$owner,"repo:",$repo,"tagName:",$tagName,"tagSHA:",$tagSHA,"commitSHA:",$commitSHA,"env:",$env);
$processors = $projectCfg->processors;
if(!isset($processors)) $processors = array();
foreach ($processors as $processor) {
	appendToLog($logger,LG_INFO,"Running processor",$processor);
	include implodePath(PROCESSORS_PATH,$processor.".php");
	appendToLog($logger,LG_INFO,"Processor finished",$processor);
}
if(DEBUG) appendToLog($logger,LG_INFO,"No more processors to run");

////////////////////////////////////////////////////////////////
// BYE !
////////////////////////////////////////////////////////////////
appendToLog($logger,LG_INFO,"Processing finished");

?>