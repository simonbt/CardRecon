<?php
/**
 * StormRecon - scans.php.
 * User: simonbeattie
 * Date: 30/06/2014
 * Time: 16:28
 */

$scans = new \Library\Scans($pdo, $queue, $logger);

//List all scans
$app->get('/scans', function() use($scans)
{
    print_r($scans->listScans());
});

$app->get('/current', function() use($scans, $app)
{
    $app->render('scans/current.phtml', array('currentScans' => $scans->listCurrentHosts()));
});

//Add new Scan
$app->post('/scans', function() use($app, $scans)
{
    $postData = $app->request()->post();
    $response = $scans->addScan($postData);

    print_r($response);
});

//Delete scan
$app->delete('/scans/:id', function($id) use($app, $scans)
{
    $deleteScan = $scans->deleteScan($id);

    print_r($deleteScan);
});

//Update scan
$app->put('/scans/:id', function($id) use($app, $scans)
{
    $putData = $app->request()->put();
    $putResponse = $scans->updateScan($id, $putData);

    print_r($putResponse);
});