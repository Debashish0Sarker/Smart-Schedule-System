<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=smart_schedule', 'root', '');
    echo "Connected successfully";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>