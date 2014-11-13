<?php

////////////////////////////////////////////////////////////////
// PUSH
////////////////////////////////////////////////////////////////
// Commits and pushes to the remote repo master.
////////////////////////////////////////////////////////////////
Class PushProcessor extends Processor {

    ////////////////////////////////////////////////////////////////
    public function run() {

        $result = run(implodeSpace("cd",$this->repoClonePath,"&&",GIT_PATH,"add","."));
        if(!$result["success"]) $this->fatalAndNotify("Can't add to repo",$result["output"]);
        if(DEBUG) $this->appendToLog(LG_INFO,"Added");

        $result = run(implodeSpace("cd",$this->repoClonePath,"&&",GIT_PATH,"commit","-m","\"processor commit\""));
        if(!$result["success"]) $this->fatalAndNotify("Can't commit to repo",$result["output"]);
        if(DEBUG) $this->appendToLog(LG_INFO,"Commited");

        $result = run(implodeSpace("cd",$this->repoClonePath,"&&",GIT_PATH,"push","origin"));
        if(!$result["success"]) $this->fatalAndNotify("Can't push repo",$result["output"]);
        if(DEBUG) $this->appendToLog(LG_INFO,"Repo pushed");

    }

}

?>