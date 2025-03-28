<?php
session_start();
require_once('../../pdo/bdd.php');

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

    header('Location: gestion_fiche.php?message=suppression_reussie');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Supprimer une fiche</title>

  <!-- Tailwind -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body class="bg-gray-100 font-body">

  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="bg-white p-8 rounded-xl shadow-lg max-w-md w-full">
      <h2 class="text-xl font-title font-semibold text-red-600 mb-4 text-center">
        Confirmation de suppression
      </h2>
      <p class="text-gray-700 text-center mb-6">
        Êtes-vous sûr de vouloir supprimer la fiche de frais
        <strong class="text-gsb-blue">ID : <?= htmlspecialchars($fiche['id_fiches']) ?></strong> ?
        </p>

      <form method="post" class="flex justify-center space-x-4">
        <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 font-medium">
          Supprimer
        </button>
        <a href="gestion_fiche.php" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 font-medium">
          Annuler
        </a>
      </form>
    </div>
  </div>
</body>
</html>