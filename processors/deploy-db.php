<?php

////////////////////////////////////////////////////////////////
// DEPLOY DB
////////////////////////////////////////////////////////////////
// Sends the DB to the env remote. Runs it. Delete it.
// Make sure you sent your public key to the remove ENV (for scp to run not interactively).
// mySQL support only.
////////////////////////////////////////////////////////////////
// processorsConfigs->envConfigs->{$this->env}->SSHURI(*) : the remote ssh uri
// processorsConfigs->envConfigs->{$this->env}->DBUser(*) : the remote db user
// processorsConfigs->envConfigs->{$this->env}->DBPassword(*) : the remove db password
// processorsConfigs->envConfigs->{$this->env}->DBName(*) : the remote db name
// processorsConfigs->envConfigs->{$this->env}->DBBinary(*) : the remote db binary
// processorsConfigs->envConfigs->{$this->env}->basePath(*) : the remote base path (to drop the tmp db file)
// processorsConfigs->DBPath(*) : the path to the db file in the repo
////////////////////////////////////////////////////////////////
Class DeployDbProcessor extends Processor {

    ////////////////////////////////////////////////////////////////
    public function run() {

        $envSSHURI = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->SSHURI;
        $envDBUser = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->DBUser;
        $envDBPassword = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->DBPassword;
        $envDBName = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->DBName;
        $envDBBinary = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->DBBinary;
        $envBasePath = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->basePath;
        $DBPath = @$this->projectCfg->processorsConfigs->DBPath;
        $unsetItems = $this->getUnsetItems(get_defined_vars(),"envSSHURI","envDBUser","envDBPassword","envDBName","envDBBinary","envBasePath","DBPath");
        if(!empty($unsetItems)) $this->fatalAndNotify("Config properties are not all set :",$unsetItems);

        $tmpDBPath = implodePath($envBasePath,"--tmp-db.sql");

        $result = run("scp",implodePath($this->repoClonePath,$DBPath),$envSSHURI.":".$tmpDBPath);
        if(!$result["success"]) $this->fatalAndNotify("Can't send db file",$result["output"]);
        if(DEBUG) $this->appendToLog(LG_INFO,"DB sent",$result["output"]);

        $result = run("ssh",$envSSHURI,"\"",$envDBBinary,"--user=".$envDBUser,"--password=".$envDBPassword,$envDBName,"<",$tmpDBPath,"\"");
        if(!$result["success"]) $this->fatalAndNotify("Can't run db file",$result["output"]);
        if(DEBUG) $this->appendToLog(LG_INFO,"DB ran",$result["output"]);

        $result = run("ssh",$envSSHURI,"\"","rm",$tmpDBPath,"\"");
        if(!$result["success"]) $this->fatalAndNotify("Can't delete db file",$result["output"]);
        if(DEBUG) $this->appendToLog(LG_INFO,"DB deleted",$result["output"]);

    }
}

?>