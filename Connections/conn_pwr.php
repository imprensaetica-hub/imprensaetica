<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"

$hostname_conn_pwr = "u714904829-ietica-imprensaetica-7cbc.i.aivencloud.com";
$database_conn_pwr = "defaultdb";
$username_conn_pwr = "avnadmin";
$password_conn_pwr = "AVNS_4hrDCIXPJ45O64cogR2"; 

$conn_pwr = new mysqli($hostname_conn_pwr, $username_conn_pwr, $password_conn_pwr, $database_conn_pwr);

if ($conn_pwr->connect_error) {
    die("Connection failed: " . $conn_pwr->connect_error);
}
?>