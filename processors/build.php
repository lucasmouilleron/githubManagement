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
Class BuildProcessor extends Processor {

    ////////////////////////////////////////////////////////////////
    public function run() {

        $buildFile = @$this->projectCfg->processorsConfigs->buildFile;
        $buildCommand = @$this->projectCfg->processorsConfigs->buildCommand;
        if(!isset($buildFile) && !isset($buildCommand)) $this->fatalAndNotify("Neither the buildFile nor the buildCommand config properties are set. You have to set at least one.");

        if(isset($buildFile) && $buildFile !== false) {
            $buildPath = implodePath($this->repoClonePath,$buildFile);
            if(!file_exists($buildPath)) $this->fatalAndNotify("Build file does not exist ",$buildPath);
            
            $result = run("cd",$this->repoClonePath,"&&",$buildFile);
            if(!$result["success"]) $this->fatalAndNotify("Can't run build file",$buildPath, $result["output"]);
            
            if(DEBUG) $this->appendToLog(LG_INFO,"Build finished from build file",$buildFile,$result["output"]);
        }
        else if(isset($buildCommand) && $buildCommand !== false) {
            $result = run("cd",$this->repoClonePath,"&&",$buildCommand);
            if(!$result["success"]) $this->fatalAndNotify("Can't run build command",$buildCommand, $result["output"]);
            
            if(DEBUG) $this->appendToLog(LG_INFO,"Build finished from build command",$buildCommand,$result["output"]);
        }

    }
}

?>