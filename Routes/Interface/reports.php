<?php
/**
 * reports.php
 * User: Simon Beattie
 * Date: 11/06/2014
 * Time: 12:39
 */

$app->get('/reports', function () use($app, $reportData, $memcache)
{
    $systems = $reportData->getResultSystems($memcache);

    //echo '<pre>';print_r($systems);echo'</pre>';die();
    $app->render('reports/index.phtml', array('systems' => $systems));
});

$app->post('/reports', function () use($app, $reportData, $memcache)
{
    $post = $app->request()->post();
    $reportData->setResultsSystems($memcache);
    $systems = $reportData->getResultSystems($memcache);

    if (!array_key_exists('falsepositive', $post))
    {
        $app->render('reports/index.phtml', array('systems' => $systems, 'failed' => '1'));
        return;
    }

    foreach ($post['falsepositive'] as $tracker)
    {
        $reportData->markSystemFalsePositive($tracker);
    }

    $app->render('reports/index.phtml', array('systems' => $systems, 'success' => '1'));

});

$app->get('/reports/cache', function () use($app, $reportData, $memcache)
{
    $reportData->setResultsSystems($memcache);
    $systems = $reportData->getResultSystems($memcache);
    $app->render('reports/index.phtml', array('systems' => $systems));
});

$app->post('/results', function () use($app, $reportData, $memcache)
{
    $post = $app->request()->post();


    if (!array_key_exists('falsepositive', $post))
    {
        $files = $reportData->getLessResultsForTracker($post['tracker'], $post['page']);
        $app->render('reports/results.phtml', array('files' => $files, 'page' => $post['page'], 'pages' =>  $post['pages'], 'tracker' => $post['tracker'], 'failed' => '1'));
        return;
    }

    foreach ($post['falsepositive'] as $filename)
    {
        $reportData->markFileFalsePositive($post['tracker'], $filename);
    }

    $files = $reportData->getLessResultsForTracker($post['tracker'], $post['page']);
    $reportData->setResultsSystems($memcache);
    $app->render('reports/results.phtml', array('files' => $files, 'page' => $post['page'], 'pages' =>  $post['pages'], 'tracker' => $post['tracker'], 'success' => '1'));


});

$app->get('/results', function () use($app, $reportData)
{
    $tracker = $app->request()->get();
    $totalPages = $tracker['total'];
    $files = $reportData->getLessResultsForTracker($tracker['tracker'], $tracker['page']);

    $app->render('reports/results.phtml', array('files' => $files, 'page' => $tracker['page'], 'pages' =>  $totalPages, 'tracker' => $tracker['tracker']));

});

$app->get('/details', function () use($app, $reportData)
{
    $get = $app->request()->get();
    $files = $reportData->getFileDetails($get['tracker'], $get['file']);
    $app->render('reports/details.phtml', array('details' => $files, 'file' => $get['file'], 'tracker' => $get['tracker'], 'page' => $get['page'], 'total' => $get['total']));

});



$app->get('/test', function() use($app, $reportData)
{
    $reportData->getSystemsData();
});

