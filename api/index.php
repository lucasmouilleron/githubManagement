<?php

////////////////////////////////////////////////////////////////
require_once __DIR__."/libs/tools.php";

/////////////////////////////////////////////////////////////////
// SLIM CONFIG AND MIDDLEWARES
/////////////////////////////////////////////////////////////////
$app = new \Slim\Slim(array("debug" => DEBUG));
$app->response->headers->set("Content-Type", "application/json");

/////////////////////////////////////////////////////////////////
// ERRORS
/////////////////////////////////////////////////////////////////
$app->error(function (\Exception $e) use ($app) {
    $app->halt(500, json_encode("Server error"));
});
$app->notFound(function () use ($app) {
    $app->halt(404, json_encode("Not found"));
});


/////////////////////////////////////////////////////////////////
// ROUTES
/////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////
$app->get("/", function() {
    echo json_encode(array("status"=>true));
});

/////////////////////////////////////////////////////////////////
$app->post("/repos/:owner/:repo/tag", function($owner, $repo) use ($app) {
	$githubToken = @getGithubToken($app);

});

/////////////////////////////////////////////////////////////////
$app->post("/repos/:owner/:repo/hook/init", function($owner, $repo) use ($app) {
	$githubToken = @getGithubToken($app);
	$token = createTokenForProject($owner,$repo);
    $url = implodePath(API_URL,"repos",$owner,$repo,"hook",$token);
    $data = '{"name": "web", "active": true, "events": ["push"], "config": {"url": "'.$url.'", "content_type": "json"}}';
	$result = postGithub($githubToken, "repos",$owner,$repo,"hooks",$data);
	echo json_encode(array("status"=>$result["status"],"hint"=>$result["content"]));
});

/////////////////////////////////////////////////////////////////
// get because from github
$app->get("/repos/:owner/:repo/hook/:token", function($owner, $repo, $token) use ($app) {
	checkTokenForProject($owner,$repo,$token);
    $hookPath = implodePath(HOOKS_PATH,HOOKS_DEFAULT_HOOK);
    $customHookPath = implodePath(HOOKS_PATH,"projects",$owner."__".$repo.".php");
    if(file_exists($customHookPath)) $hookPath = $customHookPath;
    
    //TODO DEPLOY
});

/////////////////////////////////////////////////////////////////
$app->run();

?>