<?php

////////////////////////////////////////////////////////////////
// DEPLOY FILES
////////////////////////////////////////////////////////////////
// Deploy files to the dest ENV.
// Make sure you sent your public key to the remove ENV (for rsync to run not interactively)
////////////////////////////////////////////////////////////////
// processorsConfigs->envConfigs->{$env}->SSHURI(*) : the remote ssh uri
// processorsConfigs->envConfigs->{$env}->basePath(*) : the remote root folder
// processorsConfigs->deployFolder(*) : the local repo deploy root folder
// processorsConfigs->excludeFiles : list of excluded files
////////////////////////////////////////////////////////////////

$envSSHURI = @$projectCfg->processorsConfigs->envConfigs->{$env}->SSHURI;
$deployFolder = @$projectCfg->processorsConfigs->deployFolder;
$envBasePath = @$projectCfg->processorsConfigs->envConfigs->{$env}->basePath;
$excludeFiles = @$projectCfg->processorsConfigs->excludeFiles;
$unsetItems = getUnsetItems($envSSHURI,$deployFolder,$envBasePath);
if(!empty($unsetItems)) fatalAndNotify($notifyDests,$logger,"Deploy-files config properties are not all set :",$unsetItems);

$options = "-rz";
if(DEBUG) $options.="v";
$exclusions = "";
foreach ($excludeFiles as $excludeFile) {
	$exclusions.=" --exclude '".$excludeFile."'";
}
$result = run("rsync",$options,$exclusions,implodePath($repoClonePath,$deployFolder."/"),$envSSHURI.":".$envBasePath);
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't send files",$result["output"]);

?>