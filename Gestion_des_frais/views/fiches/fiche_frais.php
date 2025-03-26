<?php
session_start();
require_once('../../pdo/bdd.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: index.php?action=login");
    exit();
}

// Récupérer les informations de l'utilisateur connecté
$user = $_SESSION['user'];

$source = $_GET['source'] ?? '';
$returnUrl = ($source === 'visiteur') ? '../../templates/visiteur.php' : 'gestion_fiche.php';

// Récupérer les types de frais depuis la base de données
$query = $cnx->query("SELECT id_tf, type FROM type_frais");
$typeFrais = $query->fetchAll(PDO::FETCH_ASSOC);

// Sauvegarder les données soumises dans la session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['saved_form'] = $_POST;
}

// Charger les données sauvegardées, ou initialiser des valeurs par défaut
$savedForm = $_SESSION['saved_form'] ?? [
    'op_date' => date('Y-m-d'),
    'type_frais' => [],
    'quantite' => [],
    'montant' => [],
    'date_frais' => [],
    'justificatif' => []
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de frais</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    // Longueur maximale autorisée pour le nom de fichier
    const maxFileNameLength = 30;

    // Fonction pour ajouter une ligne de frais dynamiquement
    function addExpenseRow() {
        const table = document.getElementById('expenseTable');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <select name="type_frais[]" class="p-2 border border-gray-300 rounded-md w-full" required>
                    <?php foreach ($typeFrais as $type): ?>
                        <option value="<?= $type['id_tf'] ?>"><?= $type['type'] ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <input type="number" name="quantite[]" class="p-2 border border-gray-300 rounded-md w-full" min="0" required>
            </td>
            <td>
                <div class="flex items-center">
                    <input type="text" name="montant[]" class="p-2 border border-gray-300 rounded-md w-full" min="0" required>
                    <span class="ml-2">€</span>
                </div>
            </td>
            <td>
                <input type="date" name="date_frais[]" class="p-2 border border-gray-300 rounded-md w-full" required>
            </td>
            <td>
                <input type="file" name="justificatif[]" class="p-2 border border-gray-300 rounded-md w-full" accept="image/*,application/pdf" required>
                <p class="text-xs text-gray-500 mt-1">Nom du fichier : max 30 caractères</p>
            </td>
            <td>
                <button type="button" onclick="removeExpenseRow(this)" class="text-red-600 font-bold">X</button>
            </td>
        `;
        table.appendChild(newRow);
    }

    // Fonction pour supprimer une ligne de frais
    function removeExpenseRow(button) {
        const row = button.parentElement.parentElement;
        row.remove();
    }

    // Vérifier les noms de fichiers avant l'envoi du formulaire
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelector('form').addEventListener('submit', function(event){
            const fileInputs = document.querySelectorAll('input[type="file"]');
            
            for (const fileInput of fileInputs) {
                const files = fileInput.files;

                for (const file of files) {
                    if (file.name.length > maxFileNameLength) {
                        event.preventDefault(); // Empêcher l'envoi du formulaire
                        alert(`Le fichier "${file.name}" dépasse la longueur maximale autorisée de ${maxFileNameLength} caractères.`);
                        return; // Arrêter après avoir trouvé un fichier non valide
                    }
                }
            };
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

        <!-- En-tête utilisateur -->
        <div class="mb-6">
            <p class="text-lg"><strong>Nom :</strong> <?= htmlspecialchars($user['lastname']) ?></p>
            <p class="text-lg"><strong>Prénom :</strong> <?= htmlspecialchars($user['firstname']) ?></p>
            <p class="text-lg"><strong>Matricule :</strong> <?= htmlspecialchars($user['id']) ?></p>
        </div>

        <!-- Formulaire de saisie -->
        <form action="insert_frais.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="op_date" class="block text-lg font-medium text-gray-700">Date d'ouverture</label>
                <input type="date" id="op_date" name="op_date" value="<?= date('Y-m-d') ?>" class="p-2 border border-gray-300 rounded-md w-full" />
            </div>

            <table class="w-full mb-4 border-collapse border border-gray-300">
                <thead>
                    <tr>
                        <th class="border border-gray-300 p-1">Type de frais</th>
                        <th class="border border-gray-300 p-1">Quantité</th>
                        <th class="border border-gray-300 p-1">Total</th>
                        <th class="border border-gray-300 p-1">Date</th>
                        <th class="border border-gray-300 p-5">Justificatif</th>
                    </tr>
                </thead>
                <tbody id="expenseTable">
                    <tr>
                        <td>
                            <select name="type_frais[]" class="p-2 border border-gray-300 rounded-md w-full" required>
                                <?php foreach ($typeFrais as $type): ?>
                                    <option value="<?= $type['id_tf'] ?>"><?= $type['type'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="quantite[]" class="p-2 border border-gray-300 rounded-md w-full" min="0" required>
                        </td>
                        <td>
                            <div class="flex items-center">
                                <input type="text" name="montant[]" class="p-2 border border-gray-300 rounded-md w-full" min="0" required>
                                <span class="ml-2">€</span>
                            </div>
                        </td>
                        <td>
                            <input type="date" name="date_frais[]" class="p-2 border border-gray-300 rounded-md w-full" required>
                        </td>
                        <td>
                            <input type="file" name="justificatif[]" class="p-2 border border-gray-300 rounded-md w-full" accept="image/*,application/pdf" required>
                            <p class="text-xs text-gray-500 mt-1">Nom du fichier : max 30 caractères</p>
                        </td>
                        <td>
                            <button type="button" onclick="removeExpenseRow(this)" class="text-red-600 font-bold">X</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <button type="button" onclick="addExpenseRow()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 mb-4">Ajouter une ligne</button>
            <br>
            <div class="flex justify-between mt-4">
                <a href="<?php echo isset($_GET['source']) && $_GET['source'] === 'visiteur' ? '../../templates/visiteur.php' : 'gestion_fiche.php'; ?>" 
                class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600">
                    Retour
                </a>
                <div class="flex space-x-4">
                    <button type="submit" name="submit_fiche" value="open" 
                            class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600">
                        Soumettre
                    </button>
                    <button type="submit" name="submit_fiche" value="close" 
                            class="bg-red-500 text-white px-6 py-2 rounded-md hover:bg-red-600">
                        Clôturer
                    </button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>