<?php

////////////////////////////////////////////////////////////////
// NOTIFY
////////////////////////////////////////////////////////////////
// Notifies the $notifyDests of $notifyMessages
////////////////////////////////////////////////////////////////

$result = notify($notifyDests,implodeBits(" - ",$fullRepo,"Processing finished"),implode("\n\r\n\r",$notifyMessages));
if(DEBUG) appendToLog($logger,LG_INFO,"Notifcation sent",$notifyDests);

?>