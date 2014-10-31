<?php

////////////////////////////////////////////////////////////////
// BUILD IF NEEDED
////////////////////////////////////////////////////////////////

$buildFile = @$projectCfg->processorsConfigs->buildFile;
$unsetItems = getUnsetItems($buildFile);
if(!empty($unsetItems)) fatalAndNotify($notifyDests,$logger,"Build config properties are not all set :",$unsetItems);

$buildPath = implodePath($repoClonePath,$buildFile);
if(!file_exists($buildPath)) fatalAndNotify($notifyDests,$logger,"Build file does not exist ",$buildPath);

$result = run("cd",$repoClonePath,"&&",$buildFile);
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't run build file",$buildPath, $result["output"]);
if(DEBUG) appendToLog($logger,LG_INFO,"Build finished",$result["output"]);

?>