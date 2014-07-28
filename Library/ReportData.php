<?php
/**
 * slim -- ReportData.php
 * User: Simon Beattie
 * Date: 10/06/2014
 * Time: 16:18
 */

namespace Library;

class ReportData extends ReportsAbstract
{

    function markSystemFalsePositive($tracker)
    {
        $query = $this->getPdo()->prepare('UPDATE hosts SET status =9 WHERE tracker =?');
        $result = $query->execute([$tracker]);
        if (!$result)
        {
            die('Failed to mark false positive system' . $query->errorInfo());
        }
    }

    function markFileFalsePositive($filename, $tracker)
    {
        $query = $this->getPdo()->prepare('UPDATE results SET is_false =1 WHERE tracker =? AND file =?');
        $result = $query->execute([$filename, $tracker]);
        if (!$result)
        {
            die('Failed to mark false positive file' . $query->errorInfo());
        }
    }

    function resultsCountBySystems()
    {

        $query = $this->getPdo()->prepare('SELECT tracker, COUNT(number) AS results FROM results GROUP BY system ORDER BY COUNT(number) DESC');
        $query->execute();
        $systems = $query->fetchAll(\PDO::FETCH_ASSOC);

        return $systems;
    }

    function getResultSystems() //\Memcache $memCache
    {

//        $memCache->connect('localhost', 11211) or die ("Could not connect to the MemCache system");
//
//        $storedQuery = $memCache->get('cache');
//        if ($storedQuery)
//        {
//            return $storedQuery;
//        }

        $systems = $this->getSystemsData();

//        $memCache->set('cache', $systems, false, 2400) or die ("Ooops memcache set");

        return $systems;
    }

    function setResultsSystems() // \Memcache $memCache
    {
//        $memCache->connect('localhost', 11211) or die ("Could not connect to the MemCache system");

        $systems = $this->getSystemsData();

//        $memCache->set('cache', $systems, false, 2400) or die ("Ooops memcache set");
    }

    function getSystemsData()
    {
        $systemCounts = array();

        $systemsQuery = $this->getPdo()->prepare('SELECT host_name, filestotal, filesscanned, bytestotal, bytesscanned, tracker FROM hosts WHERE status =4');
        $systemsQuery->execute();
        $systems = $systemsQuery->fetchAll(\PDO::FETCH_ASSOC);

        $countQuery = $this->getPdo()->prepare('SELECT COUNT(DISTINCT file) FROM results WHERE tracker =? AND is_false !=1');

        foreach ($systems as $system)
        {
            $countQuery->execute([$system['tracker']]);
            $count = $countQuery->fetch(\PDO::FETCH_COLUMN);

                $system['resultCount'] = $count;


            $systemCounts[] = $system;
        }

        //Order arrays by count
        $counts = array();
        foreach ($systemCounts as $key => $value)
        {
            $counts[$key] = $value['resultCount'];
        }
        array_multisort($counts, SORT_ASC, $systemCounts);

        return $systemCounts;
    }


    function getFullResultsForTracker($tracker)
    {
        $query = $this->getPdo()->prepare('SELECT type, pattern, file, offset, md5 FROM results WHERE tracker =?');
        $query->execute([$tracker]);
        $results = $query->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }

    function getLessResultsForTracker($tracker, $page)
    {
        $itemsPerPage = 250;
        $startingFrom = ($page-1) * $itemsPerPage;
        $query = $this->getPdo()->prepare('SELECT COUNT(file) as count, file, md5, tracker FROM results WHERE tracker =? AND is_false !=1 GROUP BY file LIMIT '.$startingFrom.', '.$itemsPerPage);
        $query->execute([$tracker]);
        $results = $query->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }

    function getAllLessResults()
    {
        //$itemsPerPage = 50;
        //$startingFrom = ($page-1) * $itemsPerPage;
        $query = $this->getPdo()->prepare('SELECT COUNT(file) as count, file, md5, tracker FROM results GROUP BY file'); // LIMIT '.$startingFrom.', '.$itemsPerPage);
        $query->execute();
        $results = $query->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }

    function getFileDetails($tracker, $file)
    {
        $query = $this->getPdo()->prepare('SELECT type, pattern, file, offset, md5 FROM results WHERE tracker =? AND file =?');
        $query->execute([$tracker, $file]);
        $results = $query->fetchAll(\PDO::FETCH_ASSOC);

        return $results;
    }

}
