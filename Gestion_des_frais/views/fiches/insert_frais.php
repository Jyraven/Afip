<?php
session_start();
require_once('../../pdo/bdd.php');

if (!isset($_SESSION['user'])) {
    header("Location: index.php?action=login");
    exit();
}

$successMessage = null;

if ($_POST) {
    if (isset(
        $_POST['op_date'], 
        $_POST['type_frais'], 
        $_POST['quantite'], 
        $_POST['montant'], 
        $_POST['date_frais']
    ) && isset($_FILES['justificatif'])) {

        $opDate = $_POST['op_date'];
        $typeFrais = $_POST['type_frais'];
        $quantites = $_POST['quantite'];
        $montants = $_POST['montant'];
        $datesFrais = $_POST['date_frais'];
        $justificatifs = $_FILES['justificatif'];
        $justificatifsExistants = $_POST['justificatif_existant'] ?? [];

        if (
            count($typeFrais) !== count($quantites) ||
            count($typeFrais) !== count($montants) ||
            count($typeFrais) !== count($datesFrais)
        ) {
            die("Erreur : Les données du formulaire sont incohérentes. Vérifiez que chaque type de frais a une quantité, un montant et une date correspondante.");
        }

        try {
            $cnx->beginTransaction();
            $status = ($_POST['submit_fiche'] == 'open') ? 2 : 1;
            $clDate = ($status == 2) ? null : date('Y-m-d');

            $stmtFiche = $cnx->prepare("INSERT INTO fiches (op_date, id_users, status_id, cl_date) VALUES (?, ?, ?, ?)");
            if (!$stmtFiche->execute([$opDate, $_SESSION['user']['id'], $status, $clDate])) {
                throw new Exception("Erreur lors de l'insertion de la fiche de frais.");
            }

            $ficheId = $cnx->lastInsertId();
            $stmtLigneFrais = $cnx->prepare("INSERT INTO lignes_frais (id_fiche, id_tf, quantité, total, sp_date, justif) VALUES (?, ?, ?, ?, ?, ?)");

            foreach ($typeFrais as $index => $id_tf) {
                $quantite = $quantites[$index];
                $montant = floatval(str_replace(',', '.', $montants[$index]));
                $dateFrais = $datesFrais[$index];

                $justifPath = null;
                if (isset($justificatifs['tmp_name'][$index]) && is_uploaded_file($justificatifs['tmp_name'][$index])) {
                    $fileTmp = $justificatifs['tmp_name'][$index];
                    $fileName = uniqid() . "_" . basename($justificatifs['name'][$index]);
                    $fileDestination = "../../justificatif/" . $fileName;

                    if (!is_dir("../../justificatif")) {
                        throw new Exception("Le répertoire de destination pour les justificatifs n'existe pas.");
                    }

                    if (!move_uploaded_file($fileTmp, $fileDestination)) {
                        throw new Exception("Erreur lors du téléchargement du fichier : " . $justificatifs['name'][$index]);
                    } else {
                        $justifPath = "justificatif/" . $fileName;
                    }
                } elseif (!empty($justificatifsExistants[$index])) {
                    $justifPath = preg_replace('#^(?:\.\./)+#', '', $justificatifsExistants[$index]);
                }

                if (!$stmtLigneFrais->execute([$ficheId, $id_tf, $quantite, $montant, $dateFrais, $justifPath])) {
                    throw new Exception("Erreur lors de l'insertion de la ligne de frais pour le type de frais ID: $id_tf.");
                }
            }

            $cnx->commit();
            $successMessage = "Fiche de frais ajoutée avec succès !";

        } catch (Exception $e) {
            $cnx->rollBack();
            die("Erreur : " . $e->getMessage());
        }

    } else {
        die("Erreur : Données du formulaire manquantes ou invalides. Vérifiez que tous les champs sont correctement remplis.");
    }
} else {
    die("Aucune donnée reçue. Vérifiez que le formulaire a bien été soumis.");
}
?>

<?php if ($successMessage): ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Succès</title>
        <style>
            .modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
            .modal-content {
                background: #fff;
                padding: 20px;
                border-radius: 10px;
                text-align: center;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            }
            .modal-content h1 {
                color: #4CAF50;
            }
            .modal-content button {
                background: #4CAF50;
                color: #fff;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
            }
            .modal-content button:hover {
                background: #45a049;
            }
        </style>
    </head>
    <body>
        <div class="modal">
            <div class="modal-content">
                <h1><?php echo $successMessage; ?></h1>
                <button onclick="window.location.href='gestion_fiche.php';">Retour</button>
            </div>
        </div>
    </body>
    </html>
<?php endif; ?>