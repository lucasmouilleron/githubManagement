<?php

////////////////////////////////////////////////////////////////
require_once __DIR__."/libs/tools.php";

////////////////////////////////////////////////////////////////
//$request = Requests::post(getAPIURL()."/repos/lucasmouilleron/testDeploy/hook/init?github-token=".GITHUB_MASTER_TOKEN);

$revision = "b07f87a235b7df73b35b9747560850c10ab0ca79";
$request = Requests::post(getAPIURL()."/repos/lucasmouilleron/testDeploy/tag?github-token=".GITHUB_MASTER_TOKEN, array(), array("tag-revision"=>$revision,"tag-name"=>"tag".time(),"tag-message"=>"message !"));

var_dump($request->body);

?>