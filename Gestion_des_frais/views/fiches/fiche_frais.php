<?php
session_start();
require_once('../../pdo/bdd.php');

if (!isset($_SESSION['user'])) {
    header("Location: index.php?action=login");
    exit();
}

$user = $_SESSION['user'];
$user_role = $user['role'];
$isComptable = ($user_role === 'Comptable');

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
      <!-- Custom CSS -->
    <link rel="stylesheet" href="../../public/css/style.css">
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

<main class="flex-1">
  <!-- Contenu principal -->
  <div class="w-full max-w-6xl mx-auto p-8 mt-10 bg-white shadow-md rounded-lg">
    <h1 class="text-2xl font-title text-gsb-blue mb-6">Fiche de frais</h1>

    <!-- Infos utilisateur -->
    <div class="mb-6 space-y-1 text-base font-body">
      <p><strong>Nom :</strong> <?= htmlspecialchars($user['lastname']) ?></p>
      <p><strong>Prénom :</strong> <?= htmlspecialchars($user['firstname']) ?></p>
      <p><strong>Matricule :</strong> <?= htmlspecialchars($user['id']) ?></p>
    </div>

    <!-- Formulaire -->
    <form action="insert_frais.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id_fiche" value="<?= htmlspecialchars($id_fiche ?? '') ?>">

      <!-- Date ouverture -->
      <div class="mb-6">
        <label for="op_date" class="form-label text-gsb-blue">Date d'ouverture</label>
        <input type="date" id="op_date" name="op_date" value="<?= date('Y-m-d') ?>" class="form-input" <?= $isComptable ? 'readonly' : '' ?> />
      </div>

      <!-- Tableau -->
      <table class="w-full text-sm font-body border-collapse border mb-4">
        <thead class="bg-gsb-blue text-white">
          <tr>
            <th class="p-2">Type de frais</th>
            <th class="p-2">Quantité</th>
            <th class="p-2">Total</th>
            <th class="p-2">Date</th>
            <th class="p-2">Justificatif</th>
            <th class="p-2"></th>
          </tr>
        </thead>
        <tbody id="expenseTable">
          <?php foreach ($lignesFrais as $ligne): ?>
            <tr class="border-b">
              <!-- Type -->
              <td class="p-2">
                <select name="type_frais[]" class="form-input" required <?= $isComptable ? 'disabled' : '' ?>>
                  <?php foreach ($typeFrais as $type): ?>
                    <option value="<?= $type['id_tf'] ?>" <?= $type['id_tf'] == $ligne['id_tf'] ? 'selected' : '' ?>>
                      <?= $type['type'] ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>

              <!-- Quantité -->
              <td class="p-2">
                <input type="number" name="quantite[]" value="<?= $ligne['quantité'] ?>" class="form-input" required <?= $isComptable ? 'readonly' : '' ?>>
              </td>

              <!-- Montant -->
              <td class="p-2">
                <div class="relative">
                  <input type="text" name="montant[]" value="<?= $ligne['total'] ?>" class="form-input pr-10" required <?= $isComptable ? 'readonly' : '' ?>>
                  <span class="absolute right-3 top-2 text-gray-500">€</span>
                </div>
              </td>

              <!-- Date -->
              <td class="p-2">
                <input type="date" name="date_frais[]" value="<?= $ligne['sp_date'] ?>" class="form-input" required <?= $isComptable ? 'readonly' : '' ?>>
              </td>

              <!-- Justificatif -->
              <td class="p-2 space-y-1">
                <?php if (!empty($ligne['justif'])): ?>
                  <a href="../../<?= htmlspecialchars($ligne['justif']) ?>" target="_blank" class="text-gsb-blue underline">Voir</a><br>
                <?php endif; ?>
                <?php if (!$isComptable): ?>
                  <input type="file" name="justificatif[]" class="form-input">
                <?php endif; ?>
                <input type="hidden" name="justificatif_existant[]" value="<?= htmlspecialchars($ligne['justif']) ?>">
                <input type="hidden" name="id_lf[]" value="<?= $ligne['id_lf'] ?>">
              </td>

              <!-- Supprimer -->
              <td class="p-2 text-center">
                <?php if (!$isComptable): ?>
                  <button type="button" onclick="removeExpenseRow(this)" class="text-red-600 font-bold">X</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Ajouter ligne -->
      <?php if (!$isComptable): ?>
        <button type="button" onclick="addExpenseRow()" class="btn-primary mb-4">Ajouter une ligne</button>
      <?php endif; ?>

      <!-- Actions -->
      <div class="flex justify-between items-center mt-6">
        <a href="<?= $returnUrl ?>" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 font-ui">Retour</a>
        <div class="flex gap-4">
          <?php if (!$isComptable): ?>
            <button type="submit" name="submit_fiche" value="open" class="btn-primary">Soumettre</button>
          <?php endif; ?>
          <button type="submit" name="submit_fiche" value="close" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 font-ui">Clôturer</button>
        </div>
      </div>
    </form>
  </div>
</main>

<script src="../../public/js/remboursement.js"></script>
<?php include('../../includes/footer.php'); ?>
</body>