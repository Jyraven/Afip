<?php
session_start();
require_once('../includes/bdd.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_fiche'])) {
    $idFiche = $_POST['id_fiche'];
    $typeFrais = $_POST['type_frais'];
    $quantite = $_POST['quantite'];
    $montant = $_POST['montant'];
    $dateFrais = $_POST['date_frais'];
    $justificatif = ''; // Gérer le fichier plus tard

    // Insérer la ligne de frais dans la base de données
    $sql = "INSERT INTO lignes_frais (id_fiche, id_tf, quantité, total, date_frais, justif)
            VALUES (:id_fiche, :id_tf, :quantité, :total, :date_frais, :justif)";
    $stmt = $cnx->prepare($sql);
    $stmt->execute([
        ':id_fiche' => $idFiche,
        ':id_tf' => $typeFrais,
        ':quantité' => $quantite,
        ':total' => $montant,
        ':date_frais' => $dateFrais,
        ':justif' => $justificatif
    ]);

    echo json_encode(['success' => true, 'message' => 'Ligne ajoutée avec succès.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
}