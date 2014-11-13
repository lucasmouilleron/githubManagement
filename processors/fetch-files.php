<?php

////////////////////////////////////////////////////////////////
// FETCH FILES
////////////////////////////////////////////////////////////////
// Fetches files from the dest ENV.
// Make sure you sent your public key to the remove ENV (for rsync to run not interactively)
////////////////////////////////////////////////////////////////
// processorsConfigs->envConfigs->{$this->env}->SSHURI(*) : the remote ssh uri
// processorsConfigs->envConfigs->{$this->env}->basePath(*) : the remote root folder
// processorsConfigs->deployFolder(*) : the local repo deploy root folder
// processorsConfigs->fetchIncludeFiles : list of included files or folders
////////////////////////////////////////////////////////////////
Class FetchFilesProcessor extends Processor {

    ////////////////////////////////////////////////////////////////
    public function run() {

        $envSSHURI = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->SSHURI;
        $deployFolder = @$this->projectCfg->processorsConfigs->deployFolder;
        $envBasePath = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->basePath;
        $fetchIncludeFiles = @$this->projectCfg->processorsConfigs->fetchIncludeFiles;
        $unsetItems = $this->getUnsetItems(get_defined_vars(), "envSSHURI","deployFolder","envBasePath");
        if(!empty($unsetItems)) $this->fatalAndNotify("Config properties are not all set :",$unsetItems);

        $options = "-rz";
        if(DEBUG) $options.="v";
        if(isset($fetchIncludeFiles)) {
            foreach ($fetchIncludeFiles as $includeFile) {
                $result = run("rsync",$options,implodePath($envSSHURI.":".$envBasePath,$includeFile),implodePath($this->repoClonePath,$deployFolder."/"));
                if(!$result["success"]) $this->fatalAndNotify("Can't get files",$result["output"]);
            }
        }
        else {
            $this->appendToLog(LG_INFO,"No files to include");
        }

    }

}

?>