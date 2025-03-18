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

// Récupération de l'ID de la fiche de frais
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de fiche invalide.";
    exit();
}

$ficheId = $_GET['id'];

// Requête pour récupérer les informations de la fiche
$sql = "SELECT fiches.*, users.user_firstname, users.user_lastname, users.id_user AS user_id, status_fiche.name_status AS status
        FROM fiches
        LEFT JOIN users ON fiches.id_users = users.id_user
        LEFT JOIN status_fiche ON fiches.status_id = status_fiche.status_id
        WHERE fiches.id_fiches = :id_fiche";
$stmt = $cnx->prepare($sql);
$stmt->bindValue(':id_fiche', $ficheId, PDO::PARAM_INT);
$stmt->execute();
$fiche = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fiche) {
    echo "Fiche introuvable.";
    exit();
}

// Récupération des lignes de frais
$ligneFraisSql = "SELECT lignes_frais.*, type_frais.type
                  FROM lignes_frais
                  LEFT JOIN type_frais ON lignes_frais.id_tf = type_frais.id_tf
                  WHERE lignes_frais.id_fiche = :id_fiche";
$ligneFraisStmt = $cnx->prepare($ligneFraisSql);
$ligneFraisStmt->bindValue(':id_fiche', $ficheId, PDO::PARAM_INT);
$ligneFraisStmt->execute();
$lignesFrais = $ligneFraisStmt->fetchAll(PDO::FETCH_ASSOC);

// Vérification du statut de la fiche pour l'affichage conditionnel
$isOuverte = $fiche['status'] === 'Ouverte';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de frais</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="max-w-7xl mx-auto p-6 bg-white shadow-md rounded-md relative">
        <h1 class="text-2xl font-bold mb-4 text-center">Fiche de frais numéro <?= htmlspecialchars($fiche['id_fiches']); ?></h1>
        <p><strong>ID utilisateur :</strong> <?= htmlspecialchars($fiche['user_id']); ?></p>
        <p><strong>Utilisateur :</strong> <?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']); ?></p>
        <p><strong>Status :</strong> <?= htmlspecialchars($fiche['status']); ?></p>

        <h2 class="text-xl font-bold mt-6">Lignes de Frais</h2>
        <table class="border-collapse border border-gray-300 w-full mt-4">
            <thead>
                <tr>
                    <th class="border border-gray-300 p-2 text-center">Type</th>
                    <th class="border border-gray-300 p-2 text-center">Quantité</th>
                    <th class="border border-gray-300 p-2 text-center">Total</th>
                    <th class="border border-gray-300 p-2 text-center">Justificatif</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignesFrais as $ligne): ?>
                    <tr>
                        <td class="border border-gray-300 p-2 text-center"><?= htmlspecialchars($ligne['type']); ?></td>
                        <td class="border border-gray-300 p-2 text-center"><?= htmlspecialchars($ligne['quantité']); ?></td>
                        <td class="border border-gray-300 p-2 text-center"><?= htmlspecialchars($ligne['total']); ?> €</td>
                        <td class="border border-gray-300 p-2 text-center">
                            <?php if ($ligne['justif']): ?>
                                <a href="<?= htmlspecialchars($ligne['justif']); ?>" target="_blank" class="text-blue-500 underline">Voir</a>
                            <?php else: ?>
                                Aucun
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- 🔹 Formulaire uniquement si l'utilisateur n'est PAS un comptable -->
        <?php if (!$is_comptable && $isOuverte): ?>
            <form method="POST" enctype="multipart/form-data" class="mt-6">
                <h3 class="text-lg font-bold">Ajouter des frais</h3>
                <div id="newLines"></div>
                <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded-md mt-4" onclick="addNewLine()">Ajouter une ligne</button>
                <div class="mt-6">
                    <button type="submit" name="submit_fiche" value="open" class="bg-green-500 text-white px-6 py-2 rounded-md">Soumettre</button>
                    <button type="submit" name="submit_fiche" value="close" class="bg-red-500 text-white px-6 py-2 rounded-md">Clôturer</button>
                </div>
            </form>
        <?php endif; ?>

        <!-- Bouton de retour -->
        <div class="absolute bottom-6 right-6">
            <a href="<?= ($_SESSION['user']['role'] === 'Comptable') ? 'gestion_remboursement.php' : 'gestion_fiche.php' ?>" 
            class="bg-gray-500 text-white px-4 py-2 rounded-md">
                Retour
            </a>
        </div>


        <!-- 🔹 Suppression des scripts d'ajout de ligne si l'utilisateur est un comptable -->
        <?php if (!$is_comptable): ?>
            <script>
                function addNewLine() {
                    const container = document.getElementById('newLines');
                    const lineId = Date.now();
                    const newLine = document.createElement('div');
                    newLine.className = "grid grid-cols-6 gap-4 mt-4 items-center";
                    newLine.setAttribute('data-line-id', lineId);
                    newLine.innerHTML = `
                        <select name="type_frais[]" class="p-2 border rounded">
                            <?php foreach ($typeFrais as $type): ?>
                                <option value="<?= $type['id_tf']; ?>"><?= $type['type']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="quantite[]" class="p-2 border rounded" placeholder="Quantité" required min="0">
                        <input type="text" name="montant[]" class="p-2 border rounded" placeholder="Total" required>
                        <input type="date" name="sp_date[]" class="p-2 border rounded" required>
                        <input type="file" name="justificatif[]" class="p-2 border rounded w-80" required>
                        <button type="button" class="text-red-500 font-bold text-3xl ml-8" onclick="removeLine(${lineId})">×</button>
                    `;
                    container.appendChild(newLine);
                }

                function removeLine(lineId) {
                    const line = document.querySelector(`[data-line-id="${lineId}"]`);
                    if (line) {
                        line.remove();
                    }
                }
            </script>
        <?php endif; ?>
    </body>
</html>