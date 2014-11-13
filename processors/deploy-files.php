<?php

////////////////////////////////////////////////////////////////
// DEPLOY FILES
////////////////////////////////////////////////////////////////
// Deploy files to the dest ENV.
// Make sure you sent your public key to the remove ENV (for rsync to run not interactively)
////////////////////////////////////////////////////////////////
// processorsConfigs->envConfigs->{$this->env}->SSHURI(*) : the remote ssh uri
// processorsConfigs->envConfigs->{$this->env}->basePath(*) : the remote root folder
// processorsConfigs->deployFolder(*) : the local repo deploy root folder
// processorsConfigs->deployExcludeFiles : list of excluded files or folders
////////////////////////////////////////////////////////////////
Class DeployFilesProcessor extends Processor {

    ////////////////////////////////////////////////////////////////
    public function run() {

        $envSSHURI = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->SSHURI;
        $deployFolder = @$this->projectCfg->processorsConfigs->deployFolder;
        $envBasePath = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->basePath;
        $excludeFiles = @$this->projectCfg->processorsConfigs->deployExcludeFiles;
        $unsetItems = $this->getUnsetItems(get_defined_vars(),"envSSHURI","deployFolder","envBasePath");
        if(!empty($unsetItems)) $this->fatalAndNotify("Config properties are not all set :",$unsetItems);

        $options = "-rz";
        if(DEBUG) $options.="v";
        $exclusions = "";
        if(isset($excludeFiles)) {
            foreach ($excludeFiles as $excludeFile) {
               $exclusions.=" --exclude '".$excludeFile."'";
           }
       }
       $result = run("rsync",$options,$exclusions,implodePath($this->repoClonePath,$deployFolder."/"),$envSSHURI.":".$envBasePath);
       if(!$result["success"]) $this->fatalAndNotify("Can't send files",$result["output"]);

   }

}


?>