<?php
session_start();
require_once('../pdo/bdd.php');

// Vérifier si l'utilisateur est connecté et a le rôle "Comptable"
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Comptable') {
    header('Location: login.php');
    exit();
}

// Récupération des informations de l'utilisateur
$user = $_SESSION['user'];
$user_id = $user['id'];

// 📌 Récupérer les fiches attribuées au comptable
$sql_attribuees = "SELECT f.*, u.user_firstname, u.user_lastname, s.name_status as status
                   FROM fiches f
                   LEFT JOIN users u ON f.id_users = u.id_user
                   LEFT JOIN status_fiche s ON f.status_id = s.status_id
                   WHERE f.id_comptable = :id_comptable
                   AND f.status_id != 4
                   ORDER BY f.op_date DESC";

$stmt_attribuees = $cnx->prepare($sql_attribuees);
$stmt_attribuees->bindValue(':id_comptable', $user_id, PDO::PARAM_INT);
$stmt_attribuees->execute();
$fiches_attribuees = $stmt_attribuees->fetchAll(PDO::FETCH_ASSOC);

// 📌 Récupérer les fiches à traiter (limitées à 5)
$sql_a_traiter = "SELECT f.*, u.user_firstname, u.user_lastname, s.name_status as status
                  FROM fiches f
                  LEFT JOIN users u ON f.id_users = u.id_user
                  LEFT JOIN status_fiche s ON f.status_id = s.status_id
                  WHERE f.status_id = 1
                  ORDER BY f.op_date DESC
                  LIMIT 5";

$stmt_a_traiter = $cnx->prepare($sql_a_traiter);
$stmt_a_traiter->execute();
$fiches_a_traiter = $stmt_a_traiter->fetchAll(PDO::FETCH_ASSOC);

// Vérifier s'il y a plus de 5 fiches à traiter
$sql_total_a_traiter = "SELECT COUNT(*) FROM fiches WHERE status_id = 1";
$total_a_traiter = $cnx->query($sql_total_a_traiter)->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comptable - Tableau de bord</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">

<!-- Barre de navigation -->
<?php include('../includes/menu_comptable.php'); ?>

<!-- Section principale -->
<div class="container mx-auto p-8">
    <h1 class="text-3xl font-semibold text-center text-blue-600 mb-4">
        Bonjour <?= htmlspecialchars($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']); ?>, vous êtes <strong>Comptable</strong>.
    </h1>
    
    <!-- 📌 Fiches attribuées -->
    <div class="mt-8 bg-white shadow-md rounded-lg p-6">

        <!-- Message de succès pour l'attribution' -->
        <?php if (isset($_GET['success']) && $_GET['success'] === 'fiche_attribuee'): ?>
            <div id="successMessage" class="relative bg-green-500 text-white px-4 py-2 rounded-md text-center mb-4">
                La fiche vous a été attribuée avec succès.
                <button class="absolute top-1 right-3 text-white hover:text-gray-300 font-bold" 
                    onclick="document.getElementById('successMessage').classList.add('hidden')">
                    &times;
                </button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-500 text-white px-4 py-2 rounded-md text-center mb-4">
                Une erreur est survenue lors de l'attribution de la fiche.
            </div>
        <?php endif; ?>

        <!-- Message de succès pour la désattribution -->
        <?php if (isset($_GET['success']) && $_GET['success'] === 'fiche_desattribuee'): ?>
            <div id="desattribMessage" class="relative bg-yellow-500 text-white px-4 py-2 rounded-md text-center mb-4">
                La fiche ne vous est plus attribuée
                <button class="absolute top-1 right-3 text-white hover:text-gray-300 font-bold" 
                    onclick="document.getElementById('desattribMessage').classList.add('hidden')">
                    &times;
                </button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'desattrib_fail'): ?>
            <div id="desattribError" class="relative bg-red-500 text-white px-4 py-2 rounded-md text-center mb-4">
                Une erreur est survenue lors de la désattribution de la fiche.
                <button class="absolute top-1 right-3 text-white hover:text-gray-300 font-bold" 
                    onclick="document.getElementById('desattribError').classList.add('hidden')">
                    &times;
                </button>
            </div>
        <?php endif; ?>

        <h2 class="text-xl font-semibold text-gray-700 mb-4">📁 Fiches attribuées</h2>
        <table class="w-full border-collapse border">
            <thead>
                <tr>
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Utilisateur</th>
                    <th class="border p-2">Date d'ouverture</th>
                    <th class="border p-2">Statut</th>
                    <th class="border p-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($fiches_attribuees)): ?>
                    <?php foreach ($fiches_attribuees as $fiche): ?>
                        <tr>
                            <td class="border p-2"><?= $fiche['id_fiches'] ?></td>
                            <td class="border p-2"><?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']) ?></td>
                            <td class="border p-2"><?= $fiche['op_date'] ?></td>
                            <td class="border p-2"><?= htmlspecialchars($fiche['status']) ?></td>
                            <td class="border p-2 text-center">
                                <div class="flex justify-center space-x-4">
                                    <!-- Bouton Voir -->
                                    <a href="../views/fiches/edit_fiche.php?id=<?= $fiche['id_fiches'] ?>&source=comptable" class="text-green-600 hover:text-green-800 text-xl transition">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </a>
                                    <!-- Bouton Retirer l'attribution -->
                                    <a href="../views/fiches/attribution_fiche.php?action=retirer&id=<?= $fiche['id_fiches'] ?>" 
                                    class="text-red-600 hover:text-red-800 text-xl transition" title="Retirer l'attribution">
                                        <i class="fas fa-user-times"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="border p-2 text-center">Aucune fiche attribuée</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 📌 Fiches à traiter -->
    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">📌 Fiches à traiter</h2>
        <table class="w-full border-collapse border">
        <thead>
            <tr>
                <th class="border p-2">ID</th>
                <th class="border p-2">Utilisateur</th>
                <th class="border p-2">Date d'ouverture</th>
                <th class="border p-2">Statut</th>
                <th class="border p-2 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($fiches_a_traiter)): ?>
                <?php foreach ($fiches_a_traiter as $fiche): ?>
                    <tr>
                        <td class="border p-2"><?= $fiche['id_fiches'] ?></td>
                        <td class="border p-2"><?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']) ?></td>
                        <td class="border p-2"><?= $fiche['op_date'] ?></td>
                        <td class="border p-2"><?= htmlspecialchars($fiche['status']) ?></td>
                        <td class="border p-2 text-center">
                            <div class="flex justify-center space-x-4">
                                <!-- Bouton Voir -->
                                <a href="../views/fiches/edit_fiche.php?id=<?= $fiche['id_fiches'] ?>&source=comptable" class="text-blue-600">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <!-- Bouton Attribuer -->
                                <a href="../views/fiches/attribution_fiche.php?action=attribuer&id=<?= $fiche['id_fiches'] ?>"
                                class="text-green-600 hover:text-green-800 text-xl transition">
                                    <i class="fas fa-user-check"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="border p-2 text-center">Aucune fiche à traiter</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total_a_traiter > 5): ?>
        <div class="mt-4 text-center">
            <a href="gestion_remboursement.php" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition">
                Voir plus
            </a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>