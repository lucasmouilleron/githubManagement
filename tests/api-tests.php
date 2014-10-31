<?php

////////////////////////////////////////////////////////////////
require_once __DIR__."/../api/libs/tools.php";

////////////////////////////////////////////////////////////////
$owner = "lucasmouilleron";
$repo = "testDeploy";
$revision = "10304ad26842eb304da3eb68951960c904c05d04";
$tagName = time()."--process-test--db";

////////////////////////////////////////////////////////////////

//$request = Requests::post(implodePath(API_URL,"repos",$owner,$repo,"hook","init?github-token=".GITHUB_MASTER_TOKEN));

$request = Requests::post(implodePath(API_URL,"repos",$owner,$repo,"tag?github-token=".GITHUB_MASTER_TOKEN), array(), array("tag-revision"=>$revision,"tag-name"=>$tagName,"tag-message"=>"message !"));

var_dump($request->body);

?>