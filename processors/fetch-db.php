
<?php

////////////////////////////////////////////////////////////////
// FETCH DB
////////////////////////////////////////////////////////////////
// Fetches the DB from the env remote.
// Make sure you sent your public key to the remove ENV (for scp to run not interactively).
// mySQL support only.

////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////
$envSSHURI = @$projectCfg->processorsConfigs->envConfigs->{$env}->SSHURI;
$envDBUser = @$projectCfg->processorsConfigs->envConfigs->{$env}->DBUser;
$envDBPassword = @$projectCfg->processorsConfigs->envConfigs->{$env}->DBPassword;
$envDBName = @$projectCfg->processorsConfigs->envConfigs->{$env}->DBName;
$envDBDumpBinary = @$projectCfg->processorsConfigs->envConfigs->{$env}->DBDumpBinary;
$envBasePath = @$projectCfg->processorsConfigs->envConfigs->{$env}->basePath;
$DBPath = @$projectCfg->processorsConfigs->DBPath;
$unsetItems = getUnsetItems($envSSHURI,$envDBUser,$envDBPassword,$envDBName,$envDBDumpBinary,$envBasePath,$DBPath);
if(!empty($unsetItems)) fatalAndNotify($notifyDests,$logger,"Config properties are not all set :",$unsetItems);

$tmpDBPath = implodePath($envBasePath,"--tmp-db.sql");

$result = run("ssh",$envSSHURI,"\"",$envDBDumpBinary,"--delayed-insert","--extended-insert=true","--user=".$envDBUser,"--password=".$envDBPassword,$envDBName,"--result-file",$tmpDBPath,"\"");
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't dump db",$result["output"]);
if(DEBUG) appendToLog($logger,LG_INFO,"DB dumped",$result["output"]);

$result = run("scp",$envSSHURI.":".$tmpDBPath,implodePath($repoClonePath,$DBPath));
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't fetch db file",$result["output"]);
if(DEBUG) appendToLog($logger,LG_INFO,"DB fetched",$result["output"]);

$result = run("ssh",$envSSHURI,"\"","rm",$tmpDBPath,"\"");
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't delete db file",$result["output"]);
if(DEBUG) appendToLog($logger,LG_INFO,"DB deleted",$result["output"]);


?>