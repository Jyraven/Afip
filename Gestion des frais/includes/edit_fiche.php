<?php
session_start();
require_once('../includes/bdd.php');

// Vérification de la connexion utilisateur
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

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

// Récupérer les types de frais pour le formulaire
$query = $cnx->query("SELECT id_tf, type FROM type_frais");
$typeFrais = $query->fetchAll(PDO::FETCH_ASSOC);

// Gestion du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_fiche'])) {
        $action = $_POST['submit_fiche'];
        
        // Traitement des lignes de frais
        foreach ($_POST['type_frais'] as $index => $typeFraisId) {
            $quantite = $_POST['quantite'][$index];
            // Conversion du montant avec une virgule en point pour la base de données
            $montant = str_replace(',', '.', $_POST['montant'][$index]);
            $dateFrais = $_POST['sp_date'][$index];
            $justificatif = null;

            // Vérification et gestion du justificatif
            if (isset($_FILES['justificatif']['name'][$index]) && $_FILES['justificatif']['error'][$index] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['justificatif']['tmp_name'][$index];
                $fileName = $_FILES['justificatif']['name'][$index];
                $fileSize = $_FILES['justificatif']['size'][$index];
                $fileType = mime_content_type($fileTmpPath);
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
                
                // Vérification si le type MIME du fichier est autorisé
                if (in_array($fileType, $allowedTypes)) {
                    // Générer un nom unique pour éviter les conflits
                    $destination = "../justificatif/" . uniqid() . "_" . $fileName;

                    // Déplacer le fichier vers le répertoire de destination
                    move_uploaded_file($fileTmpPath, $destination);
                    $justificatif = $destination;
                } else {
                    echo "Le fichier doit être une image (JPEG, PNG, GIF) ou un PDF.";
                    exit(); // Arrêter l'exécution si le fichier n'est pas valide
                }
            }

            $sql = "INSERT INTO lignes_frais (id_fiche, id_tf, quantité, total, sp_date, justif) 
                    VALUES (:id_fiche, :id_tf, :quantite, :montant, :sp_date, :justif)";
            $stmt = $cnx->prepare($sql);
            $stmt->execute([
                ':id_fiche' => $ficheId,
                ':id_tf' => $typeFraisId,
                ':quantite' => $quantite,
                ':montant' => $montant,
                ':sp_date' => $dateFrais,
                ':justif' => $justificatif,
            ]);
        }

        // Si action = 'close', mise à jour du statut
        if ($action === 'close') {
            $updateSql = "UPDATE fiches SET status_id = (SELECT status_id FROM status_fiche WHERE name_status = 'Clôturée') WHERE id_fiches = :id_fiche";
            $updateStmt = $cnx->prepare($updateSql);
            $updateStmt->execute([':id_fiche' => $ficheId]);
        }

        header('Location: gestion_fiche.php');
        exit();
    }
}

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

        <form method="POST" enctype="multipart/form-data" class="mt-6">
            <?php if ($isOuverte): ?>
                <h3 class="text-lg font-bold">Ajouter des frais</h3>
                <div id="newLines"></div>
                <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded-md mt-4" onclick="addNewLine()">Ajouter une ligne</button>
            <?php endif; ?>

            <div class="mt-6">
                <?php if ($isOuverte): ?>
                    <button type="submit" name="submit_fiche" value="open" class="bg-green-500 text-white px-6 py-2 rounded-md">Soumettre</button>
                    <button type="submit" name="submit_fiche" value="close" class="bg-red-500 text-white px-6 py-2 rounded-md">Clôturer</button>
                <?php endif; ?>
            </div>
        </form>

        <div class="absolute bottom-6 right-6">
            <a href="gestion_fiche.php" class="bg-gray-500 text-white px-4 py-2 rounded-md">Retour à la gestion des fiches</a>
        </div>
    </div>

    <script>
        function addNewLine() {
            const container = document.getElementById('newLines');
            const lineId = Date.now(); // Unique ID for each line
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
                <input type="text" name="montant[]" class="p-2 border rounded" placeholder="Total" required pattern="^\d+(\,\d{1,2})?$">
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
</body>
</html>