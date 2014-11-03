<?php

////////////////////////////////////////////////////////////////
// DEPLOY DB
////////////////////////////////////////////////////////////////
// Sends the DB to the env remote. Runs it. Delete it.
// Make sure you sent your public key to the remove ENV (for scp to run not interactively).
// mySQL support only.
////////////////////////////////////////////////////////////////
// processorsConfigs->envConfigs->{$env}->SSHURI(*) : the remote ssh uri
// processorsConfigs->envConfigs->{$env}->DBUser(*) : the remote db user
// processorsConfigs->envConfigs->{$env}->DBPassword(*) : the remove db password
// processorsConfigs->envConfigs->{$env}->DBName(*) : the remote db name
// processorsConfigs->envConfigs->{$env}->DBBinary(*) : the remote db binary
// processorsConfigs->envConfigs->{$env}->basePath(*) : the remote base path (to drop the tmp db file)
// processorsConfigs->DBPath(*) : the path to the db file in the repo
////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////
$envSSHURI = @$projectCfg->processorsConfigs->envConfigs->{$env}->SSHURI;
$envDBUser = @$projectCfg->processorsConfigs->envConfigs->{$env}->DBUser;
$envDBPassword = @$projectCfg->processorsConfigs->envConfigs->{$env}->DBPassword;
$envDBName = @$projectCfg->processorsConfigs->envConfigs->{$env}->DBName;
$envDBBinary = @$projectCfg->processorsConfigs->envConfigs->{$env}->DBBinary;
$envBasePath = @$projectCfg->processorsConfigs->envConfigs->{$env}->basePath;
$DBPath = @$projectCfg->processorsConfigs->DBPath;
$unsetItems = getUnsetItems($envSSHURI,$envDBUser,$envDBPassword,$envDBName,$envDBBinary,$envBasePath,$DBPath);
if(!empty($unsetItems)) fatalAndNotify($notifyDests,$logger,"DB config properties are not all set :",$unsetItems);

$tmpDBPath = implodePath($envBasePath,"--tmp-db.sql");
$result = run("scp",implodePath($repoClonePath,$DBPath),$envSSHURI.":".$tmpDBPath);
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't send db file",$result["output"]);
if(DEBUG) appendToLog($logger,LG_INFO,"DB sent",$result["output"]);
$result = run("ssh",$envSSHURI,"\"",$envDBBinary,"--user=".$envDBUser,"--password=".$envDBPassword,$envDBName,"<",$tmpDBPath,"\"");
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't run db file",$result["output"]);
if(DEBUG) appendToLog($logger,LG_INFO,"DB ran",$result["output"]);
$result = run("ssh",$envSSHURI,"\"","rm",$tmpDBPath,"\"");
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't delete db file",$result["output"]);
if(DEBUG) appendToLog($logger,LG_INFO,"DB deleted",$result["output"]);


?>