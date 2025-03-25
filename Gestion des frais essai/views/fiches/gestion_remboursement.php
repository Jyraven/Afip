<?php
session_start();
require_once('../../pdo/bdd.php');

if (!isset($_SESSION['user'])) {
    header('Location: ../../login.php');
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$is_comptable = $user['role'] === 'Comptable';
$is_visiteur = $user['role'] === 'Visiteur';

// Déterminer l'onglet actif
$onglet_actif = isset($_GET['onglet']) ? $_GET['onglet'] : ($is_visiteur ? 'historique' : 'attribuees');

if ($is_comptable) {
    $sql_attribuees = "SELECT f.*, u.user_firstname, u.user_lastname, s.name_status 
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

    $sql_a_traiter = "SELECT f.*, u.user_firstname, u.user_lastname, s.name_status 
                      FROM fiches f
                      LEFT JOIN users u ON f.id_users = u.id_user
                      LEFT JOIN status_fiche s ON f.status_id = s.status_id
                      WHERE f.status_id = 1
                      ORDER BY f.op_date DESC";

    $stmt_a_traiter = $cnx->prepare($sql_a_traiter);
    $stmt_a_traiter->execute();
    $fiches_a_traiter = $stmt_a_traiter->fetchAll(PDO::FETCH_ASSOC);
}

$sql_historique = "SELECT f.*, u.user_firstname, u.user_lastname, s.name_status, f.total_frais, f.total_rembourse
                   FROM fiches f
                   LEFT JOIN users u ON f.id_users = u.id_user
                   LEFT JOIN status_fiche s ON f.status_id = s.status_id
                   WHERE f.status_id = 4";

if ($is_visiteur) {
    $sql_historique .= " AND f.id_users = :id_user";
}

$sql_historique .= " ORDER BY f.op_date DESC";
$stmt_historique = $cnx->prepare($sql_historique);

if ($is_visiteur) {
    $stmt_historique->bindValue(':id_user', $user_id, PDO::PARAM_INT);
}

$stmt_historique->execute();
$fiches_historique = $stmt_historique->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Remboursements</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">

<?php
if ($is_comptable) {
    include('../../includes/menu_comptable.php');
} elseif ($is_visiteur) {
    include('../../includes/menu_visiteur.php');
}
?>

<div class="container mx-auto p-8 bg-white shadow-md rounded-md">
    <h1 class="text-3xl font-semibold text-center text-blue-600 mb-4">Gestion des Remboursements</h1>

    <div class="flex justify-center space-x-4 mb-6">
        <?php if ($is_comptable): ?>
            <a href="?onglet=attribuees" class="px-6 py-2 rounded-md <?= $onglet_actif === 'attribuees' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' ?>">
                Fiches attribuées
            </a>
            <a href="?onglet=a_traiter" class="px-6 py-2 rounded-md <?= $onglet_actif === 'a_traiter' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' ?>">
                Fiches à traiter
            </a>
        <?php endif; ?>
        <a href="?onglet=historique" class="px-6 py-2 rounded-md <?= $onglet_actif === 'historique' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' ?>">
            Historique
        </a>
    </div>

    <!-- Fiche attribuees -->
    <?php if ($onglet_actif === 'attribuees' && $is_comptable): ?>
        <table class="w-full border-collapse border bg-white">
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
                <?php foreach ($fiches_attribuees as $fiche): ?>
                    <tr>
                        <td class="border p-2 text-center"><?= $fiche['id_fiches'] ?></td>
                        <td class="border p-2"><?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']) ?></td>
                        <td class="border p-2"><?= $fiche['op_date'] ?></td>
                        <td class="border p-2"><?= htmlspecialchars($fiche['name_status']) ?></td>
                        <td class="border p-2 text-center">
                            <a href="edit_fiche.php?id=<?= $fiche['id_fiches'] ?>&source=gestion_remboursement&onglet=attribuees" class="text-blue-600">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="attribution_fiche.php?action=retirer&id=<?= $fiche['id_fiches'] ?>&source=gestion_remboursement" class="text-red-600">
                                <i class="fas fa-user-times"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <!-- Fiche à traiter -->
    <?php elseif ($onglet_actif === 'a_traiter' && $is_comptable): ?>
        <table class="w-full border-collapse border bg-white">
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
                <?php foreach ($fiches_a_traiter as $fiche): ?>
                    <tr>
                        <td class="border p-2 text-center"><?= $fiche['id_fiches'] ?></td>
                        <td class="border p-2"><?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']) ?></td>
                        <td class="border p-2"><?= $fiche['op_date'] ?></td>
                        <td class="border p-2"><?= htmlspecialchars($fiche['name_status']) ?></td>
                        <td class="border p-2 text-center">
                            <a href="edit_fiche.php?id=<?= $fiche['id_fiches'] ?>&source=gestion_remboursement&onglet=a_traiter" class="text-blue-600">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="attribution_fiche.php?action=attribuer&id=<?= $fiche['id_fiches'] ?>&source=gestion_remboursement" class="text-green-600">
                                <i class="fas fa-user-check"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Historique -->
    <?php if ($onglet_actif === 'historique'): ?>
        <table class="w-full border-collapse border bg-white mt-8">
            <thead>
                <tr>
                    <th class="border p-2">ID</th>
                    <th class="border p-2">Utilisateur</th>
                    <th class="border p-2">Date d'ouverture</th>
                    <th class="border p-2">Total Frais</th>
                    <th class="border p-2">Total Remboursé</th>
                    <th class="border p-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fiches_historique as $fiche): ?>
                    <tr>
                        <td class="border p-2 text-center"><?= $fiche['id_fiches'] ?></td>
                        <td class="border p-2"><?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']) ?></td>
                        <td class="border p-2"><?= $fiche['op_date'] ?></td>
                        <td class="border p-2"><?= $fiche['total_frais'] ?> €</td>
                        <td class="border p-2"><?= $fiche['total_rembourse'] ?> €</td>
                        <td class="border p-2 text-center">
                            <a href="edit_fiche.php?id=<?= $fiche['id_fiches'] ?>&source=historique" class="text-blue-600" title="Voir la fiche">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>