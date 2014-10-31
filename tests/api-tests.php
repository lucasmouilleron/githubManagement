<?php

////////////////////////////////////////////////////////////////
require_once __DIR__."/../api/libs/tools.php";

////////////////////////////////////////////////////////////////
$owner = "lucasmouilleron";
$repo = "webBoilerplate";
$revision = "f58e6d2d4715789f35493b694c132156500bc47f";
$tagName = time()."--process-test";
/*$owner = "lucasmouilleron";
$repo = "testDeploy";
$revision = "10304ad26842eb304da3eb68951960c904c05d04";
$tagName = time()."--process-test--db";*/

////////////////////////////////////////////////////////////////

//$request = Requests::post(implodePath(API_URL,"repos",$owner,$repo,"hook","init?github-token=".GITHUB_MASTER_TOKEN));

$request = Requests::post(implodePath(API_URL,"repos",$owner,$repo,"tag?github-token=".GITHUB_MASTER_TOKEN), array(), array("tag-revision"=>$revision,"tag-name"=>$tagName,"tag-message"=>"message !"));

var_dump($request->body);

?>