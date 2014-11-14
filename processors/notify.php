<?php

////////////////////////////////////////////////////////////////
// NOTIFY
////////////////////////////////////////////////////////////////
// Notifies the $notifyDests of $notifyMessages
////////////////////////////////////////////////////////////////
Class NotifyProcessor extends Processor {

    ////////////////////////////////////////////////////////////////
    public function run() {

        $result = $this->notify(implodeBits(" - ",$this->fullRepo,"Processing finished"),implode("\n\r\n\r",$this->getNotifyMessages()));
        if(DEBUG) $this->appendToLog(LG_INFO,"Notifcation sent",$this->notifyDests);

    }
}

?>