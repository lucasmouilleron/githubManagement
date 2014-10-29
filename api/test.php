<?php

////////////////////////////////////////////////////////////////
require_once __DIR__."/libs/tools.php";

////////////////////////////////////////////////////////////////
$request = Requests::post(getAPIURL()."/repos/lucasmouilleron/slimBoilerplate/hook/init?github-token=".TESTS_GITHUB_TOKEN);
var_dump($request->body);

//http://lucas.haveidols.com:8070/githubManagement/api//repos/lucasmouilleron/slimBoilerplate/hook/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.Imx1Y2FzbW91aWxsZXJvbnNsaW1Cb2lsZXJwbGF0ZSI.0Q2Mv6d_VdPa30-j4hYhrZ-fygFnR7sH1eHMMAsfqpU

?>