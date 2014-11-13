<?php

require_once __DIR__."/tools.php";
require_once __DIR__."/processor.php";

class MainProcessor extends Processor {

    ////////////////////////////////////////////////////////////////
    public function __construct($owner, $repo, $tagName, $tagSHA, $commitSHA) {
        $this->owner = $owner;
        $this->repo = $repo;
        $this->tagName  = $tagName;
        $this->tagSHA = $tagSHA;
        $this->commitSHA = $commitSHA;
    }

    ////////////////////////////////////////////////////////////////
    public function initEnv() {

        @mkdir(LOG_PATH);
        @mkdir(LOCKS_PATH);
        @mkdir(REPOS_CLONES_PATH);

        putenv("HOME=".APACHE_HOME);
        putenv("PATH=".ENV_PATH);

        global $PROCESSOR_AVAILABLE_ENVS;
        $this->availableEnvs = $PROCESSOR_AVAILABLE_ENVS;
        $this->logger = implodeBits("-","processing",$this->owner,$this->repo);
        $this->fullRepo = implodePath($this->owner,$this->repo);
        $this->notifyDests = MAIN_EMAIL;
        $this->notifyMessages = array();

        $this->setErrorHandler();

        $this->env = $this->getEnvFromTagName();
        if($this->env == false) {
            $this->appendToLog(LG_INFO,"Destination env is not defined, not a processor tag",$this->tagName);
            fatal();
        }

        $this->target = $this->getTargetFromTagName();

        $this->projectCfg = readJSONFile(implodePath(CONFIGS_PATH,$this->owner,$this->repo.".json"));
        if($this->projectCfg == false) {
            $this->fatalAndNotify("Config file not found");
        }

        $this->envProcess = @$this->projectCfg->processorsConfigs->envConfigs->{$this->env}->process;
        if(!isset($this->envProcess) || !$this->envProcess) {
            $this->fatalAndNotify("Processing is not activated");
        }

        $this->repoClonePath = implodePath(REPOS_CLONES_PATH,$this->owner,$this->repo);
        $this->repoCloneContainerPath = implodePath(REPOS_CLONES_PATH,$this->owner);
        
        if(isset($this->projectCfg->processorsConfigs->notifyRecipients)) {
            $this->notifyDests = $this->projectCfg->processorsConfigs->notifyRecipients;
        }
    }

    ////////////////////////////////////////////////////////////////
    public function run() {
        $this->appendToLog(LG_INFO,"Begining processing","owner",$this->owner,"repo",$this->repo,"tagName",$this->tagName,"tagSHA",$this->tagSHA,"commitSHA",$this->commitSHA,"env",$this->env,"target",$this->target);
        $this->pushNotifyMessage(implodeBits(" / ","Processed : ","owner",$this->owner,"repo",$this->repo,"tagName",$this->tagName,"tagSHA",$this->tagSHA,"commitSHA",$this->commitSHA,"env",$this->env,"target",$this->target));
        $this->processors = $this->projectCfg->processors->{$this->target};

        if(!isset($this->processors)) {
            $this->processors = array();
        }
        foreach ($this->processors as $processor) {
            $processorPath = implodePath(PROCESSORS_PATH,$processor.".php");
            if(!file_exists($processorPath)) {
                $this->appendToLog(LG_ERROR, "Processor not found",$processor);
                continue;
            }
            $this->appendToLog(LG_INFO,"Running processor",$processor);
            require_once $processorPath;
            $processorClassName = camelCase($processor." processor");
            $processorInstance = new $processorClassName;
            $processorInstance->importFromMainProcessor($this);
            $processorInstance->run();
            $this->appendToLog(LG_INFO,"Processor finished",$processor);
        }
        if(DEBUG) $this->appendToLog(LG_INFO,"No more processors to run");
    }

    ////////////////////////////////////////////////////////////////
    protected function setErrorHandler() {
        global $that;
        $that = $this;
        function managerError($errno, $errstr, $errfile, $errline) {
            if(!($errno && error_reporting())) return true;
            global $that;
            var_dump($that);
            $that->fatalAndNotify("A script error append",$errno,$errstr,$errfile,$errline);
            die();
        }
        set_error_handler("managerError", E_ALL & ~E_NOTICE & ~E_USER_NOTICE);
    }

    ////////////////////////////////////////////////////////////////
    protected function getEnvFromTagName() {
        if(!contains($this->tagName,PROCESSOR_DEPLOY_PREFIX)) {
            return false;
        }
        $params = substr($this->tagName, strpos($this->tagName, PROCESSOR_DEPLOY_PREFIX) + strlen(PROCESSOR_DEPLOY_PREFIX.PROCESSOR_DEPLOY_SEPARATOR));
        $params = explode(PROCESSOR_DEPLOY_SEPARATOR,$params);
        $env = $params[0];
        if(in_array($env, $this->availableEnvs)) {
            return $env;
        }
        else {
            return false;
        }
    }

    ////////////////////////////////////////////////////////////////
    protected function getTargetFromTagName() {
        if(!contains($this->tagName,PROCESSOR_DEPLOY_PREFIX)) {
            return false;
        }
        $params = substr($this->tagName, strpos($this->tagName, PROCESSOR_DEPLOY_PREFIX) + strlen(PROCESSOR_DEPLOY_PREFIX.PROCESSOR_DEPLOY_SEPARATOR));
        $params = explode(PROCESSOR_DEPLOY_SEPARATOR,$params);
        if(count($params) < 2) {
            return "default";
        }
        else {
            return $params[1];
        }
    }

}

?>