<?php
/**
 * StormRecon - regex.php.
 * User: simonbeattie
 * Date: 30/06/2014
 * Time: 16:59
 */

$regex = new \Library\Regex($pdo, $queue, $logger);

//List regex patterns
$app->get('/regex', function() use($regex, $app)
{
    $app->render('regex/regex.phtml',array('profiles' => $regex->listRegex()));
});

//Add regex pattern
$app->get('/addregex', function() use($app, $regex)
{
    $app->render('regex/addRegex.phtml');

});

//Add regex pattern
$app->post('/regex', function() use($app, $regex)
{
    $postData = $app->request()->post();
    $response = $regex->addRegex($postData);
    if($response)
    {
        $app->render('regex/regex.phtml', array( 'profiles' => $regex->listRegex(), 'success' => 'added'));
    }
    else
    {
        $app->render('regex/regex.phtml', array( 'profiles' => $regex->listRegex(), 'success' => 'failed'));
    }

});

//Delete regex pattern
$app->delete('/regex/delete/:id', function($id) use($app, $regex)
{
    $deleteRegex = $regex->deleteRegex($id);

});

//Update regex patten
$app->put('/regex/:id', function($id) use($app, $regex)
{
    $putData = $app->request()->put();
    $putResponse = $regex->updateRegex($id, $putData);

    print_r($putResponse);
});