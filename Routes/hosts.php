<?php
/**
 * StormRecon - hosts.php.
 * User: simonbeattie
 * Date: 01/07/2014
 * Time: 07:42
 */

$hosts = new \Library\Hosts($pdo);

//List hosts
$app->get('/hosts', function() use($hosts)
{
    print_r($hosts->listHosts());
});

//Get host details
$app->get('/hosts/:id', function($id) use($hosts)
{
    print_r($hosts->hostDetails($id));
});


//Add host
$app->post('/hosts', function() use($app, $hosts)
{
    $postData = $app->request()->post();
    $response = $hosts->addHost($postData);

    print_r($response);
});

//Delete host
$app->delete('/hosts/:id', function($id) use($app, $hosts)
{
    $deleteData = $hosts->deleteHost($id);
    print_r($deleteData);
});

//Update host
$app->put('/hosts/:id', function($id) use($app, $hosts)
{
    $putData = $app->request()->put();

    print_r($putData);
});