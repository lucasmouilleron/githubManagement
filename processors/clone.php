<?php

////////////////////////////////////////////////////////////////
// CLONE
////////////////////////////////////////////////////////////////
// Clones the project for a commit.
// If the clone already exists, pull and reset to commit.
////////////////////////////////////////////////////////////////

if(!file_exists($repoClonePath)) {
	@mkdir($repoCloneContainerPath);
	$result = run("cd",$repoCloneContainerPath,"&&",GIT_PATH,"clone",GITHUB_CLONE_PREFIX.implodePath($owner,$repo));
	if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't clone repo",$result["output"]);
	appendToLog($logger,LG_INFO,"Repo cloned");
}
else {
	$result = run(implodeSpace("cd",$repoClonePath,"&&",GIT_PATH,"pull"));
	if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't pull repo",$result["output"]);
	if(DEBUG) appendToLog($logger,LG_INFO,"Repo pulled");
	$result = run(implodeSpace("cd",$repoClonePath,"&&",GIT_PATH,"reset","--hard",$commitSHA));
	if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't reset repo",$result["output"]);
	appendToLog($logger,LG_INFO,"Repo reseted",$commitSHA);
}

$result = run("cd",$repoClonePath,"&&",GIT_PATH,"show","--pretty='format:'","--name-only",$commitSHA,"|","sort","|","uniq");
if(is_array($result["output"])) $notifyMessages[]=implodeBits(" : ","Diff files",implode("\n\r",$result["output"]));

?>