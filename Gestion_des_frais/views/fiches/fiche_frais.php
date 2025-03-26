<?php
session_start();
require_once('../../pdo/bdd.php');

if (!isset($_SESSION['user'])) {
    header("Location: index.php?action=login");
    exit();
}

$user = $_SESSION['user'];
$isComptable = ($user['role'] === 'Comptable');

$source = $_GET['source'] ?? '';
$returnUrl = ($source === 'visiteur') ? '../../templates/visiteur.php' : 'gestion_fiche.php';

$query = $cnx->query("SELECT id_tf, type FROM type_frais");
$typeFrais = $query->fetchAll(PDO::FETCH_ASSOC);

$id_fiche = $_GET['id_fiche'] ?? null;
$lignesFrais = [];
if ($id_fiche) {
    $stmt = $cnx->prepare("SELECT * FROM lignes_frais WHERE id_fiche = :id_fiche");
    $stmt->bindValue(':id_fiche', $id_fiche, PDO::PARAM_INT);
    $stmt->execute();
    $lignesFrais = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de frais</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    const maxFileNameLength = 30;

    function addExpenseRow() {
        const table = document.getElementById('expenseTable');
        const newRow = document.createElement('tr');
        newRow.classList.add('border-t');
        newRow.innerHTML = `
            <td class="py-2">
                <select name="type_frais[]" class="p-2 border border-gray-300 rounded-md w-full" required>
                    <?php foreach ($typeFrais as $type): ?>
                        <option value="<?= $type['id_tf'] ?>"><?= $type['type'] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td class="py-2">
                <input type="number" name="quantite[]" class="p-2 border border-gray-300 rounded-md w-full" min="0" required>
            </td>
            <td class="py-2">
                <div class="relative">
                    <input type="text" name="montant[]" class="p-2 pr-10 border border-gray-300 rounded-md w-full" required>
                    <span class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500">€</span>
                </div>
            </td>
            <td class="py-2">
                <input type="date" name="date_frais[]" class="p-2 border border-gray-300 rounded-md w-full" required>
            </td>
            <td class="py-2">
                <input type="file" name="justificatif[]" class="p-2 border border-gray-300 rounded-md w-full" accept="image/*,application/pdf" required>
                <p class="text-xs text-gray-500 mt-1">Nom du fichier : max 30 caractères</p>
            </td>
            <td class="py-2">
                <button type="button" onclick="removeExpenseRow(this)" class="text-red-600 font-bold">X</button>
            </td>
        `;
        table.appendChild(newRow);
    }

    function removeExpenseRow(button) {
        const row = button.parentElement.parentElement;
        row.remove();
    }

    document.addEventListener("DOMContentLoaded", function() {
        document.querySelector('form').addEventListener('submit', function(event){
            const fileInputs = document.querySelectorAll('input[type="file"]');
            for (const fileInput of fileInputs) {
                const files = fileInput.files;
                for (const file of files) {
                    if (file.name.length > maxFileNameLength) {
                        event.preventDefault();
                        alert(`Le fichier "${file.name}" dépasse la longueur maximale autorisée de ${maxFileNameLength} caractères.`);
                        return;
                    }
                }
            }
        });
    });
    </script>
</head>
<body class="bg-gray-100">
    <div class="bg-blue-600 text-white py-4 px-8 flex justify-between items-center">
        <div>
            <img src="../../public/images/logo.webp" alt="Logo" class="w-32">
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-white"><?= htmlspecialchars($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']); ?></span>
            <img src="assets/profil.jpg" alt="Profil" class="w-10 h-10 rounded-full border-2 border-white">
        </div>
    </div>

    <div class="w-full max-w-6xl mx-auto p-6 bg-white shadow-md rounded-lg mt-10">
        <h1 class="text-2xl font-bold text-gray-700 mb-6">Fiche de frais</h1>

        <div class="mb-6">
            <p class="text-lg"><strong>Nom :</strong> <?= htmlspecialchars($user['lastname']) ?></p>
            <p class="text-lg"><strong>Prénom :</strong> <?= htmlspecialchars($user['firstname']) ?></p>
            <p class="text-lg"><strong>Matricule :</strong> <?= htmlspecialchars($user['id']) ?></p>
        </div>

        <form action="insert_frais.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_fiche" value="<?= htmlspecialchars($id_fiche ?? '') ?>">
            <div class="mb-4">
                <label for="op_date" class="block text-lg font-medium text-gray-700">Date d'ouverture</label>
                <input type="date" id="op_date" name="op_date" value="<?= date('Y-m-d') ?>" class="p-2 border border-gray-300 rounded-md w-full" <?= $isComptable ? 'readonly' : '' ?> />
            </div>

            <table class="w-full mb-4 border-collapse border border-gray-300">
                <thead>
                    <tr>
                        <th class="border border-gray-300 p-1">Type de frais</th>
                        <th class="border border-gray-300 p-1">Quantité</th>
                        <th class="border border-gray-300 p-1">Total</th>
                        <th class="border border-gray-300 p-1">Date</th>
                        <th class="border border-gray-300 p-5">Justificatif</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="expenseTable">
                    <?php foreach ($lignesFrais as $ligne): ?>
                        <tr class="border-t">
                            <td class="py-2">
                                <select name="type_frais[]" class="p-2 border border-gray-300 rounded-md w-full" required <?= $isComptable ? 'disabled' : '' ?>>
                                    <?php foreach ($typeFrais as $type): ?>
                                        <option value="<?= $type['id_tf'] ?>" <?= $type['id_tf'] == $ligne['id_tf'] ? 'selected' : '' ?>><?= $type['type'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="py-2">
                                <input type="number" name="quantite[]" value="<?= $ligne['quantité'] ?>" class="p-2 border border-gray-300 rounded-md w-full" required <?= $isComptable ? 'readonly' : '' ?>>
                            </td>
                            <td class="py-2">
                                <div class="relative">
                                    <input type="text" name="montant[]" value="<?= $ligne['total'] ?>" class="p-2 pr-10 border border-gray-300 rounded-md w-full" required <?= $isComptable ? 'readonly' : '' ?>>
                                    <span class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500">€</span>
                                </div>
                            </td>
                            <td class="py-2">
                                <input type="date" name="date_frais[]" value="<?= $ligne['sp_date'] ?>" class="p-2 border border-gray-300 rounded-md w-full" required <?= $isComptable ? 'readonly' : '' ?>>
                            </td>
                            <td class="py-2">
                                <?php if (!empty($ligne['justif'])): ?>
                                    <a href="../../<?= htmlspecialchars($ligne['justif']) ?>" target="_blank" class="text-blue-600 underline">Voir</a><br>
                                <?php endif; ?>
                                <?php if (!$isComptable): ?>
                                    <input type="file" name="justificatif[]" class="p-2 border border-gray-300 rounded-md w-full">
                                <?php endif; ?>
                                <input type="hidden" name="justificatif_existant[]" value="<?= htmlspecialchars($ligne['justif']) ?>">
                                <input type="hidden" name="id_lf[]" value="<?= $ligne['id_lf'] ?>">
                            </td>
                            <td class="py-2">
                                <?php if (!$isComptable): ?>
                                    <button type="button" onclick="removeExpenseRow(this)" class="text-red-600 font-bold">X</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (!$isComptable): ?>
                <button type="button" onclick="addExpenseRow()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 mb-4">Ajouter une ligne</button>
            <?php endif; ?>

            <div class="flex justify-between mt-4">
                <a href="<?= $returnUrl ?>" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600">Retour</a>
                <div class="flex space-x-4">
                    <?php if (!$isComptable): ?>
                        <button type="submit" name="submit_fiche" value="open" class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600">Soumettre</button>
                    <?php endif; ?>
                    <button type="submit" name="submit_fiche" value="close" class="bg-red-500 text-white px-6 py-2 rounded-md hover:bg-red-600">Clôturer</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>