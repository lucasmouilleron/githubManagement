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
$commitSHA = "51fe3f631e295832d2010f5b911ef5eb23f0c3e1";

////////////////////////////////////////////////////////////////
// INIT
////////////////////////////////////////////////////////////////
require_once __DIR__."/../api/libs/tools.php";
$env = getEnvFromTagName($HOOKS_DEFAULT_AVAILABLE_ENVS,$tagName);
$logger = implodeBits("-","hook",$owner,$repo);
$projectCfg = readJSONFile(implodePath(CONFIGS_PATH,$owner,$repo.".json"));
$notifyDests = HOOKS_MAIN_EMAIL;
$repoClonePath = implodePath(REPOS_CLONES_PATH,$owner,$repo);
$repoCloneContainerPath = implodePath(REPOS_CLONES_PATH,$owner);

////////////////////////////////////////////////////////////////
// TEST LOCKING
////////////////////////////////////////////////////////////////
appendToLog($logger,LG_INFO,"begining deploy","owner:",$owner,"repo:",$repo,"tagName:",$tagName,"tagSHA:",$tagSHA,"commitSHA:",$commitSHA,"env:",$env);
if(isLocked($owner,$repo)) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Repo is locked, abort !");

////////////////////////////////////////////////////////////////
// SETUP AUTO LOCKING
////////////////////////////////////////////////////////////////
lock($owner,$repo);
function shutdown($owner,$repo) {unlock($owner,$repo);}
register_shutdown_function("shutdown",$owner,$repo);

////////////////////////////////////////////////////////////////
// SETUP ENV
////////////////////////////////////////////////////////////////
if($env == false) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Destination env is not defined");
if($projectCfg == false) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Config file not found");

$envDeploy = @$projectCfg->envConfigs->{$env}->deploy;
if(!isset($envDeploy) || !$envDeploy) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Deploying is not activated");

$notifyDests = $projectCfg->notifyRecipients;
$webFolder = $projectCfg->webFolder;
$buildFoler = $projectCfg->buildFolder;
$DBPath = $projectCfg->DBPath;
$envDestBasePath = $projectCfg->envConfigs->{$env}->destBasePath;
$envDestBaseURL = $projectCfg->envConfigs->{$env}->destBaseURL;
$envDBUser = $projectCfg->envConfigs->{$env}->DBUser;
$envDBPassword = $projectCfg->envConfigs->{$env}->DBPassword;
$envDBName = $projectCfg->envConfigs->{$env}->DBName;
$envDBBinary = $projectCfg->envConfigs->{$env}->DBBinary;
$envRSyncHostURI = $projectCfg->envConfigs->{$env}->RSyncHostURI;
$unsetItems = getUnsetItems($notifyDests,$webFolder,$buildFoler,$DBPath,$envDeploy,$envDestBasePath,$envDestBasePath,$envDestBaseURL,$envDBUser,$envDBPassword,$envDBName,$envDBBinary,$envRSyncHostURI);
if(!empty($unsetItems)) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Config properties are not all set : ".dump($unsetItems));

@mkdir($repoCloneContainerPath);

appendToLog($logger,LG_INFO,"setup finished",$owner,$repo,$tagName,$tagSHA,$commitSHA,$env);

////////////////////////////////////////////////////////////////
// CLONE OR PULL
////////////////////////////////////////////////////////////////
if(!file_exists($repoClonePath)) {
    $command = implodeSpace("cd",$repoCloneContainerPath,"&&",GIT_PATH,"clone",GITHUB_CLONE_PREFIX.implodePath($owner,$repo));
    exec($command, $output, $code);
    if($code>0) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Can't clone repo");
    appendToLog($logger,LG_INFO,"repo cloned");
}
else {
    $command = implodeSpace("cd",$repoClonePath,"&&",GIT_PATH,"pull");
    exec($command, $output, $code);
    if($code>0) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Can't pull repo");
    if(DEBUG) appendToLog($logger,LG_INFO,"repo pulled");
    
    $command = implodeSpace("cd",$repoClonePath,"&&",GIT_PATH,"reset","--hard",$commitSHA);
    exec($command, $output, $code);
    if($code>0) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Can't reset repo","commit :",$commitSHA);
    appendToLog($logger,LG_INFO,"repo reseted",$commitSHA);
}

////////////////////////////////////////////////////////////////
// BUILD
////////////////////////////////////////////////////////////////
if(!copydir(implodePath(LOCALS_PATH,$owner,$repo,$env),implodePath($repoClonePath,"local"))) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Can't copy local files");
appendToLog($logger,LG_INFO,"Local files copied");
//TODO BUILD

////////////////////////////////////////////////////////////////
// SEND TO ENV DEST
////////////////////////////////////////////////////////////////
$command = implodeSpace("rsync","-r","--exclude","'.git*'",implodePath($repoClonePath,$webFolder."/"),$envRSyncHostURI.":".$envDestBasePath);
exec($command, $output, $code);
if($code>0) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Can't send files",$env);
appendToLog($logger,LG_INFO,"Files sent",$env);

////////////////////////////////////////////////////////////////
// DEPLOY DB
////////////////////////////////////////////////////////////////
// TODO

////////////////////////////////////////////////////////////////
// NOTIFY
////////////////////////////////////////////////////////////////
// TODO

appendToLog($logger,LG_INFO,"deploy finished");

?>