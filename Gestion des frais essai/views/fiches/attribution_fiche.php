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
    header('Location: ../../templates/comptable.php?error=invalid_request');
    exit();
}

$action = $_GET['action'];
$ficheId = $_GET['id'];
$comptableId = $_SESSION['user']['id']; // ID du comptable connecté

try {
    $cnx->beginTransaction(); // Démarrer une transaction pour éviter les problèmes de mise à jour

    if ($action === 'attribuer') {
        // Attribution : Met à jour la fiche avec l'ID du comptable connecté et change le statut en "En cours de traitement" (status_id = 3)
        $sql = "UPDATE fiches SET id_comptable = :id_comptable, status_id = 3 WHERE id_fiches = :id_fiche";
        $stmt = $cnx->prepare($sql);
        $stmt->execute([
            ':id_comptable' => $comptableId,
            ':id_fiche' => $ficheId
        ]);

        $cnx->commit(); // Valider la transaction
        header('Location: ../../templates/comptable.php?success=fiche_attribuee');
        exit();

    } elseif ($action === 'retirer') {
        // Vérifier que la fiche est bien attribuée au comptable connecté
        $sqlCheck = "SELECT id_comptable FROM fiches WHERE id_fiches = :id_fiche AND id_comptable = :id_comptable";
        $stmtCheck = $cnx->prepare($sqlCheck);
        $stmtCheck->execute([':id_fiche' => $ficheId, ':id_comptable' => $comptableId]);

        if ($stmtCheck->rowCount() === 0) {
            // Si la fiche n'est pas attribuée au comptable connecté, empêcher la désattribution
            header('Location: ../../templates/comptable.php?error=not_assigned');
            exit();
        }

        // Désattribution : Vider id_comptable et remettre le statut à "Clôturée" (status_id = 1)
        $sql = "UPDATE fiches SET id_comptable = NULL, status_id = 1 WHERE id_fiches = :id_fiche";
        $stmt = $cnx->prepare($sql);
        $stmt->execute([':id_fiche' => $ficheId]);

        $cnx->commit(); // Valider la transaction
        header('Location: ../../templates/comptable.php?success=fiche_desattribuee');
        exit();

    } else {
        // Action invalide
        header('Location: ../../templates/comptable.php?error=invalid_action');
        exit();
    }
} catch (PDOException $e) {
    $cnx->rollBack(); // Annuler la transaction en cas d'erreur
    header('Location: ../../templates/comptable.php?error=db_error');
    exit();
}
?>