<?php

////////////////////////////////////////////////////////////////
// DEPLOY DB IF NEEDED
////////////////////////////////////////////////////////////////
$envSSHURI = @$projectCfg->processorsConfigs->envConfigs->{$env}->SSHURI;
$envDBUser = @$projectCfg->processorsConfigs->envConfigs->{$env}->DBUser;
$envDBPassword = @$projectCfg->processorsConfigs->envConfigs->{$env}->DBPassword;
$envDBName = @$projectCfg->processorsConfigs->envConfigs->{$env}->DBName;
$envDBBinary = @$projectCfg->processorsConfigs->envConfigs->{$env}->DBBinary;
$envBasePath = @$projectCfg->processorsConfigs->envConfigs->{$env}->basePath;
$DBPath = @$projectCfg->processorsConfigs->DBPath;
$unsetItems = getUnsetItems($envSSHURI,$envDBUser,$envDBPassword,$envDBName,$envDBBinary,$envBasePath,$DBPath);
if(!empty($unsetItems)) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Deploy-DB config properties are not all set :",$unsetItems);

if(getNeedDBFromTagName($tagName)) {
	if(!empty($unsetItems)) fatalAndNotify($notifyDests,$logger,LG_ERROR,"DB config properties are not all set :",$unsetItems);
	$tmpDBPath = implodePath($envBasePath,"--tmp-db.sql");
	$result = run(implodeSpace("scp",implodePath($repoClonePath,$DBPath),$envSSHURI.":".$tmpDBPath));
	if(!$result["success"]) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Can't send db file",$result["output"]);
	if(DEBUG) appendToLog($logger,LG_INFO,"DB sent",$result["output"]);
	$result = run(implodeSpace("ssh",$envSSHURI,"\"",$envDBBinary,"--user=".$envDBUser,"--password=".$envDBPassword,$envDBName,"<",$tmpDBPath,"\""));
	if(!$result["success"]) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Can't run db file",$result["output"]);
	if(DEBUG) appendToLog($logger,LG_INFO,"DB ran",$result["output"]);
	$result = run(implodeSpace("ssh",$envSSHURI,"\"","rm",$tmpDBPath,"\""));
	if(!$result["success"]) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Can't delete db file",$result["output"]);
	if(DEBUG) appendToLog($logger,LG_INFO,"DB deleted",$result["output"]);
}
else {
	appendToLog($logger,LG_INFO,"DB deploy was not asked.","You need to explicitely add the DB deploy key in the tag message",PROCESSOR_DEPLOY_DB);
}

?>