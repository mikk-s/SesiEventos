<?php


 $host = "localhost";
 $db = "sesieventos";
 $user = "root";
 $pass = "";

try {
  $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);


  
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
   die("Erro de conexão: " . $e->getMessage());
}