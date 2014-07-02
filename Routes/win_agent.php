<?php
/**
 * StormRecon - win_agent.php.
 * User: simonbeattie
 * Date: 02/07/2014
 * Time: 09:03
 */

$agentResponse = new \Library\AgentResponse($pdo);

$app->post('/agent', function() use($app, $agentResponse)
{
    $postData = $app->request()->post();
    $agentResponse->receive($postData, $_FILES);
});