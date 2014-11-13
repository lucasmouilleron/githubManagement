<?php

////////////////////////////////////////////////////////////////
// LOCK
////////////////////////////////////////////////////////////////
// Forbids concurrent processings untill the current one is finished.
// Auto released when the processing is finished.
////////////////////////////////////////////////////////////////
Class LockProcessor extends Processor {

    ////////////////////////////////////////////////////////////////
    public function run() {

        if($this->isLocked()) {
            $this->fatalAndNotify("Repo is locked, abort !");
        }
        
        $this->lock();
        
        if(DEBUG) $this->appendToLog(LG_INFO,"Locked");
    }

    ////////////////////////////////////////////////////////////////
    public function lock() {
        @mkdir(implodePath(LOCKS_PATH,$this->owner));
        file_put_contents(implodePath(LOCKS_PATH,$this->owner,$this->repo), "locked");
        $this->registerAutoUnlock();
    }

    ////////////////////////////////////////////////////////////////
    public function unlock() {
        unlink(implodePath(LOCKS_PATH,$this->owner,$this->repo));
    }

    ////////////////////////////////////////////////////////////////
    public function isLocked() {
        return file_exists(implodePath(LOCKS_PATH,$this->owner,$this->repo));
    }

    ////////////////////////////////////////////////////////////////
    protected function registerAutoUnlock() {
        function shutdown($that) {
            $that->unlock();
        }
        register_shutdown_function("shutdown",$this);
    }
}

?>