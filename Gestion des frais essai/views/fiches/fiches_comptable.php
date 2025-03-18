<?php
require_once('../../pdo/bdd.php');

// Récupération de l'ID du comptable connecté
$user_id = $_SESSION['user']['id'];

// Récupérer les fiches attribuées au comptable
$sql_attribuees = "SELECT f.*, u.user_firstname, u.user_lastname, s.name_status 
                   FROM fiches f
                   LEFT JOIN users u ON f.id_users = u.id_user
                   LEFT JOIN status_fiche s ON f.status_id = s.status_id
                   WHERE f.id_comptable = :id_comptable
                   ORDER BY f.op_date DESC";

$stmt_attribuees = $cnx->prepare($sql_attribuees);
$stmt_attribuees->bindValue(':id_comptable', $user_id, PDO::PARAM_INT);
$stmt_attribuees->execute();
$fiches_attribuees = $stmt_attribuees->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les fiches clôturées à traiter
$sql_a_traiter = "SELECT f.*, u.user_firstname, u.user_lastname, s.name_status 
                  FROM fiches f
                  LEFT JOIN users u ON f.id_users = u.id_user
                  LEFT JOIN status_fiche s ON f.status_id = s.status_id
                  WHERE f.status_id = 1
                  ORDER BY f.op_date DESC";

$stmt_a_traiter = $cnx->prepare($sql_a_traiter);
$stmt_a_traiter->execute();
$fiches_a_traiter = $stmt_a_traiter->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mt-8 bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">📌 Fiches attribuées</h2>
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
                    <td class="border p-2"><?= $fiche['id_fiches'] ?></td>
                    <td class="border p-2"><?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']) ?></td>
                    <td class="border p-2"><?= $fiche['op_date'] ?></td>
                    <td class="border p-2"><?= htmlspecialchars($fiche['name_status']) ?></td>
                    <td class="border p-2 text-center">
                        <div class="flex justify-center space-x-4">
                            <a href="../views/fiches/edit_fiche.php?id=<?= $fiche['id_fiches'] ?>" class="text-blue-600 hover:text-blue-800 text-xl transition" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="gestion_remboursement.php?id=<?= $fiche['id_fiches'] ?>" class="text-green-600 hover:text-green-800 text-xl transition" title="Traiter">
                                <i class="fas fa-money-bill-wave"></i>
                            </a>
                            <a href="../views/fiches/attribution_fiche.php?action=retirer&id=<?= $fiche['id_fiches'] ?>" class="text-red-600 hover:text-red-800 text-xl transition" title="Désattribuer">
                                <i class="fas fa-user-times"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="mt-8 bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-semibold text-gray-700 mb-4">📌 Fiches à traiter</h2>
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
                    <td class="border p-2"><?= $fiche['id_fiches'] ?></td>
                    <td class="border p-2"><?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']) ?></td>
                    <td class="border p-2"><?= $fiche['op_date'] ?></td>
                    <td class="border p-2"><?= htmlspecialchars($fiche['name_status']) ?></td>
                    <td class="border p-2 text-center">
                        <div class="flex justify-center space-x-4">
                            <a href="../views/fiches/edit_fiche.php?id=<?= $fiche['id_fiches'] ?>" class="text-blue-600 hover:text-blue-800 text-xl transition" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="../views/fiches/attribution_fiche.php?action=attribuer&id=<?= $fiche['id_fiches'] ?>" class="text-green-600 hover:text-green-800 text-xl transition" title="Attribuer">
                                <i class="fas fa-user-check"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>