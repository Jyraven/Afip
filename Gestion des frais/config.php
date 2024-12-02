<?php
include('config.php');
try {
    $cnx = new PDO("mysql:host=localhost;dbname=afip_slam1", "root", "root");
    $cnx->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}