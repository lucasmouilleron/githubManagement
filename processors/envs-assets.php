<?php

////////////////////////////////////////////////////////////////
// COPY ENV ASSETS
////////////////////////////////////////////////////////////////
// Copies project env assets files from `envs-assets/owner/repo/ENV` to `clones/owner/repo/$envAssetsPath`
// Convenient if some parameters are diffrent from one env to the other
// In this case, isolate these parameters in some files which are copied depending on what ENV is targeted
////////////////////////////////////////////////////////////////
// processorsConfigs->envAssetsPath(*) : the env assets destination path
////////////////////////////////////////////////////////////////

$envAssetsPath = @$projectCfg->processorsConfigs->envAssetsPath;
$unsetItems = getUnsetItems($envAssetsPath);
if(!empty($unsetItems)) fatalAndNotify($notifyDests,$logger,"Config properties are not all set :",$unsetItems);

$envAssetsPathSource = implodePath(ENV_ASSETS_PATH,$owner,$repo,$env);
if(!is_dir($envAssetsPathSource)) fatalAndNotify($notifyDests,$logger,"Envs assets source folder does not exist ",$envAssetsPathSource);

$options = "-r";
if(DEBUG) $options.="v";
$result = run("cp",$options,$envAssetsPathSource,implodePath($repoClonePath,$envAssetsPath));
if(!$result["success"]) fatalAndNotify($notifyDests,$logger,"Can't copy env assets files",$result["output"]);
if(DEBUG) appendToLog($logger,LG_INFO,"Files copied",$result["output"]);

?>