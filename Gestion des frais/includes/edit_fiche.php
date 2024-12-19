<?php
session_start();
require_once('../includes/bdd.php');

// Vérification de la connexion utilisateur
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Récupération de l'ID de la fiche de frais
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ficheId = $_GET['id'];

    // Requête pour récupérer les informations de la fiche
    $sql = "SELECT fiches.*, users.user_firstname, users.user_lastname, status_fiche.name_status AS status
            FROM fiches
            LEFT JOIN users ON fiches.id_users = users.id_user
            LEFT JOIN status_fiche ON fiches.status_id = status_fiche.status_id
            WHERE fiches.id_fiches = :id_fiche;";
    $stmt = $cnx->prepare($sql);
    $stmt->bindValue(':id_fiche', $ficheId, PDO::PARAM_INT);
    $stmt->execute();
    $fiche = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fiche) {
        echo "Fiche introuvable.";
        exit();
    }

    // Requête pour récupérer les lignes de frais avec le type de frais et le justificatif
    $ligneFraisSql = "SELECT lignes_frais.*, type_frais.type, lignes_frais.justif 
                      FROM lignes_frais 
                      LEFT JOIN type_frais ON lignes_frais.id_tf = type_frais.id_tf
                      WHERE lignes_frais.id_fiche = :id_fiche";
    $ligneFraisStmt = $cnx->prepare($ligneFraisSql);
    $ligneFraisStmt->bindValue(':id_fiche', $ficheId, PDO::PARAM_INT);
    $ligneFraisStmt->execute();
    $lignesFrais = $ligneFraisStmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    echo "ID de fiche invalide.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualisation de la Fiche de Frais</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">

<div class="bg-blue-600 text-white py-4 px-8 flex justify-between items-center">
    <div>
        <img src="assets/logo.webp" alt="Logo" class="w-32">
    </div>
    <div class="flex-grow flex justify-center space-x-8">
        <a href="../vues/admin.php" class="text-white hover:text-gray-300">Accueil</a>
        <a href="gestion_utilisateurs.php" class="text-white hover:text-gray-300">Gestion des utilisateurs</a>
    </div>
    <div class="flex items-center space-x-4">
        <span class="text-white"><?= htmlspecialchars($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']); ?></span>
        <img src="assets/profil.jpg" alt="Profil" class="w-10 h-10 rounded-full border-2 border-white">
    </div>
</div>

<div class="p-8">
    <h1 class="text-2xl font-bold">Détails de la Fiche de Frais</h1>
    <div class="mt-4">
        <p><strong>ID Fiche:</strong> <?= htmlspecialchars($fiche['id_fiches']); ?></p>
        <p><strong>Utilisateur:</strong> <?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']); ?></p>
        <p><strong>Date d'ouverture:</strong> <?= $fiche['op_date']; ?></p>
        <p><strong>Date de clôture:</strong> <?= $fiche['cl_date'] ?: 'Non clôturé'; ?></p>
        <p><strong>Statut:</strong> <?= isset($fiche['status']) ? htmlspecialchars($fiche['status']) : 'Non défini'; ?></p>
    </div>

    <!-- Affichage des lignes de frais -->
    <h2 class="text-xl font-bold mt-6">Lignes de Frais</h2>
    <?php if (count($lignesFrais) > 0): ?>
        <table class="w-full mt-4 border-collapse border">
            <thead>
                <tr>
                    <th class="border p-2">Catégorie</th>
                    <th class="border p-2">Quantité</th>
                    <th class="border p-2">Total</th>
                    <th class="border p-2">Justificatif</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignesFrais as $ligne): ?>
                    <tr>
                        <td class="border p-2"><?= htmlspecialchars($ligne['type']); ?></td> <!-- Affichage du type de frais -->
                        <td class="border p-2"><?= htmlspecialchars($ligne['quantité']); ?></td> <!-- Affichage de la quantité -->
                        <td class="border p-2"><?= htmlspecialchars($ligne['total']); ?> €</td> <!-- Affichage du total -->
                        <td class="border p-2">
                            <?php if ($ligne['justif']): ?>
                                <a href="<?= htmlspecialchars($ligne['justif']); ?>" target="_blank" class="text-blue-600">Voir le justificatif</a>
                            <?php else: ?>
                                <span>Aucun justificatif</span>
                            <?php endif; ?>
                        </td> <!-- Affichage du justificatif -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune ligne de frais trouvée pour cette fiche.</p>
    <?php endif; ?>

    <div class="mt-6">
        <a href="gestion_fiche.php" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700">Retour à la gestion des fiches</a>
    </div>
</div>

</body>
</html>