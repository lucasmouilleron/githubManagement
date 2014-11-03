<?php

////////////////////////////////////////////////////////////////
// PUSH
////////////////////////////////////////////////////////////////
// Commits and pushes to the remote repo master.
////////////////////////////////////////////////////////////////

$result = run(implodeSpace("cd",$repoClonePath,"&&",GIT_PATH,"add","."));
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't add to repo",$result["output"]);
if(DEBUG) appendToLog($logger,LG_INFO,"Added");

$result = run(implodeSpace("cd",$repoClonePath,"&&",GIT_PATH,"commit","-m","\"processor commit\""));
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't commit to repo",$result["output"]);
if(DEBUG) appendToLog($logger,LG_INFO,"Commited");

$result = run(implodeSpace("cd",$repoClonePath,"&&",GIT_PATH,"push","origin"));
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't push repo",$result["output"]);
if(DEBUG) appendToLog($logger,LG_INFO,"Repo pushed");

?>