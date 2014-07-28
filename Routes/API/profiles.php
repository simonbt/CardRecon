<?php
/**
 * StormRecon - profiles.php.
 * User: simonbeattie
 * Date: 30/06/2014
 * Time: 16:28
 */

$profiles = new \Library\Profiles($pdo, $queue, $logger);

//List profiles
$app->get('/profiles', function() use($app, $profiles)
{
    $app->render('profiles/profiles.phtml', array('profiles' => $profiles->listProfiles()));
});

//Get profile details
$app->get('/profiles/:id', function($id) use($profiles, $app)
{
    print_r($profiles->profileDetails($id));die();
    $app->render('profiles/editProfile.phtml', array('profile' => $profiles->profileDetails($id)));
});

//Add Profile
$app->get('/addprofile', function() use($app)
{
    $app->render('profiles/addProfile.phtml');
});

//Add profile
$app->post('/profiles', function() use($app, $profiles)
{
    $postData = $app->request()->post();
    $addProfile = $profiles->addProfile($postData);

    print_r($addProfile);
});

//Delete profile
$app->post('/profiles/delete/:id', function($id) use($app, $profiles)
{
    $response = $profiles->deleteProfile($id);
    if($response)
    {
        $app->render('profiles/profiles.phtml', array( 'profiles' => $profiles->listProfiles(), 'success' => array('deleted' => $id)));
    }
    else
    {
        $app->render('profiles/profiles.phtml', array( 'profiles' => $profiles->listProfiles(), 'success' => array('failed_delete' => $id)));
    }
});

//Update profile
$app->post('/profiles/:id', function($id) use($app, $profiles)
{
    $postData = $app->request()->post();

    print_r($postData);
});