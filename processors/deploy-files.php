<?php

////////////////////////////////////////////////////////////////
// DEPLOY FILES TO ENV DEST IF NEEDED
////////////////////////////////////////////////////////////////

$envSSHURI = @$projectCfg->processorsConfigs->envConfigs->{$env}->SSHURI;
$webFolder = @$projectCfg->processorsConfigs->webFolder;
$envBasePath = $projectCfg->processorsConfigs->envConfigs->{$env}->basePath;
$unsetItems = getUnsetItems($envSSHURI,$webFolder,$envBasePath);
if(!empty($unsetItems)) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Deploy-files config properties are not all set :",$unsetItems);

$result = run(implodeSpace("rsync","-r","--exclude","'.git*'",implodePath($repoClonePath,$webFolder."/"),$envSSHURI.":".$envBasePath));
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Can't send files",$result["output"]);

?>