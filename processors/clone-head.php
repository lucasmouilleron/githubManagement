<?php

////////////////////////////////////////////////////////////////
// CLONE-HEAD
////////////////////////////////////////////////////////////////
// Clones the project for a HEAD from master.
// If the clone already exists, pull and reset to commit.
////////////////////////////////////////////////////////////////

if(!file_exists($repoClonePath)) {
	@mkdir($repoCloneContainerPath);
	$result = run("cd",$repoCloneContainerPath,"&&",GIT_PATH,"clone",GITHUB_CLONE_PREFIX.implodePath($owner,$repo));
	if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't clone repo",$result["output"]);
	appendToLog($logger,LG_INFO,"Repo cloned");
}
else {
    $result = run(implodeSpace("cd",$repoClonePath,"&&",GIT_PATH,"reset","--hard","HEAD"));
    $result = run(implodeSpace("cd",$repoClonePath,"&&",GIT_PATH,"clean","-f","-d"));
	$result = run(implodeSpace("cd",$repoClonePath,"&&",GIT_PATH,"pull"));
	if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't pull repo",$result["output"]);
	if(DEBUG) appendToLog($logger,LG_INFO,"Repo pulled");
}

$result = run("cd",$repoClonePath,"&&",GIT_PATH,"show","--pretty='format:'","--name-only","HEAD","|","sort","|","uniq");
if(is_array($result["output"])) $notifyMessages[]=implodeBits(" : ","Pushed files",implode("\n\r",$result["output"]));

?>