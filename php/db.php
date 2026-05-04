<?php


define('DB_SERVER', 'MSI\\SQLEXPRESS'); 
define('DB_NAME', 'tuition_db');

function getDB() {
   
    $connectionInfo = array(
        "Database" => DB_NAME,
        "CharacterSet" => "UTF-8",
        "ReturnDatesAsStrings" => true
    );

    
    $conn = sqlsrv_connect(DB_SERVER, $connectionInfo);

    if ($conn === false) {
        
        $errors = sqlsrv_errors();
        $error_msg = "";
        foreach ($errors as $error) {
            $error_msg .= "SQLSTATE: ".$error['SQLSTATE']."<br />";
            $error_msg .= "Code: ".$error['code']."<br />";
            $error_msg .= "Message: ".$error['message']."<br />";
        }

        die("<div style='color:red;padding:20px;font-family:sans-serif;'>
                <h3>Database Connection Failed (MS SQL Server)</h3>
                <p>" . $error_msg . "</p>
                <p>Make sure <strong>SQL Express</strong> service is running and database <strong>" . DB_NAME . "</strong> exists in SSMS.</p>
            </div>");
    }

    return $conn;
}

?>