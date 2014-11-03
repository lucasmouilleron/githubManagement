<?php

////////////////////////////////////////////////////////////////
// FETCH FILES
////////////////////////////////////////////////////////////////
// Fetches files from the dest ENV.
// Make sure you sent your public key to the remove ENV (for rsync to run not interactively)
////////////////////////////////////////////////////////////////
// processorsConfigs->envConfigs->{$env}->SSHURI(*) : the remote ssh uri
// processorsConfigs->envConfigs->{$env}->basePath(*) : the remote root folder
// processorsConfigs->deployFolder(*) : the local repo deploy root folder
// processorsConfigs->fetchIncludeFiles : list of included files or folders
////////////////////////////////////////////////////////////////

$envSSHURI = @$projectCfg->processorsConfigs->envConfigs->{$env}->SSHURI;
$deployFolder = @$projectCfg->processorsConfigs->deployFolder;
$envBasePath = @$projectCfg->processorsConfigs->envConfigs->{$env}->basePath;
$includeFiles = @$projectCfg->processorsConfigs->fetchIncludeFiles;
$unsetItems = getUnsetItems($envSSHURI,$deployFolder,$envBasePath,$includeFiles);
if(!empty($unsetItems)) fatalAndNotify($notifyDests,$logger,"Config properties are not all set :",$unsetItems);

$options = "-rz";
if(DEBUG) $options.="v";
foreach ($includeFiles as $includeFile) {
    $result = run("rsync",$options,implodePath($envSSHURI.":".$envBasePath,$includeFile),implodePath($repoClonePath,$deployFolder."/"));
    if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't get files",$result["output"]);
}

?>