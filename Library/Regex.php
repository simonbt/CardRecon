<?php
/**
 * StormRecon - Regex.php.
 * User: simonbeattie
 * Date: 30/06/2014
 * Time: 16:58
 */

namespace Library;


class Regex extends ReconAbstract{

    public function listRegex()
    {
        $regexQuery = $this->getPdo()->prepare('SELECT * FROM regex');
        $regexQuery->execute();
        $regex = $regexQuery->fetchAll(\PDO::FETCH_ASSOC);

        return $regex;
    }

    public function addRegex($postData)
    {
        $regex = array('name', 'pattern');

        foreach ($regex as $key)
        {
            if (!array_key_exists($key, $postData))
            {
                die('You must post a ' . $key);
            }
        }

        $name = $postData['name'];
        $pattern = $postData['pattern'];
        $regexQuery = $this->getPdo()->prepare('INSERT INTO regex (name, pattern) VALUES(?, ?)');
        $response = $regexQuery->execute(array($name, $pattern));

        if (!$response)
        {
            die('Failed to insert new regex pattern -- ' . print_r($regexQuery->errorInfo()));
        }

        return true;
    }

    public function deleteRegex($id)
    {

        $regexQuery = $this->getPdo()->prepare('DELETE FROM regex WHERE id =?');
        $response = $regexQuery->execute(array($id));

        if (!$response)
        {
            die('Failed to delete regex pattern id: ' . $id . ' -- ' . print_r($regexQuery->errorInfo()));
        }

        return true;
    }

    public function updateRegex($id, $putData)
    {
        $regex = array('name', 'pattern');

        foreach ($regex as $key)
        {
            if (array_key_exists($key, $putData))
            {
                $regexQuery = $this->getPdo()->prepare('UPDATE regex SET ' . $key . ' =? WHERE id =?');
                $response = $regexQuery->execute(array($putData[$key], $id));

                if (!$response)
                {
                    die('Failed to update regex pattern value: ' . $key . ' -- ' . print_r($regexQuery->errorInfo()));
                }
            }
            else
            {
                die('You haven\'t posted any data!');
            }
        }

        return true;
    }
} 