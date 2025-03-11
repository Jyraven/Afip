<?php
session_start();
require_once('../pdo/bdd.php');

// Vérifier si l'utilisateur est connecté et a le rôle "Visiteur"
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Visiteur') {
    header('Location: login.php');
    exit();
}

// Récupérer les informations de l'utilisateur
$user = $_SESSION['user'];
$user_id = $user['id'];

// Récupérer les fiches créées par l'utilisateur
$sql = "SELECT f.*, s.name_status AS status
        FROM fiches f
        LEFT JOIN status_fiche s ON f.status_id = s.status_id
        WHERE f.id_users = :id_user
        ORDER BY f.op_date DESC";

$stmt = $cnx->prepare($sql);
$stmt->bindValue(':id_user', $user_id, PDO::PARAM_INT);
$stmt->execute();
$fiches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visiteur - Gestion des Fiches de Frais</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <!-- Inclusion du menu dynamique -->
    <?php include('../includes/menu_visiteur.php'); ?>

    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-semibold text-center text-blue-600 mb-4">
            Bonjour <?= htmlspecialchars($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']); ?>, vous êtes <strong>Visiteur</strong>.
        </h1>

        <div class="flex justify-center mb-6">
            <a href="../views/fiches/fiche_frais.php" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition">Créer une nouvelle fiche de frais</a>
        </div>

        <table class="w-full border-collapse border">
            <thead>
                <tr>
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Date d'ouverture</th>
                    <th class="border p-2">Date de clôture</th>
                    <th class="border p-2">Statut</th>
                    <th class="border p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($fiches): ?>
                    <?php foreach ($fiches as $fiche): ?>
                        <tr>
                            <td class="border p-2"><?= $fiche['id_fiches'] ?></td>
                            <td class="border p-2"><?= $fiche['op_date'] ?></td>
                            <td class="border p-2"><?= $fiche['cl_date'] ?: 'Non clôturé' ?></td>
                            <td class="border p-2"><?= $fiche['status'] ?></td>
                            <td class="border p-2 flex justify-center space-x-2">
                                <a href="edit_fiche.php?id=<?= $fiche['id_fiches'] ?>" class="text-blue-600 font-bold" title="Éditer">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="delete_fiche.php?id=<?= $fiche['id_fiches'] ?>" class="text-red-600 font-bold" title="Supprimer">
                                    <i class="fas fa-times"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="border p-2 text-center">Aucune fiche de frais trouvée.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>