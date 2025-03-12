<?php
session_start();
require_once('../../pdo/bdd.php');

// Vérification que l'utilisateur est bien connecté et est un comptable
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Comptable') {
    header('Location: ../../login.php');
    exit();
}

// Vérification que l'ID de la fiche est bien fourni et que l'action est valide
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['action'])) {
    header('Location: ../../views/comptable.php?error=invalid_request');
    exit();
}

$action = $_GET['action'];
$ficheId = $_GET['id'];
$comptableId = $_SESSION['user']['id']; // ID du comptable connecté

try {
    if ($action === 'attribuer') {
        // Attribution : Met à jour la fiche avec l'ID du comptable connecté
        $sql = "UPDATE fiches SET id_comptable = :id_comptable WHERE id_fiches = :id_fiche";
        $stmt = $cnx->prepare($sql);
        $stmt->execute([
            ':id_comptable' => $comptableId,
            ':id_fiche' => $ficheId
        ]);

        header('Location: ../../vues/comptable.php?success=fiche_attribuee');
        exit();
    
    } elseif ($action === 'retirer') {
        // Suppression de l'attribution du comptable (mettre id_comptable à NULL)
        $sql = "UPDATE fiches SET id_comptable = NULL WHERE id_fiches = :id_fiche";
        $stmt = $cnx->prepare($sql);
        $stmt->execute([':id_fiche' => $ficheId]);

        header('Location: ../../vues/comptable.php?success=fiche_desattribuee');
        exit();
    
    } else {
        // Action invalide
        header('Location: ../../views/comptable.php?error=invalid_action');
        exit();
    }
} catch (PDOException $e) {
    header('Location: ../../views/comptable.php?error=db_error');
    exit();
}