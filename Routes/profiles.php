<?php
/**
 * StormRecon - profiles.php.
 * User: simonbeattie
 * Date: 30/06/2014
 * Time: 16:28
 */

$profiles = new \Library\Profiles($pdo);

//List profiles
$app->get('/profiles', function() use($profiles)
{
   print_r($profiles->listProfiles());
});

//Get profile details
$app->get('/profiles/:id', function($id) use($profiles)
{
    print_r($profiles->profileDetails($id));
});

//Add profile
$app->post('/profiles', function() use($app, $profiles)
{
    $postData = $app->request()->post();
    $addProfile = $profiles->addProfile($postData);

    print_r($addProfile);
});

//Delete profile
$app->delete('/profiles/:id', function($id) use($app, $profiles)
{
    $response = $profiles->deleteProfile($id);

    print_r($response);
});

//Update profile
$app->put('/profiles/:id', function($id) use($app, $profiles)
{
    $putData = $app->request()->put();

    print_r($putData);
});