<?php
session_start();
require_once('../includes/bdd.php');

// Vérification de la connexion utilisateur
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Vérifier si l'ID de la fiche est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: gestion_fiches.php');
    exit();
}

$id_fiche = (int)$_GET['id'];

// Récupération des informations de la fiche
$query = $cnx->prepare("SELECT * FROM fiches WHERE id_fiches = :id_fiches");
$query->bindValue(':id_fiches', $id_fiche, PDO::PARAM_INT);
$query->execute();
$fiche = $query->fetch();

if (!$fiche) {
    header('Location: gestion_fiches.php');
    exit();
}

// Suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deleteStmt = $cnx->prepare("DELETE FROM fiches WHERE id_fiches = :id_fiches");
    $deleteStmt->bindValue(':id_fiches', $id_fiche, PDO::PARAM_INT);
    $deleteStmt->execute();

    header('Location: gestion_fiches.php?message=suppression_reussie');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer une fiche</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <!-- Fenêtre modale -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-bold mb-4 text-red-600">Confirmer la suppression</h2>
            <p class="mb-4">Êtes-vous sûr de vouloir supprimer la fiche de frais <strong>ID: <?= htmlspecialchars($fiche['id_fiches']) ?></strong> ?</p>
            <form method="post">
                <div class="flex space-x-4">
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Supprimer
                    </button>
                    <a href="gestion_fiches.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
