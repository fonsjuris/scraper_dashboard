<?php

function get_status_of_all_processes()
{
    $connection = _get_connection();
    $sql = 'SELECT id, name, item_description FROM scraper';
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
        $item_description = $scraper['item_description'];

        $sql = 'select timestamp, status_id, status_text, num_processed_items from scraper_run where scraper_id = :scraper_id order by timestamp desc limit 1';
        try {
            $stmt = $connection->prepare($sql);
            $stmt->bindParam(':scraper_id', $scraper_id);
            $stmt->execute();
            $scraper_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (sizeof($scraper_results) == 0){
                $result_array[$scraper_id] = array(
                    'id'=>$scraper_id,
                    'name'=>$scraper_name,
                    'timestamp'=>'-',
                    'num_processed_items'=>'-',
                    'status_id'=>'',
                    'status_text'=>'Never ran',
                    'item_description'=>$item_description);
            } else {
                $timestamp = $scraper_results[0]['timestamp'];
                $status_id = $scraper_results[0]['status_id'];
                $status_text = $scraper_results[0]['status_text'];
                $num_processed_items = $scraper_results[0]['num_processed_items'];
                $result_array[$scraper_id] = array(
                    'id'=>$scraper_id,
                    'name'=>$scraper_name,
                    'timestamp'=>$timestamp,
                    'num_processed_items'=>$num_processed_items,
                    'status_id'=>$status_id,
                    'status_text'=>$status_text,
                    'item_description'=>$item_description);
            }
        }
        catch (PDOException $e)
        {
            echo $sql . "<br>" . $e->getMessage();
        }
    }

    $scraper_ids = array_keys($result_array);

    foreach ($scraper_ids as $scraper_id) {

        $sql = "SELECT sum(num_processed_items) as c FROM scraper_run WHERE scraper_id=:scraper_id";

        try {
            $stmt = $connection->prepare($sql);
            $stmt->bindParam(':scraper_id', $scraper_id);

            $stmt->execute();
            $arr = $stmt->fetch(PDO::FETCH_ASSOC);
            $result_array[$scraper_id]['total_num_items'] = $arr['c'];
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

