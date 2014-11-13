
<?php

////////////////////////////////////////////////////////////////
// FETCH DB
////////////////////////////////////////////////////////////////
// Fetches the DB from the env remote.
// Make sure you sent your public key to the remove ENV (for scp to run not interactively).
// mySQL support only.
////////////////////////////////////////////////////////////////
// processorsConfigs->envConfigs->{$this->env}->SSHURI(*) : the remote ssh uri
// processorsConfigs->envConfigs->{$this->env}->DBUser(*) : the remote db user
// processorsConfigs->envConfigs->{$this->env}->DBPassword(*) : the remove db password
// processorsConfigs->envConfigs->{$this->env}->DBName(*) : the remote db name
// processorsConfigs->envConfigs->{$this->env}->DBDumpBinary(*) : the remote db dump binary
// processorsConfigs->envConfigs->{$this->env}->basePath(*) : the remote root folder
////////////////////////////////////////////////////////////////
Class FetchDbProcessor extends Processor {

    ////////////////////////////////////////////////////////////////
    public function run() {

        $envSSHURI = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->SSHURI;
        $envDBUser = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->DBUser;
        $envDBPassword = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->DBPassword;
        $envDBName = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->DBName;
        $envDBDumpBinary = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->DBDumpBinary;
        $envBasePath = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->basePath;
        $DBPath = @$this->projectCfg->processorsConfigs->DBPath;
        $unsetItems = $this->getUnsetItems(get_defined_vars(),"envSSHURI","envDBUser","envDBPassword","envDBName","envDBDumpBinary","envBasePath","DBPath");
        if(!empty($unsetItems)) $this->fatalAndNotify("Config properties are not all set :",$unsetItems);

        $tmpDBPath = implodePath($envBasePath,"--tmp-db.sql");

        $result = run("ssh",$envSSHURI,"\"",$envDBDumpBinary,"--delayed-insert","--extended-insert=true","--user=".$envDBUser,"--password=".$envDBPassword,$envDBName,"--result-file",$tmpDBPath,"\"");
        if(!$result["success"]) $this->fatalAndNotify("Can't dump db",$result["output"]);
        if(DEBUG) $this->appendToLog(LG_INFO,"DB dumped",$result["output"]);

        $result = run("scp",$envSSHURI.":".$tmpDBPath,implodePath($this->repoClonePath,$DBPath));
        if(!$result["success"]) $this->fatalAndNotify("Can't fetch db file",$result["output"]);
        if(DEBUG) $this->appendToLog(LG_INFO,"DB fetched",$result["output"]);

        $result = run("ssh",$envSSHURI,"\"","rm",$tmpDBPath,"\"");
        if(!$result["success"]) $this->fatalAndNotify("Can't delete db file",$result["output"]);
        if(DEBUG) $this->appendToLog(LG_INFO,"DB deleted",$result["output"]);

    }
}

?>