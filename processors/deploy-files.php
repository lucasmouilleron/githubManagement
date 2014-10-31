<?php

////////////////////////////////////////////////////////////////
// DEPLOY FILES TO ENV DEST IF NEEDED
////////////////////////////////////////////////////////////////

$envSSHURI = @$projectCfg->processorsConfigs->envConfigs->{$env}->SSHURI;
$deployFolder = @$projectCfg->processorsConfigs->deployFolder;
$envBasePath = $projectCfg->processorsConfigs->envConfigs->{$env}->basePath;
$unsetItems = getUnsetItems($envSSHURI,$deployFolder,$envBasePath);
if(!empty($unsetItems)) fatalAndNotify($notifyDests,$logger,"Deploy-files config properties are not all set :",$unsetItems);

$result = run(implodeSpace("rsync","-r","--exclude","'.git*'",implodePath($repoClonePath,$deployFolder."/"),$envSSHURI.":".$envBasePath));
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't send files",$result["output"]);

?>