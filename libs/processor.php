<?php

require_once __DIR__."/tools.php";

abstract class Processor {

    ////////////////////////////////////////////////////////////////
    public $availableEnvs;
    public $notifyDests;
    public $notifyMessages;
    public $fullRepo;
    public $logger;
    public $owner;
    public $repo;
    public $tagName;
    public $tagSHA;
    public $commitSHA;
    public $env;
    public $target;
    public $projectCfg;
    public $envProcess;
    public $repoClonePath;
    public $repoCloneContainerPath;
    public $processors;
    public $mainProcessor;

    ////////////////////////////////////////////////////////////////
    abstract function run();

    ////////////////////////////////////////////////////////////////
    public function importFromMainProcessor(MainProcessor $mainProcessor)
    {   
        $this->mainProcessor = $mainProcessor;
        foreach (get_object_vars($mainProcessor) as $key => $value) {
            $this->{$key} = $value;
        }
    }

    ////////////////////////////////////////////////////////////////
    public function addNotifyMessage($message) {
        $this->mainProcessor->notifyMessages[]=$message;
    }

    ////////////////////////////////////////////////////////////////
    public function getNotifyMessages() {
        return $this->mainProcessor->notifyMessages;
    }

    ////////////////////////////////////////////////////////////////
    public function appendToLog($level,$messages) {
        $args = func_get_args();
        array_unshift($args,$this->logger);
        call_user_func_array("appendToLog", $args);
    }

    ////////////////////////////////////////////////////////////////
    public function fatalAndNotify($messages) {
        $args = func_get_args();
        array_unshift($args,$this->notifyDests,$this->logger);
        call_user_func_array("fatalAndNotify", $args);
    }

    ////////////////////////////////////////////////////////////////
    public function appendToLogAndNotify($level,$messages) {
        $args = func_get_args();
        array_unshift($args,$this->notifyDests,$this->logger);
        call_user_func_array("appendToLogAndNotify", $args);
    }

    ////////////////////////////////////////////////////////////////
    public function notify($subject, $message) {
        notify($this->notifyDests,$subject,$message);
    }

    ////////////////////////////////////////////////////////////////
    protected function getUnsetItems($definedVars, $items) {
        $unsets = array();
        $args = func_get_args();
        $definedVars = array_shift($args);
        foreach ($args as $varName) {
            if(!isset($definedVars[$varName])) $unsets[]=$this->printVarName($definedVars, $varName);
        }
        return $unsets;
    }

    ////////////////////////////////////////////////////////////////
    protected function printVarName($definedVars, $varName) {
        $allVars = $definedVars;
        foreach($allVars as $var_name => $value) {
            if ($var_name === $varName) {
                return $var_name;
            }
        }
        return false;
    }

}

?>