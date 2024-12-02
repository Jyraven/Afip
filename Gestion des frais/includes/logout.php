<?php
session_start(); // Démarre la session
session_destroy(); // Détruit toutes les données de la session
header('Location: ../index.php?action=login'); // Redirige vers la page de connexion
exit(); // Termine le script
?>