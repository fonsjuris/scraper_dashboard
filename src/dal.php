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
                $result_array[$scraper_id] = array(
                    'id'=>$scraper_id,
                    'name'=>$scraper_name,
                    'timestamp'=>'-',
                    'num_processed_items'=>'-',
                    'status_id'=>'',
                    'status_text'=>'Never ran');
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
                    'status_text'=>$status_text);
            }
        }
        catch (PDOException $e)
        {
            echo $sql . "<br>" . $e->getMessage();
        }
    }

    $scraper_ids = array_keys($result_array);
    $desc = '';

    foreach ($scraper_ids as $scraper_id) {
        if ($scraper_id == '1'){  //1 Samkeppni.is scraper
            $sql = "select count(*) as c from samkeppni_entries";
            $desc = ' mál';
        } elseif ($scraper_id == '2') {  //2 Skemma.is scraper
            $sql = "select count(*) as c from skemma_thesis Where has_public_files=1";
            $desc = ' ritgerðir';
        } elseif ($scraper_id == '3') {  //3 Samkeppni.is PDF to Text Converter
            $sql = "select count(*) as c from samkeppni_extracted_text Where file_name <> ''";
            $desc = ' skjöl';
        } elseif ($scraper_id == '4') {  //4 Skemma.is PDF to Text Converter
            $sql = "select count(*) as c from skemma_extracted_text Where file_name <> ''";
            $desc = ' skjöl';
        } elseif ($scraper_id == '5') {  //5 Dagskrá dómstóla scraper
            $sql = 'select count(*) as c from lawyer_appointments';
            $desc = ' appointments';
        } elseif ($scraper_id == '7') {  //7 Uppfæra vaxtatöflur fyrir Vaxtareikni
            $sql = 'select 2 as c';  // Just a constant :)
            $desc = ' skjöl';
        }

        try {
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            $arr = $stmt->fetch(PDO::FETCH_ASSOC);
            $result_array[$scraper_id]['total_num_items'] = $arr['c'] . $desc;
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

