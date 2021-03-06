<?php 

////////////////////////////////////////////////////////////////
require_once __DIR__."/../libs/tools.php";
require_once __DIR__."/../libs/main-processor.php";

////////////////////////////////////////////////////////////////
$owner = "lucasmouilleron";
$repo = "webBoilerplate";
$tagName = "1234--process==test";
$tagSHA = "03d8ff99aba7a8f381a351d3c0c30aae13a99996";
$commitSHA = "f58e6d2d4715789f35493b694c132156500bc47f";

////////////////////////////////////////////////////////////////
/*$owner = "lucasmouilleron";
$repo = "webBoilerplate";
$tagName = "1234--process==test==fetch";
$tagSHA = "03d8ff99aba7a8f381a351d3c0c30aae13a99996";
$commitSHA = "10304ad26842eb304da3eb68951960c904c05d04";*/

////////////////////////////////////////////////////////////////
/*$owner = "lucasmouilleron";
$repo = "testDeploy";
$tagName = "1234--process==test==db";
$tagSHA = "03d8ff99aba7a8f381a351d3c0c30aae13a99996";
$commitSHA = "10304ad26842eb304da3eb68951960c904c05d04";*/

////////////////////////////////////////////////////////////////
/*$owner = "lucasmouilleron";
$repo = "testDeploy";
$tagName = "1234--process==test==fetch";
$tagSHA = "03d8ff99aba7a8f381a351d3c0c30aae13a99996";
$commitSHA = "10304ad26842eb304da3eb68951960c904c05d04";*/

////////////////////////////////////////////////////////////////
$mainProcessor = new MainProcessor($owner, $repo, $tagName, $tagSHA, $commitSHA);
$mainProcessor->initEnv();
$mainProcessor->run();

?>