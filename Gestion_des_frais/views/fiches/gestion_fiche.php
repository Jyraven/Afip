<?php
session_start();
require_once('../../pdo/bdd.php');

// Vérification de la connexion utilisateur
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Récupération des informations utilisateur
$user = $_SESSION['user'];
$user_id = $user['id'];
$user_role = $user['role'];
$is_comptable = ($user_role === 'Comptable');

// Détermination du menu à inclure selon le rôle de l'utilisateur
$menuFile = '';

// Pagination
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filtres avec valeurs par défaut
$order = $_GET['order'] ?? 'asc';
$sortBy = $_GET['sortBy'] ?? 'id_fiches';
$status = $_GET['status'] ?? ($is_comptable ? 'Ouverte' : '');
$search = $_GET['search'] ?? '';

// Construction de la requête SQL avec filtres
$sql = "SELECT f.*, u.user_firstname, u.user_lastname, s.name_status as status
        FROM fiches f
        LEFT JOIN users u ON f.id_users = u.id_user
        LEFT JOIN status_fiche s ON f.status_id = s.status_id
        WHERE 1=1";

// Si l'utilisateur est un visiteur, il ne voit que ses propres fiches
if ($user_role === 'Visiteur') {
    $sql .= " AND f.id_users = :user_id";
}

// Exclure les fiches "En cours de traitement" pour un comptable
if ($is_comptable) {
    $sql .= " AND f.status_id != 3";
}

// Ajout des filtres sélectionnés
if (!empty($status)) {
    $sql .= " AND s.name_status = :status";
}
if (!empty($search)) {
    $sql .= " AND (u.user_firstname LIKE :search OR u.user_lastname LIKE :search OR f.id_fiches LIKE :search)";
}

// Ajout de l’ordre et de la pagination
$sql .= " ORDER BY $sortBy $order LIMIT :limit OFFSET :offset";

// Exécution de la requête
$stmt = $cnx->prepare($sql);
if ($user_role === 'Visiteur') {
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
}
if (!empty($status)) {
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
}
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$fiches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Correction du comptage total des fiches
$countSql = "SELECT COUNT(*) FROM fiches f 
             LEFT JOIN users u ON f.id_users = u.id_user 
             LEFT JOIN status_fiche s ON f.status_id = s.status_id
             WHERE 1=1";

if ($user_role === 'Visiteur') {
    $countSql .= " AND f.id_users = :user_id";
}
if ($is_comptable) {
    $countSql .= " AND f.status_id != 3"; // Exclure les fiches en cours de traitement
}
if (!empty($status)) {
    $countSql .= " AND s.name_status = :status";
}
if (!empty($search)) {
    $countSql .= " AND (u.user_firstname LIKE :search OR u.user_lastname LIKE :search OR f.id_fiches LIKE :search)";
}

// Exécution du comptage
$countStmt = $cnx->prepare($countSql);
if ($user_role === 'Visiteur') {
    $countStmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
}
if (!empty($status)) {
    $countStmt->bindValue(':status', $status, PDO::PARAM_STR);
}
if (!empty($search)) {
    $countStmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$countStmt->execute();
$totalFiches = $countStmt->fetchColumn();

// Correction du nombre total de pages pour éviter une page vide
$totalPages = max(ceil($totalFiches / $limit), 1);

// Générer l'URL avec les filtres pour la pagination
$baseUrl = "gestion_fiche.php?sortBy=$sortBy&order=$order&status=$status&search=" . urlencode($search);

$currentQuery = $_SERVER['QUERY_STRING'];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Fiches de Frais</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">

    <?php
    // Détermination du menu à inclure selon le rôle de l'utilisateur
    $menuFile = '';

    if ($user_role === 'Administrateur') {
        $menuFile = '../../includes/menu_admin.php';
    } elseif ($user_role === 'Comptable') {
        $menuFile = '../../includes/menu_comptable.php';
    } elseif ($user_role === 'Visiteur') {
        $menuFile = '../../includes/menu_visiteur.php';
    }

    if (!empty($menuFile) && file_exists($menuFile)) {
        include($menuFile);
    }
    ?>

    
    <div class="p-8">
        <form method="get" class="flex space-x-4 mb-6">
            <input type="hidden" name="page" value="1">
            
            <!-- Filtre sur le tri -->
            <select name="sortBy" class="p-2 border rounded">
                <option value="id_fiches" <?= $sortBy == 'id_fiches' ? 'selected' : '' ?>>ID</option>
                <option value="op_date" <?= $sortBy == 'op_date' ? 'selected' : '' ?>>Date d'ouverture</option>
                <option value="cl_date" <?= $sortBy == 'cl_date' ? 'selected' : '' ?>>Date de clôture</option>
            </select>

            <!-- Filtre sur l'ordre -->
            <select name="order" class="p-2 border rounded">
                <option value="asc" <?= $order == 'asc' ? 'selected' : '' ?>>Croissant</option>
                <option value="desc" <?= $order == 'desc' ? 'selected' : '' ?>>Décroissant</option>
            </select>

            <!-- Filtre sur le statut -->
            <select name="status" class="p-2 border rounded">
                <option value="">Tous les statuts</option>
                <option value="Ouverte" <?= $status == 'Ouverte' ? 'selected' : '' ?>>Fiches Ouvertes</option>
                <option value="Clôturée" <?= $status == 'Clôturée' ? 'selected' : '' ?>>Fiches Clôturées</option>
            </select>

            <!-- Champ de recherche -->
            <input type="text" name="search" placeholder="Recherche..." value="<?= htmlspecialchars($search) ?>" class="p-2 border rounded">
            
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
                        <td class="border p-2"><?= date('d/m/Y', strtotime($fiche['op_date'])) ?></td>
                        <td class="border p-2"><?= $fiche['cl_date'] ? date('d/m/Y', strtotime($fiche['cl_date'])) : 'Non clôturé' ?></td>
                        <td class="border p-2"><?= htmlspecialchars($fiche['status']) ?></td>
                        <td class="border p-2 text-center">
                            <div class="flex justify-center space-x-4">
                                 <a href="edit_fiche.php?id=<?= $fiche['id_fiches'] ?>&source=gestion_fiche&<?= htmlspecialchars($currentQuery) ?>" class="text-blue-600">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="edit_fiche.php?id=<?= $fiche['id_fiches'] ?>&source=gestion_fiche&<?= htmlspecialchars($currentQueryString) ?>" class="delete-btn text-red-600 font-bold">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="mt-4 flex justify-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="<?= $baseUrl ?>&page=<?= $i ?>" class="px-4 py-2 <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200' ?> rounded"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>

</body>
</html>