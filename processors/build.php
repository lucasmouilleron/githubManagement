<?php

////////////////////////////////////////////////////////////////
// BUILD
////////////////////////////////////////////////////////////////
// Builds the project with a build command or a build file.
// The build file is an executable script or app (must be runnable).
////////////////////////////////////////////////////////////////
// processorsConfigs->buildFile(false) : the executbable build file
// processorsConfigs->buildCommand(false) : the build command
////////////////////////////////////////////////////////////////

$buildFile = @$projectCfg->processorsConfigs->buildFile;
$buildCommand = @$projectCfg->processorsConfigs->buildCommand;
if(!isset($buildFile) && !isset($buildCommand)) fatalAndNotify($notifyDests,$logger,"Neither the buildFile nor the buildCommand config properties are set. You have to set at least one.");

if(isset($buildFile) && $buildFile !== false) {
	$buildPath = implodePath($repoClonePath,$buildFile);
	if(!file_exists($buildPath)) fatalAndNotify($notifyDests,$logger,"Build file does not exist ",$buildPath);
	$result = run("cd",$repoClonePath,"&&",$buildFile);
	if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't run build file",$buildPath, $result["output"]);
	if(DEBUG) appendToLog($logger,LG_INFO,"Build finished from build file",$buildFile,$result["output"]);
}
else if(isset($buildCommand) && $buildCommand !== false) {
	$result = run("cd",$repoClonePath,"&&",$buildCommand);
	if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't run build command",$buildCommand, $result["output"]);
	if(DEBUG) appendToLog($logger,LG_INFO,"Build finished from build command",$buildCommand,$result["output"]);
}



?>