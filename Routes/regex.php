<?php
/**
 * StormRecon - regex.php.
 * User: simonbeattie
 * Date: 30/06/2014
 * Time: 16:59
 */

$regex = new \Library\Regex($pdo, $queue, $logger);

//List regex patterns
$app->get('/regex', function() use($regex)
{
    print_r($regex->listRegex());
});

//Add regex pattern
$app->post('/regex', function() use($app, $regex)
{
    $postData = $app->request()->post();
    $response = $regex->addRegex($postData);

    print_r($response);
});

//Delete regex pattern
$app->delete('/regex/:id', function($id) use($app, $regex)
{
    $deleteRegex = $regex->deleteRegex($id);

    print_r($deleteRegex);
});

//Update regex patten
$app->put('/regex/:id', function($id) use($app, $regex)
{
    $putData = $app->request()->put();
    $putResponse = $regex->updateRegex($id, $putData);

    print_r($putResponse);
});