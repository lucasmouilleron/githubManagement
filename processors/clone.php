<?php

////////////////////////////////////////////////////////////////
// CLONE (OR PULL + RESET)
////////////////////////////////////////////////////////////////

if(!file_exists($repoClonePath)) {
	@mkdir($repoCloneContainerPath);
	$result = run(implodeSpace("cd",$repoCloneContainerPath,"&&",GIT_PATH,"clone",GITHUB_CLONE_PREFIX.implodePath($owner,$repo)));
	if(!$result["success"]) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Can't clone repo",$result["output"]);
	appendToLog($logger,LG_INFO,"Repo cloned");
}
else {
	$result = run(implodeSpace("cd",$repoClonePath,"&&",GIT_PATH,"pull"));
	if(!$result["success"]) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Can't pull repo",$result["output"]);
	if(DEBUG) appendToLog($logger,LG_INFO,"Repo pulled");
	$result = run(implodeSpace("cd",$repoClonePath,"&&",GIT_PATH,"reset","--hard",$commitSHA));
	if(!$result["success"]) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Can't reset repo",$result["output"]);
	appendToLog($logger,LG_INFO,"Repo reseted",$commitSHA);
}

?>