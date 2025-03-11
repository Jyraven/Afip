<?php
session_start();
require_once('../pdo/bdd.php');

// Vérifier si l'utilisateur est connecté et a le rôle "Visiteur"
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Visiteur') {
    header('Location: login.php'); 
    exit();
}

// Récupérer les informations de l'utilisateur connecté
$user = $_SESSION['user'];
$user_id = $user['id'];

// Récupérer les fiches créées par l'utilisateur
$sql = "SELECT f.*, s.name_status AS status
        FROM fiches f
        LEFT JOIN status_fiche s ON f.status_id = s.status_id
        WHERE f.id_users = :id_user
        ORDER BY f.op_date DESC"; // Trier par date d'ouverture

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">

<!-- Inclusion du menu visiteur -->
<?php include('../includes/menu_visiteur.php'); ?>

<!-- Section principale -->
<div class="container mx-auto p-8">
    <h1 class="text-3xl font-semibold text-center text-blue-600 mb-4">
        Bonjour <?= htmlspecialchars($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']); ?>, vous êtes <strong>Visiteur</strong>.
    </h1>

    <!-- Bouton pour créer une nouvelle fiche de frais -->
    <div class="flex justify-center mb-6">
        <a href="fiche_frais.php" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition">Créer une nouvelle fiche de frais</a>
    </div>
    <form action="../Auth/logout.php" method="post" class="flex justify-center mt-8">
        <button type="submit" class="bg-red-600 text-white px-6 py-3 rounded-md hover:bg-red-700 transition">
            Se déconnecter
        </button>
    </form>

    <!-- Affichage des fiches de frais -->
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
            <?php if (!empty($fiches)): ?>
                <?php foreach ($fiches as $fiche): ?>
                    <tr>
                        <td class="border p-2 text-center"><?= $fiche['id_fiches'] ?></td>
                        <td class="border p-2 text-center"><?= $fiche['op_date'] ?></td>
                        <td class="border p-2 text-center"><?= $fiche['cl_date'] ?: 'Non clôturé' ?></td>
                        <td class="border p-2 text-center"><?= htmlspecialchars($fiche['status']) ?></td>
                        <td class="border p-2 flex justify-center space-x-4">
                            <!-- Bouton Voir la fiche -->
                            <a href="../views/fiches/edit_fiche.php?id=<?= $fiche['id_fiches'] ?>" class="text-blue-600 font-bold" title="Voir la fiche">
                                <i class="fas fa-eye"></i>
                            </a>
                            <!-- Bouton Supprimer la fiche -->
                            <a href="../views/fiches/delete_fiche.php?id=<?= $fiche['id_fiches'] ?>" class="text-red-600 font-bold" title="Supprimer">
                                <i class="fas fa-trash"></i>
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