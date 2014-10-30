<?php

////////////////////////////////////////////////////////////////
// COPY LOCAL FILES IF ANY
////////////////////////////////////////////////////////////////
$localsPath = @$projectCfg->processorsConfigs->localsPath;
$unsetItems = getUnsetItems($localsPath);
if(!empty($unsetItems)) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Locals config properties are not all set :",$unsetItems);

$localsPathSource = implodePath(LOCALS_PATH,$owner,$repo,$env);
if(!is_dir($localsPathSource)) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Locals folder does not exist ",$localsPathSource);

$result = run(implodeSpace("cp","-r",$localsPathSource,implodePath($repoClonePath,$localsPath)));
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,LG_ERROR,"Can't copy local files",$result["output"]);
if(DEBUG) appendToLog($logger,LG_INFO,"Files copied",$result["output"]);

?>