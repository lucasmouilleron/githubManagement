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

//http://fideloper.com/node-github-autodeploy
//http://behindcompanies.com/2014/01/a-simple-script-for-deploying-code-with-githubs-webhooks/

/////////////////////////////////////////////////////////////////
// needs a valid Github token as extra get parameter
$app->post("/repos/:owner/:repo/tag", function($owner, $repo) use ($app) {
    $githubToken = @getGithubToken($app);
    $tagRevision = $app->request->post("tag-revision");
    $tagName = $app->request->post("tag-name");
    $tagMessage = $app->request->post("tag-message");
    $data = '{"tag": "'.$tagName.'","message": "'.$tagMessage.'","object": "'.$tagRevision.'","type": "commit"}';
    $result = postGithub($githubToken,"repos",$owner,$repo,"git","tags",$data);
    if(!$result["status"]) echo json_encode(array("status"=>$result["status"],"hint"=>$result["content"]));

    $tagSHA = $result["content"]->sha;
    $data = '{"ref": "refs/tags/'.$tagName.'","sha": "'.$tagSHA.'"}';
    $result = postGithub($githubToken,"repos",$owner,$repo,"git","refs",$data);
    echo json_encode(array("status"=>$result["status"],"hint"=>$result["content"]));
});

/////////////////////////////////////////////////////////////////
// needs a valid Github token as extra get parameter
$app->post("/repos/:owner/:repo/hook/init", function($owner, $repo) use ($app) {
    $githubToken = @getGithubToken($app);
    $url = implodePath(API_URL,"repos",$owner,$repo,"hook");
    $data = '{"name": "web", "active": true, "events": ["create"], "config": {"url": "'.$url.'", "content_type": "json", "secret":"'.API_PRIVATE_KEY.'"}}';
    $result = postGithub($githubToken,"repos",$owner,$repo,"hooks",$data);
    echo json_encode(array("status"=>$result["status"],"hint"=>$result["content"]));
});

/////////////////////////////////////////////////////////////////
// post from github
$app->post("/repos/:owner/:repo/hook", function($owner, $repo) use ($app) {
    
    $body = $app->request()->getBody();
    $signature = $app->request->headers->get(GITHUB_API_HUB_SIGNATURE);
    checkHookSignature($body, $signature);
    if(DEBUG) appendToLog("api",LG_INFO,"hook signature ok",$signature);

    $data = json_decode($body);
    $tagType = $data->ref_type;
    if($tagType !== "tag") return;
    $owner = $data->repository->owner->login;
    $repo = $data->repository->name;
    $tagName = $data->ref;
    if(DEBUG) appendToLog("api",LG_INFO,"hook looks good",$owner,$repo,$tagName);

    $result = getGithub(GITHUB_MASTER_TOKEN,"repos",$owner,$repo,"git","refs","tags",$tagName);
    if(DEBUG) appendToLog("api",LG_INFO,"recieved tag infos",$result);
    if(!$result["status"]) fatalAndNotify(HOOKS_MAIN_EMAIL,"api",LG_ERROR,"Can't get tag infos : ".dump($result["content"]));

    $tagSHA = $result["content"]->object->sha;
    $result = getGithub(GITHUB_MASTER_TOKEN,"repos",$owner,$repo,"git","tags",$tagSHA);
    if(DEBUG) appendToLog("api",LG_INFO,"recieved tag infos 2",$result);
    if(!$result["status"]) fatalAndNotify(HOOKS_MAIN_EMAIL,"api",LG_ERROR,"Can't get tag infos : ".dump($result["content"]));
    
    $commitSHA = $result["content"]->object->sha;

    $hookPath = implodePath(HOOKS_PATH,HOOKS_DEFAULT_HOOK);
    $customHookPath = implodePath(HOOKS_PATH,$owner,$repo.".php");
    if(file_exists($customHookPath)) $hookPath = $customHookPath;

    appendToLog("api",LG_INFO,"Hooking now ...",$owner,$repo);
    include $hookPath;
});

/////////////////////////////////////////////////////////////////
$app->run();

?>