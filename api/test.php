<?php
require 'config/koneksi.php';

$result = $conn->query("SHOW TABLES");

echo "Tabel dalam database: <br>";
while ($row = $result->fetch_array()) {
    echo $row[0] . "<br>";
}
?>
