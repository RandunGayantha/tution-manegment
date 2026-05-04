<?php
// ============================================
// db.php - Database Connection
// ============================================

define('DB_HOST', 'DESKTOP-J4BHA1A\\MSSQLSERVER01'); // Change this to your SQL Server instance name
define('DB_USER', '');           // Your SQL Server username (if using SQL Authentication)
define('DB_PASS', '');           // Your SQL Server password (if using SQL Authentication)
define('DB_NAME', 'tuition_db');

function getDB() {
    try {
        // Build the DSN. If DB_USER and DB_PASS are empty, it will try to use Windows Authentication if TrustServerCertificate=1 and connection string allows, but usually we specify credentials or use Integrated Security.
        // For basic setup with SQL Server Auth:
        $dsn = "sqlsrv:Server=" . DB_HOST . ";Database=" . DB_NAME . ";TrustServerCertificate=1";        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        // If using Windows Authentication, leave DB_USER and DB_PASS empty and you might need to add "LoginTimeout=30" or similar to DSN, but basic sqlsrv usually defaults to it if no user/pass is provided and it's configured.
        if (empty(DB_USER)) {
             $conn = new PDO($dsn, null, null, $options);
        } else {
             $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        }
        
        return $conn;
    } catch (PDOException $e) {
        die("<div style='color:red;padding:20px;font-family:sans-serif;'>
            <h3>Database Connection Failed</h3>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <p>Make sure SQL Server is running, the <strong>sqlsrv</strong> PHP extension is enabled, and database <strong>" . DB_NAME . "</strong> exists.</p>
        </div>");
    }
}
?>
