<?php 

require_once __DIR__."/../api/libs/tools.php";

$owner = "lucasmouilleron";
$repo = "testDeploy";
$tagName = "1234--process-test--db";
$tagSHA = "03d8ff99aba7a8f381a351d3c0c30aae13a99996";
$commitSHA = "10304ad26842eb304da3eb68951960c904c05d04";

include implodePath(PROCESSORS_PATH,"main.php");

?>