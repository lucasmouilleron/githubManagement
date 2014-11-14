<?php

////////////////////////////////////////////////////////////////
// CLONE
////////////////////////////////////////////////////////////////
// Clones the project for a commit from master.
// If the clone already exists, pull and reset to commit.
////////////////////////////////////////////////////////////////
Class CloneProcessor extends Processor {

    ////////////////////////////////////////////////////////////////
	public function run() {
		if(!file_exists($this->repoClonePath)) {
			@mkdir($this->repoCloneContainerPath);
			$result = run("cd",$this->repoCloneContainerPath,"&&",GIT_PATH,"clone",GITHUB_CLONE_PREFIX.implodePath($this->owner,$this->repo));
			if(!$result["success"]) $this->fatalAndNotify("Can't clone repo",$result["output"]);
			$this->appendToLog(LG_INFO,"Repo cloned");
		}
		else {
			$result = run(implodeSpace("cd",$this->repoClonePath,"&&",GIT_PATH,"reset","--hard","HEAD"));
			$result = run(implodeSpace("cd",$this->repoClonePath,"&&",GIT_PATH,"clean","-f","-d"));
			$result = run(implodeSpace("cd",$this->repoClonePath,"&&",GIT_PATH,"pull"));
			if(!$result["success"]) $this->fatalAndNotify("Can't pull repo",$result["output"]);
			if(DEBUG) $this->appendToLog(LG_INFO,"Repo pulled");
			$result = run(implodeSpace("cd",$this->repoClonePath,"&&",GIT_PATH,"reset","--hard",$this->commitSHA));
			if(!$result["success"]) $this->fatalAndNotify("Can't reset repo",$result["output"]);
			if(DEBUG) $this->appendToLog(LG_INFO,"Repo reseted",$this->commitSHA);
		}

		$result = run("cd",$this->repoClonePath,"&&",GIT_PATH,"show","--pretty='format:'","--name-only",$this->commitSHA,"|","sort","|","uniq");
		if(is_array($result["output"])) $this->addNotifyMessage(implodeBits(" : ","Diff files",implode("\n\r",$result["output"])));
	}

}

?>