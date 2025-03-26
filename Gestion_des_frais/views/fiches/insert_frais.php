<?php
session_start();
require_once('../../pdo/bdd.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: index.php?action=login");
    exit();
}

// Initialisation des variables pour gérer l'affichage du message de succès
$successMessage = null;

if ($_POST) {
    // Vérifier les données du formulaire
    if (isset($_POST['op_date'], $_POST['type_frais'], $_POST['quantite'], $_POST['montant'], $_POST['date_frais']) && isset($_FILES['justificatif'])) {

        // Récupérer les valeurs
        $opDate = $_POST['op_date']; // Date d'ouverture
        $typeFrais = $_POST['type_frais']; // Types de frais
        $quantites = $_POST['quantite']; // Quantités
        $montants = $_POST['montant']; // Montants
        $datesFrais = $_POST['date_frais']; // Dates des frais
        $justificatifs = $_FILES['justificatif']; // Justificatifs

        // Vérifier la cohérence des données
        if (count($typeFrais) !== count($quantites) || count($typeFrais) !== count($montants) || count($typeFrais) !== count($datesFrais)) {
            die("Erreur : Les données du formulaire sont incohérentes. Vérifiez que chaque type de frais a une quantité, un montant et une date correspondante.");
        }

        try {
            // Démarrer la transaction
            $cnx->beginTransaction();

            // Déterminer le statut de la fiche : 'open' ou 'close'
            $status = ($_POST['submit_fiche'] == 'open') ? 2 : 1;  // 2 = "Ouverte", 1 = "Clôturée"

            // Insérer la fiche (date d'ouverture, statut, user_id, cl_date NULL si ouverte)
            $stmtFiche = $cnx->prepare("
                INSERT INTO fiches (op_date, id_users, status_id, cl_date) 
                VALUES (?, ?, ?, ?)
            ");
            
            // Si la fiche est ouverte, la date de clôture est NULL, sinon elle est remplie avec la date du jour
            $clDate = ($status == 2) ? null : date('d-m-Y');

            if (!$stmtFiche->execute([$opDate, $_SESSION['user']['id'], $status, $clDate])) {
                throw new Exception("Erreur lors de l'insertion de la fiche de frais.");
            }

            $ficheId = $cnx->lastInsertId(); // Récupérer l'ID de la fiche insérée

            // Préparer la requête pour insérer les lignes de frais
            $stmtLigneFrais = $cnx->prepare("
                INSERT INTO lignes_frais (id_fiche, id_tf, quantité, total, sp_date, justif)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            // Insérer chaque ligne de frais
            foreach ($typeFrais as $index => $id_tf) {
                $quantite = $quantites[$index];
                $montant = $montants[$index];
                $dateFrais = $datesFrais[$index];

                $montant = floatval(str_replace(',', '.', $montant));

                $total = $montant;

                // Gérer le justificatif
                $justifPath = null;
                if (isset($justificatifs['tmp_name'][$index]) && is_uploaded_file($justificatifs['tmp_name'][$index])) {
                    $fileTmp = $justificatifs['tmp_name'][$index];
                    $fileName = uniqid() . "_" . basename($justificatifs['name'][$index]);
                    $fileDestination = "../../justificatif/" . $fileName;

                    // Vérifier si le dossier existe et est accessible
                    if (!is_dir("../../justificatif")) {
                        throw new Exception("Le répertoire de destination pour les justificatifs n'existe pas.");
                    }

                    // Déplacer le fichier
                    if (!move_uploaded_file($fileTmp, $fileDestination)) {
                        throw new Exception("Erreur lors du téléchargement du fichier : " . $justificatifs['name'][$index]);
                    } else {
                        $justifPath = $fileDestination; // Stocker le chemin du fichier
                    }
                } else {
                    // Si aucun justificatif n'est fourni, on ne l'enregistre pas
                    $justifPath = null;
                }

                // Insérer la ligne de frais
                if (!$stmtLigneFrais->execute([$ficheId, $id_tf, $quantite, $total, $dateFrais, $justifPath])) {
                    throw new Exception("Erreur lors de l'insertion de la ligne de frais pour le type de frais ID: $id_tf.");
                }
            }

            // Commit de la transaction
            $cnx->commit();

            // Définir le message de succès
            $successMessage = "Fiche de frais ajoutée avec succès !";

        } catch (Exception $e) {
            // Rollback en cas d'erreur
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

<!-- Affichage du message de succès dans une fenêtre modale -->
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