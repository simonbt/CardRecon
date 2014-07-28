<?php
/**
 * StormRecon - Profiles.php.
 * User: simonbeattie
 * Date: 30/06/2014
 * Time: 16:38
 */

namespace Library;


class Profiles extends ReconAbstract{

    public function listProfiles()
    {
        $profilesQuery = $this->getPdo()->prepare('SELECT profile FROM profiles');
        $profilesQuery->execute();
        $profiles = $profilesQuery->fetchAll(\PDO::FETCH_ASSOC);

        return $profiles;
    }

    public function profileDetails($profile)
    {
        $profileQuery = $this->getPdo()->prepare('SELECT * from profiles where profile =?');
        $profileQuery->execute(array($profile));
        $profileDetails = $profileQuery->fetchAll(\PDO::FETCH_ASSOC);

        return $profileDetails;
    }


    public function addProfile($postData)
    {
        $profileFields = array(
            'profile_name',
            'username',
            'password',
            'domain',
            'exts',
            'ignore_exts',
            'dirs',
            'ignore_dirs',
            'regex',
            'path',
            'serverurl',
            'serveruser',
            'serverpass',
            'service_description',
            'debug_level',
            'concurrent_deployments',
            'creditcards',
            'zipfiles',
            'memory',
            'mask_data'
        );

        foreach ($profileFields as $key)
        {
            if (!array_key_exists($key, $postData))
            {
                die('You must post a ' . $key);
            }
        }

        $profileQuery = $this->getPdo()->prepare('INSERT INTO profiles (profile_name, username, password, domain, exts, ignore_exts, dirs, ignore_dirs, regex, path, serverurl, serveruser, serverpass, service_description, debug_level, concurrent_deployments, creditcards ,zipfiles, memory, mask_data) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $response = $profileQuery->execute(array_values($postData));

        if (!$response)
        {
            die('Failed to add new profile pattern -- ' . print_r($profileQuery->errorInfo()));
        }

        return true;
    }

    public function deleteProfile($id)
    {

        $profileQuery = $this->getPdo()->prepare('DELETE FROM profiles WHERE id =?');
        $response = $profileQuery->execute(array($id));

        if (!$response)
        {
            die('Failed to delete profile id: ' . $id . ' -- ' . print_r($profileQuery->errorInfo()));
        }

        return true;
    }

    public function updateProfile($id, $putData)
    {
        $profileFields = array('profile_name', 'scantype', 'username', 'password', 'domain');

        foreach ($profileFields as $key)
        {
            if (array_key_exists($key, $putData))
            {
                $profileQuery = $this->getPdo()->prepare('UPDATE profiles SET ' . $key . ' =? WHERE id =?');
                $response = $profileQuery->execute(array($putData[$key], $id));

                if (!$response)
                {
                    die('Failed to update profile value: ' . $key . ' -- ' . print_r($profileQuery->errorInfo()));
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