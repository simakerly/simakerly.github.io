<?php 
/**
* @author Anibal Copitan
* @url www.acopitan.blogspot.com
* 
* Page for send mail o other action Ajax.
**/

$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : false;
$mailVisit = (isset($_REQUEST['email'])) ? $_REQUEST['email'] : '';
$name = (isset($_REQUEST['nombre'])) ? $_REQUEST['nombre'] .' '. $_REQUEST['apellido'] : '';
$company = (isset($_REQUEST['compania'])) ? $_REQUEST['compania'] : '-';
$job = (isset($_REQUEST['puesto'])) ? $_REQUEST['puesto'] : '-';
$created_at = date('Y-m-d H:i:s');

// save in database
try{
    $databaseName = 'mysqlitedb.db';
    if (!file_exists($databaseName)){
        $db = new PDO("sqlite:$databaseName");
        $db->exec("CREATE TABLE people (id INTEGER PRIMARY KEY AUTOINCREMENT,name VARCHAR(255), company VARCHAR(255), job VARCHAR(255), mail VARCHAR(255), created_at DATETIME)");
    }
    $db = new PDO("sqlite:$databaseName");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
    $db->exec("INSERT INTO people (name, company, job, mail, created_at) VALUES ('$name', '$company', '$job', '$mailVisit', '$created_at')");
}catch (PDOException $e){
    echo $e->getMessage();
}

if ($action == 'mail') {
    // The message
    $messageLab = "
    Gracias por contactarnos $name  \r\n\r\n
    Descarga el Ranking en \r\n   
    ";
    // In case any of our lines are larger than 70 characters, we should use wordwrap()
    $messageLab = wordwrap($messageLab, 70, "\r\n");
    
    /**_layout mail***/
    $to = $mailVisit;
    $fromName = ''; // Enter mail Comapny contacto@dominio.com 
    $message = $messageLab;

    /* GET File Variables */
    $fileName = 'reporte.pdf';
    $tmpName = dirname(__FILE__) . "/assets/docs/{$fileName}"; 
    $fileType = 'application/pdf'; //$_FILES['attachment']['type']; 

    /* Start of headers */ 
    $headers = "From: $fromName"; 

    if (file($tmpName)) {
        /* Reading file ('rb' = read binary)  */
        $file = fopen($tmpName,'rb');
        $data = fread($file,filesize($tmpName));
        fclose($file); 

        /* a boundary string */
        $randomVal = md5(time()); 
        $mimeBoundary = "==Multipart_Boundary_x{$randomVal}x"; 

        /* Header for File Attachment */
        $headers .= "\nMIME-Version: 1.0\n"; 
        $headers .= "Content-Type: multipart/mixed;\n" ;
        $headers .= " boundary=\"{$mimeBoundary}\""; 

        /* Multipart Boundary above message */
        $message .= "This is a multi-part message in MIME format.\n\n" . 
        "--{$mimeBoundary}\n" . 
        "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . 
        "Content-Transfer-Encoding: 7bit\n\n" . 
        $message . "\n\n"; 
        /* Encoding file data */
        $data = chunk_split(base64_encode($data)); 

        /* Adding attchment-file to message*/
        $message .= "--{$mimeBoundary}\n" . 
        "Content-Type: {$fileType};\n" . 
        " name=\"{$fileName}\"\n" . 
        "Content-Transfer-Encoding: base64\n\n" . 
        $data . "\n\n" . 
        "--{$mimeBoundary}--\n";
    }
    /**_layout mail***/

    // Send
    $flag = mail ("$to", "Future Brand descarga ranking", "$message", "$headers"); 
    
    if ($flag) {
        echo 1;
    } else {
        echo 0;        
    }
} else {
    echo 0;
}

