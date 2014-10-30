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
$app->post("/repos/:owner/:repo/hook/init", function($owner, $repo) use ($app) {
    $githubToken = @getGithubToken($app);
    $token = createTokenForProject($owner,$repo);
    $url = implodePath(API_URL,"repos",$owner,$repo,"hook",$token);
    $data = '{"name": "web", "active": true, "events": ["create"], "config": {"url": "'.$url.'", "content_type": "json"}}';
    $result = postGithub($githubToken,"repos",$owner,$repo,"hooks",$data);
    echo json_encode(array("status"=>$result["status"],"hint"=>$result["content"]));
});

/////////////////////////////////////////////////////////////////
// post from github
$app->post("/repos/:owner/:repo/hook/:token", function($owner, $repo, $token) use ($app) {
    
    checkTokenForProject($owner,$repo,$token);

    $data = json_decode($app->request()->getBody());
    $tagType = $data->ref_type;
    if($tagType !== "tag") return;
    $owner = $data->repository->owner->login;
    $repo = $data->repository->name;
    $tagName = $data->ref;
    if(DEBUG) appendToLog("api","deploy",implodePath($owner,$repo,$tagName));

    $result = getGithub(GITHUB_MASTER_TOKEN,"repos",$owner,$repo,"git","refs","tags",$tagName);
    if(DEBUG) appendToLog("api","deploy",$result);
    if(!$result["status"]) {
        $hint = "Can't get tag infos : ".var_export($result["content"],true);
        appendToLog("api","deploy",$hint);
        notify(HOOKS_MAIN_EMAIL, "Can't deploy", $hint);
        return;
    }

    $tagSHA = $result["content"]->object->sha;
    $result = getGithub(GITHUB_MASTER_TOKEN,"repos",$owner,$repo,"git","tags",$tagSHA);
    if(DEBUG) appendToLog("api","deploy",$result);
    if(!$result["status"]) {
        $hint = "Can't get tag infos : ".var_export($result["content"],true);
        appendToLog("api","deploy",$hint);
        notify(HOOKS_MAIN_EMAIL, "Can't deploy", $hint);
        return;
    }
    
    $commitSHA = $result["content"]->object->sha;

    $hookPath = implodePath(HOOKS_PATH,HOOKS_DEFAULT_HOOK);
    $customHookPath = implodePath(HOOKS_PATH,"projects",$owner."__".$repo.".php");
    if(file_exists($customHookPath)) $hookPath = $customHookPath;

    appendToLog("api","deploy","Hooking for ".implodePath($owner,$repo));
    include $hookPath;
});

/////////////////////////////////////////////////////////////////
$app->run();

?>