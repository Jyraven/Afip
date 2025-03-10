<?php
require_once __DIR__ . '/../../pdo/bdd.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['id'])) {
        echo json_encode(["status" => "error", "message" => "Aucun utilisateur spécifié."]);
        exit;
    }

    $id = $_POST['id'];

    // Vérifier si l'utilisateur existe
    $checkUser = $cnx->prepare("SELECT COUNT(*) FROM users WHERE id_user = ?");
    $checkUser->execute([$id]);

    if ($checkUser->fetchColumn() == 0) {
        echo json_encode(["status" => "error", "message" => "Utilisateur introuvable."]);
        exit;
    }

    // Supprimer l'utilisateur
    $deleteUser = $cnx->prepare("DELETE FROM users WHERE id_user = ?");
    $deleteUser->execute([$id]);

    echo json_encode(["status" => "success", "message" => "Utilisateur supprimé avec succès !"]);
    exit;
} else {
    echo json_encode(["status" => "error", "message" => "Requête invalide."]);
    exit;
}
?>