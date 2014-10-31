<?php

////////////////////////////////////////////////////////////////
// COPY ENV ASSETS IF ANY
////////////////////////////////////////////////////////////////
$envAssetsPath = @$projectCfg->processorsConfigs->envAssetsPath;
$unsetItems = getUnsetItems($envAssetsPath);
if(!empty($unsetItems)) fatalAndNotify($notifyDests,$logger,"Envs-assets config properties are not all set :",$unsetItems);

$envAssetsPathSource = implodePath(ENV_ASSETS_PATH,$owner,$repo,$env);
if(!is_dir($envAssetsPathSource)) fatalAndNotify($notifyDests,$logger,"Envs assets source folder does not exist ",$envAssetsPathSource);

$result = run(implodeSpace("cp","-r",$envAssetsPathSource,implodePath($repoClonePath,$envAssetsPath)));
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't copy env assets files",$result["output"]);
if(DEBUG) appendToLog($logger,LG_INFO,"Files copied",$result["output"]);

?>