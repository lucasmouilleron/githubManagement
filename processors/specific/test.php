<?php

////////////////////////////////////////////////////////////////
// TEST
////////////////////////////////////////////////////////////////
Class SpecificTestProcessor extends Processor {

    ////////////////////////////////////////////////////////////////
    public function run() {
        $this->appendToLog(LG_INFO,"Test processor","yeah");    
    }
    
}

?>