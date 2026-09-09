<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"

$hostname_conn_pwr = "u714904829-ietica-imprensaetica-7cbc.i.aivencloud.com";
$database_conn_pwr = "defaultdb";
$username_conn_pwr = "avnadmin";
$password_conn_pwr = "AVNS_4hrDCIXPJ45O64cogR2";

// Verifica se a extensão mysqli está disponível
if (!class_exists('mysqli')) {
    die('<h2>Erro de configuração do servidor</h2>
         <p>A extensão <strong>MySQLi</strong> não está habilitada no PHP.</p>
         <p>Para corrigir, ative a extensão no arquivo <code>php.ini</code> ou instale o pacote <code>php-mysql</code> no servidor.</p>');
}

$conn_pwr = new mysqli($hostname_conn_pwr, $username_conn_pwr, $password_conn_pwr, $database_conn_pwr);

if ($conn_pwr->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn_pwr->connect_error);
}
?>