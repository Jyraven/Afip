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
$sql = "SELECT f.*, 
               u.user_firstname, u.user_lastname, 
               s.name_status as status,
               c.user_firstname AS comptable_firstname, 
               c.user_lastname AS comptable_lastname
        FROM fiches f
        LEFT JOIN users u ON f.id_users = u.id_user
        LEFT JOIN status_fiche s ON f.status_id = s.status_id
        LEFT JOIN users c ON f.id_comptable = c.id_user
        WHERE 1=1";

// Si l'utilisateur est un visiteur, il ne voit que ses propres fiches
if ($user_role === 'Visiteur') {
    $sql .= " AND f.id_users = :user_id AND f.status_id != 4";
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
      <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body class="page-admin bg-gray-100 font-body">

<?php
// Menu selon le rôle
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
    <h1 class="text-2xl font-title text-gsb-blue mb-6">Gestion des fiches de frais</h1>

    <form method="get" class="flex flex-wrap justify-between items-end mb-6 gap-y-4">
        <input type="hidden" name="page" value="1">

        <!-- Bloc gauche : filtres + bouton -->
        <div class="flex flex-wrap items-end gap-4">
            <select name="sortBy" class="form-input w-36 h-[40px] text-sm">
            <option value="id_fiches" <?= $sortBy == 'id_fiches' ? 'selected' : '' ?>>ID</option>
            <option value="op_date" <?= $sortBy == 'op_date' ? 'selected' : '' ?>>Date d'ouverture</option>
            <option value="cl_date" <?= $sortBy == 'cl_date' ? 'selected' : '' ?>>Date de clôture</option>
            </select>

            <select name="order" class="form-input w-36 h-[40px] text-sm">
            <option value="asc" <?= $order == 'asc' ? 'selected' : '' ?>>Croissant</option>
            <option value="desc" <?= $order == 'desc' ? 'selected' : '' ?>>Décroissant</option>
            </select>

            <select name="status" class="form-input w-36 h-[40px] text-sm">
            <option value="">Tous les statuts</option>
            <option value="Ouverte" <?= $status == 'Ouverte' ? 'selected' : '' ?>>Fiches Ouvertes</option>
            <option value="Clôturée" <?= $status == 'Clôturée' ? 'selected' : '' ?>>Fiches Clôturées</option>
            <option value="En cours de traitement" <?= $status == 'En cours de traitement' ? 'selected' : '' ?>>En cours de traitement</option>
            </select>

            <button type="submit" class="btn-primary h-[40px] text-sm">Filtrer</button>
        </div>

        <!-- Bloc droite : champ de recherche -->
        <div class="flex">
            <input type="text" name="search" placeholder="Recherche..." value="<?= htmlspecialchars($search) ?>" class="form-input w-52 h-[40px] text-sm" />
        </div>
    </form>


    <!-- Tableau -->
    <div class="bg-white rounded shadow-md overflow-hidden">
        <table class="w-full border-collapse text-sm font-body">
            <thead class="bg-gsb-blue text-white">
                <tr>
                    <th class="p-3 text-left">ID</th>
                    <th class="p-3 text-left">Utilisateur</th>
                    <th class="p-3 text-left">Date d'ouverture</th>
                    <th class="p-3 text-left">Date de clôture</th>
                    <th class="p-3 text-left">Statut</th>
                    <?php if ($user_role === 'Administrateur'): ?>
                        <th class="p-3 text-left">Traité par</th>
                    <?php endif; ?>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fiches as $fiche): ?>
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3"><?= $fiche['id_fiches'] ?></td>
                        <td class="p-3"><?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']) ?></td>
                        <td class="p-3"><?= date('d/m/Y', strtotime($fiche['op_date'])) ?></td>
                        <td class="p-3"><?= $fiche['cl_date'] ? date('d/m/Y', strtotime($fiche['cl_date'])) : 'Non clôturé' ?></td>
                        <td class="p-3"><?= htmlspecialchars($fiche['status']) ?></td>
                        
                        <?php if ($user_role === 'Administrateur'): ?>
                            <td class="p-3">
                                <?= !empty($fiche['comptable_firstname']) 
                                    ? htmlspecialchars($fiche['comptable_firstname'] . ' ' . $fiche['comptable_lastname']) 
                                    : '-' ?>
                            </td>
                        <?php endif; ?>

                        <td class="p-3 text-center">
                            <div class="flex justify-center space-x-4">
                                <?php
                                    $isVisiteur = ($_SESSION['user']['role'] === 'Visiteur');
                                    if ($fiche['status_id'] != 2) {
                                        $ficheUrl = "edit_fiche.php?id={$fiche['id_fiches']}&source=gestion_fiche";
                                    } else {
                                        $ficheUrl = $isVisiteur 
                                            ? "fiche_frais.php?id_fiche={$fiche['id_fiches']}&source=visiteur" 
                                            : "fiche_frais.php?id_fiche={$fiche['id_fiches']}&source=gestion_fiche";
                                    }
                                ?>

                                <a href="<?= $ficheUrl ?>" class="text-gsb-blue hover:text-gsb-light">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="edit_fiche.php?id=<?= $fiche['id_fiches'] ?>&source=gestion_fiche&<?= htmlspecialchars($currentQueryString) ?>" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?= $baseUrl ?>&page=<?= $i ?>" class="px-4 py-2 rounded font-ui text-sm <?= $i == $page ? 'bg-gsb-blue text-white' : 'bg-gray-200 hover:bg-gray-300' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</div>
<?php include('../../includes/footer.php'); ?>
</body>