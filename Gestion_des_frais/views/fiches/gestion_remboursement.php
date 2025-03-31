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

$sql_historique = "SELECT f.*, 
                          u.user_firstname, u.user_lastname, 
                          s.name_status, f.total_frais, f.total_rembourse,
                          uc.user_firstname AS comptable_firstname, uc.user_lastname AS comptable_lastname
                   FROM fiches f
                   LEFT JOIN users u ON f.id_users = u.id_user
                   LEFT JOIN status_fiche s ON f.status_id = s.status_id
                   LEFT JOIN users uc ON f.id_comptable = uc.id_user
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

  <!-- Tailwind + Font Awesome + Custom CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body class="bg-gray-100 font-body">

<?php
if ($is_comptable) {
    include('../../includes/menu_comptable.php');
} elseif ($is_visiteur) {
    include('../../includes/menu_visiteur.php');
}
?>

<div class="container mx-auto p-8 bg-white shadow-md rounded-md mt-8">
  <h1 class="text-3xl font-title text-center text-gsb-blue mb-6">Gestion des Remboursements</h1>

  <div class="flex justify-center space-x-4 mb-8 text-sm font-medium">
    <?php if ($is_comptable): ?>
      <a href="?onglet=attribuees" class="px-6 py-2 rounded-md transition <?= $onglet_actif === 'attribuees' ? 'bg-gsb-blue text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
        Fiches attribuées
      </a>
      <a href="?onglet=a_traiter" class="px-6 py-2 rounded-md transition <?= $onglet_actif === 'a_traiter' ? 'bg-gsb-blue text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
        Fiches à traiter
      </a>
    <?php endif; ?>
    <a href="?onglet=historique" class="px-6 py-2 rounded-md transition <?= $onglet_actif === 'historique' ? 'bg-gsb-blue text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
      Historique
    </a>
  </div>
<!-- Fiches attribuées -->
  <?php if ($onglet_actif === 'attribuees' && $is_comptable): ?>
    <table class="w-full border-collapse text-sm table-auto shadow-sm">
      <thead class="bg-gsb-blue text-white">
        <tr>
          <th class="p-3 text-left">ID</th>
          <th class="p-3 text-left">Utilisateur</th>
          <th class="p-3 text-left">Date d'ouverture</th>
          <th class="p-3 text-left">Statut</th>
          <th class="p-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($fiches_attribuees as $fiche): ?>
          <tr class="border-b hover:bg-gray-50 transition">
            <td class="p-3 text-center"><?= $fiche['id_fiches'] ?></td>
            <td class="p-3"><?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']) ?></td>
            <td class="p-3"><?= $fiche['op_date'] ?></td>
            <td class="p-3"><?= htmlspecialchars($fiche['name_status']) ?></td>
            <td class="p-3 text-center">
              <div class="flex justify-center space-x-4">
                <a href="edit_fiche.php?id=<?= $fiche['id_fiches'] ?>&source=gestion_remboursement&onglet=attribuees" class="text-green-600 hover:text-green-800 text-lg transition">
                <i class="fas fa-money-bill-wave"></i>
                </a>
                <a href="attribution_fiche.php?action=retirer&id=<?= $fiche['id_fiches'] ?>&source=gestion_remboursement" class="text-red-600 hover:text-red-800 text-lg transition">
                  <i class="fas fa-user-times"></i>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <!-- Fiches à traiter -->
  <?php elseif ($onglet_actif === 'a_traiter' && $is_comptable): ?>
    <table class="w-full border-collapse text-sm table-auto shadow-sm">
      <thead class="bg-gsb-blue text-white">
        <tr>
          <th class="p-3 text-left">ID</th>
          <th class="p-3 text-left">Utilisateur</th>
          <th class="p-3 text-left">Date d'ouverture</th>
          <th class="p-3 text-left">Statut</th>
          <th class="p-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($fiches_a_traiter as $fiche): ?>
          <tr class="border-b hover:bg-gray-50 transition">
            <td class="p-3 text-center"><?= $fiche['id_fiches'] ?></td>
            <td class="p-3"><?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']) ?></td>
            <td class="p-3"><?= $fiche['op_date'] ?></td>
            <td class="p-3"><?= htmlspecialchars($fiche['name_status']) ?></td>
            <td class="p-3 text-center">
              <div class="flex justify-center space-x-4">
                <a href="edit_fiche.php?id=<?= $fiche['id_fiches'] ?>&source=gestion_remboursement&onglet=a_traiter" class="text-gsb-blue hover:text-gsb-light text-lg transition">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="attribution_fiche.php?action=attribuer&id=<?= $fiche['id_fiches'] ?>&source=gestion_remboursement" class="text-green-600 hover:text-green-800 text-lg transition">
                  <i class="fas fa-user-check"></i>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
<!-- Historique -->
  <?php if ($onglet_actif === 'historique'): ?>
    <table class="w-full border-collapse text-sm table-auto shadow-sm mt-6">
      <thead class="bg-gsb-blue text-white">
        <tr>
          <th class="p-3 text-left">ID</th>
          <th class="p-3 text-left">Utilisateur</th>
          <th class="p-3 text-left">Date d'ouverture</th>
          <th class="p-3 text-left">Total Frais</th>
          <th class="p-3 text-left">Total Remboursé</th>
          <th class="p-3 text-left">Traité par</th>
          <th class="p-3 text-center">Voir</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($fiches_historique as $fiche): ?>
          <tr class="border-b hover:bg-gray-50 transition">
            <td class="p-3 text-center"><?= $fiche['id_fiches'] ?></td>
            <td class="p-3"><?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']) ?></td>
            <td class="p-3"><?= $fiche['op_date'] ?></td>
            <td class="p-3"><?= $fiche['total_frais'] ?> €</td>
            <td class="p-3"><?= $fiche['total_rembourse'] ?> €</td>
            <td class="p-3"><?= htmlspecialchars($fiche['comptable_firstname'] . ' ' . $fiche['comptable_lastname']) ?></td>
            <td class="p-3 text-center">
              <a href="edit_fiche.php?id=<?= $fiche['id_fiches'] ?>&source=historique" class="text-gsb-blue hover:text-gsb-light text-lg transition" title="Voir la fiche">
                <i class="fas fa-eye"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include('../../includes/footer.php'); ?>

</body>
</html>