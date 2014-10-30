<?php 

////////////////////////////////////////////////////////////////
// VARIABLES AVAILABLE
// $owner
// $repo
// $tagName
// $tagSHA
// $commitSHA

////////////////////////////////////////////////////////////////
// TESTS
////////////////////////////////////////////////////////////////
$owner = "lucasmouilleron";
$repo = "testDeploy";
$tagName = "deploy-test1234";
$tagSHA = "03d8ff99aba7a8f381a351d3c0c30aae13a99996";
$commitSHA = "b07f87a235b7df73b35b9747560850c10ab0ca79";

////////////////////////////////////////////////////////////////
// INIT
////////////////////////////////////////////////////////////////
require_once __DIR__."/../api/libs/tools.php";
$env = getEnvFromTagName($HOOKS_DEFAULT_AVAILABLE_ENVS,$tagName);
$logger = implodeBits("-","hook",$owner,$repo);
$projectCfg = readJSONFile(implodePath(CONFIGS_PATH,$owner,$repo.".json"));
$notifyDests = HOOKS_MAIN_EMAIL;

////////////////////////////////////////////////////////////////
// SETUP
////////////////////////////////////////////////////////////////
appendToLog($logger,"start", implodePath($owner,$repo,$tagName,$tagSHA,$commitSHA,$env));

if($env == false) {
    appendToLogAndNotify($notifyDests,$logger,"error","Destination env is not defined");
    return;
}
if($projectCfg == false) {
    appendToLogAndNotify($notifyDests,$logger,"error","Config file not found");
    return;
}

$notifyDests = $projectCfg->notifyRecipients;
$webFolder = $projectCfg->webFolder;
$buildFoler = $projectCfg->buildFolder;
$DBPath = $projectCfg->DBPath;
$envDeploy = $projectCfg->envConfigs->{$env}->deploy;
$envDestBasePath = $projectCfg->envConfigs->{$env}->destBasePath;
$envDestBaseURL = $projectCfg->envConfigs->{$env}->destBaseURL;
$envDBUser = $projectCfg->envConfigs->{$env}->DBUser;
$envDBPassword = $projectCfg->envConfigs->{$env}->DBPassword;
$envDBName = $projectCfg->envConfigs->{$env}->DBName;
$envDBBinary = $projectCfg->envConfigs->{$env}->DBBinary;
$unsetItems = getUnsetItems($notifyDests,$webFolder,$buildFoler,$DBPath,$envDeploy,$envDestBasePath,$envDestBasePath,$envDestBaseURL,$envDBUser,$envDBPassword,$envDBName,$envDBBinary);
if(!empty($unsetItems)) {
    appendToLogAndNotify($notifyDests,$logger,"error","Config properties are not all set : ".implode("/",$unsetItems));
    return;
}

////////////////////////////////////////////////////////////////
// GO !!!
////////////////////////////////////////////////////////////////
$command = implodeSpace(GIT_PATH);
exec($command, $output, $code);


?>