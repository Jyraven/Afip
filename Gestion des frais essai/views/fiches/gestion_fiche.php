<?php
session_start();
require_once('../../pdo/bdd.php');

// Vérification de la connexion utilisateur
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filtres
$order = $_GET['order'] ?? 'asc';
$sortBy = $_GET['sortBy'] ?? 'id_fiches';
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Construction de la requête SQL avec filtres
$sql = "SELECT f.*, u.user_firstname, u.user_lastname, s.name_status as status
        FROM fiches f
        LEFT JOIN users u ON f.id_users = u.id_user
        LEFT JOIN status_fiche s ON f.status_id = s.status_id
        WHERE 1=1";

if (!empty($status)) {
    $sql .= " AND s.name_status = :status";
}

if (!empty($search)) {
    $sql .= " AND (u.user_firstname LIKE :search OR u.user_lastname LIKE :search OR f.id_fiches LIKE :search)";
}

$sql .= " ORDER BY $sortBy $order LIMIT :limit OFFSET :offset";

$stmt = $cnx->prepare($sql);

if (!empty($status)) $stmt->bindValue(':status', $status, PDO::PARAM_STR);
if (!empty($search)) $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$fiches = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countSql = "SELECT COUNT(*) 
             FROM fiches f 
             LEFT JOIN users u ON f.id_users = u.id_user 
             LEFT JOIN status_fiche s ON f.status_id = s.status_id
             WHERE 1=1";
if (!empty($status)) $countSql .= " AND s.name_status = :status";
if (!empty($search)) $countSql .= " AND (u.user_firstname LIKE :search OR u.user_lastname LIKE :search OR f.id_fiches LIKE :search)";
$countStmt = $cnx->prepare($countSql);
if (!empty($status)) $countStmt->bindValue(':status', $status, PDO::PARAM_STR);
if (!empty($search)) $countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
$countStmt->execute();
$totalFiches = $countStmt->fetchColumn();
$totalPages = ceil($totalFiches / $limit);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Fiches de Frais</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">

<div class="bg-blue-600 text-white py-4 px-8 flex justify-between items-center">
    <div>
        <img src="assets/logo.webp" alt="Logo" class="w-32">
    </div>
    <div class="flex-grow flex justify-center space-x-8">
        <a href="../../vues/admin.php" class="text-white hover:text-gray-300">Accueil</a>
        <a href="../users/gestion_usr.php" class="text-white hover:text-gray-300">Gestion des utilisateurs</a>
    </div>
    <div class="flex items-center space-x-4">
        <span class="text-white"><?= htmlspecialchars($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']); ?></span>
        <img src="assets/profil.jpg" alt="Profil" class="w-10 h-10 rounded-full border-2 border-white">
    </div>
</div>

<div class="p-8">
    <a href="fiche_frais.php" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 mb-4 inline-block">
        Nouvelle fiche
    </a>
</div>

<div class="p-8">
    <form method="get" class="flex space-x-4 mb-6">
        <select name="sortBy" class="p-2 border rounded">
            <option value="id_fiches" <?= ($_GET['sortBy'] ?? '') == 'id_fiches' ? 'selected' : '' ?>>ID</option>
            <option value="op_date" <?= ($_GET['sortBy'] ?? '') == 'op_date' ? 'selected' : '' ?>>Date d'ouverture</option>
            <option value="cl_date" <?= ($_GET['sortBy'] ?? '') == 'cl_date' ? 'selected' : '' ?>>Date de clôture</option>
        </select>

        <select name="order" class="p-2 border rounded">
            <option value="asc" <?= ($_GET['order'] ?? '') == 'asc' ? 'selected' : '' ?>>Croissant</option>
            <option value="desc" <?= ($_GET['order'] ?? '') == 'desc' ? 'selected' : '' ?>>Décroissant</option>
        </select>

        <?php
        $query = $cnx->query("SELECT name_status FROM status_fiche");
        $statuses = $query->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <select name="status" class="p-2 border rounded">
            <option value="">Tous les statuts</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?= htmlspecialchars($status['name_status']) ?>" 
                    <?= ($_GET['status'] ?? '') == $status['name_status'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($status['name_status']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="search" placeholder="Recherche..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="p-2 border rounded">
        <button type="submit" class="bg-blue-600 text-white p-2 rounded">Filtrer</button>
    </form>

    <table class="w-full border-collapse border">
        <thead>
            <tr>
                <th class="border p-2">ID</th>
                <th class="border p-2">Utilisateur</th>
                <th class="border p-2">Date d'ouverture</th>
                <th class="border p-2">Date de clôture</th>
                <th class="border p-2">Statut</th>
                <th class="border p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fiches as $fiche): ?>
                <tr>
                    <td class="border p-2"><?= $fiche['id_fiches'] ?></td>
                    <td class="border p-2"><?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']) ?></td>
                    <td class="border p-2"><?= $fiche['op_date'] ?></td>
                    <td class="border p-2"><?= $fiche['cl_date'] ?: 'Non clôturé' ?></td>
                    <td class="border p-2"><?= isset($fiche['status']) ? htmlspecialchars($fiche['status']) : 'Non défini' ?></td>
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
        </tbody>
    </table>

    <div class="mt-4 flex justify-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php 
            $queryParams = $_GET;
            $queryParams['page'] = $i;
            $url = '?' . http_build_query($queryParams);
            ?>
            <a href="<?= $url ?>" class="px-4 py-2 <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200' ?> rounded"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>

</body>
</html>