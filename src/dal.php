<?php

function get_saved_theses()
{
    $connection = _get_connection();
    $sql = 'SELECT id, title, author, has_public_files, url FROM skemma_thesis';
    try {
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $arr;
    }
    catch (PDOException $e)
    {
        echo $sql . "<br>" . $e->getMessage();
    }
}


function get_status_of_all_processes()
{
    $connection = _get_connection();
    $sql = 'SELECT id, name FROM scraper';
    try {
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $scrapers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e)
    {
        echo $sql . "<br>" . $e->getMessage();
        return array();
    }


    $result_array = array();
    foreach ($scrapers as $scraper){
        $scraper_id = $scraper['id'];
        $scraper_name = $scraper['name'];

        $sql = 'select timestamp, status_id, status_text, num_processed_items from scraper_run where scraper_id = :scraper_id order by timestamp desc limit 1';
        try {
            $stmt = $connection->prepare($sql);
            $stmt->bindParam(':scraper_id', $scraper_id);
            $stmt->execute();
            $scraper_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (sizeof($scraper_results) == 0){
                array_push($result_array,
                    array(
                        'id'=>$scraper_id,
                        'name'=>$scraper_name,
                        'timestamp'=>'-',
                        'num_processed_items'=>'-',
                        'status_id'=>'',
                        'status_text'=>'Never ran'));
            } else {
                $timestamp = $scraper_results[0]['timestamp'];
                $status_id = $scraper_results[0]['status_id'];
                $status_text = $scraper_results[0]['status_text'];
                $num_processed_items = $scraper_results[0]['num_processed_items'];
                array_push($result_array,
                    array(
                        'id'=>$scraper_id,
                        'name'=>$scraper_name,
                        'timestamp'=>$timestamp,
                        'num_processed_items'=>$num_processed_items,
                        'status_id'=>$status_id,
                        'status_text'=>$status_text));
            }
        }
        catch (PDOException $e)
        {
            echo $sql . "<br>" . $e->getMessage();
        }
    }

    return $result_array;
}

function _get_connection(){
    $password =  getenv('MYSQL_PW');
    $username = getenv('MYSQL_USER');
    $servername = "fjv3-cluster.cluster-cdeoqg4injkb.eu-west-1.rds.amazonaws.com";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=mannsi;charset=utf8", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e)
    {
        echo "Connection failed: " . $e->getMessage();
    }

    return $conn;
}

