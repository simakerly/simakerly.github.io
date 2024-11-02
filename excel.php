<?php

// save in database
$dataExcel = array();

try{
    $databaseName = 'mysqlitedb.db';
    if (!file_exists($databaseName)){
        $db = new PDO("sqlite:$databaseName");
        $db->exec("CREATE TABLE people (id INTEGER PRIMARY KEY AUTOINCREMENT,name VARCHAR(255), company VARCHAR(255), job VARCHAR(255), mail VARCHAR(255), created_at DATETIME)");
    }
    $db = new PDO("sqlite:$databaseName");
   // $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sth = $db->prepare("SELECT * FROM people");
    $sth->execute();
    $res = $sth->fetchAll();

    if (is_array($res) && !empty($res)) {
        $dataExcel[] = array('ID','NAME', 'COMPANY', 'JOB', 'MAIL', 'DATE');        
    }
    foreach ($res as $row){
        $dataExcel[] = $row;        
    }


}catch (PDOException $e){
    echo $e->getMessage();
}

/**
 * Function helper for create report csv
 */
if (is_array($dataExcel) && count($dataExcel) > 0) {

    header('Content-Type: application/excel');
    header('Content-Disposition: attachment; filename="sample.csv"');
    foreach ($dataExcel as $key => $value) {
        $data[] = "{$value[0]},{$value[1]},{$value[2]},{$value[3]},{$value[4]},{$value[5]}";
    }

   $fp = fopen('php://output', 'w');
    foreach ( $data as $line ) {
        $val = explode(",", $line);
        fputcsv($fp, $val);
    }
    fclose($fp); 

} else {
    echo "Not found data in your database.";
}
