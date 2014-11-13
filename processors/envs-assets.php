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
Class EnvsAssetsProcessor extends Processor {

    ////////////////////////////////////////////////////////////////
    public function run() {

        $envAssetsPath = @$this->projectCfg->processorsConfigs->envAssetsPath;
        $unsetItems = $this->getUnsetItems(get_defined_vars(),"envAssetsPath");
        if(!empty($unsetItems)) $this->fatalAndNotify("Config properties are not all set :",$unsetItems);

        $envAssetsPathSource = implodePath(ENV_ASSETS_PATH,$this->owner,$this->repo,$this->env);
        if(!is_dir($envAssetsPathSource)) $this->fatalAndNotify("Envs assets source folder does not exist ",$envAssetsPathSource);

        $options = "-r";
        if(DEBUG) $options.="v";
        $result = run("cp",$options,$envAssetsPathSource,implodePath($this->repoClonePath,$envAssetsPath));
        if(!$result["success"]) $this->fatalAndNotify("Can't copy env assets files",$result["output"]);
        if(DEBUG) $this->appendToLog(LG_INFO,"Files copied",$result["output"]);
        
    }
}

?>