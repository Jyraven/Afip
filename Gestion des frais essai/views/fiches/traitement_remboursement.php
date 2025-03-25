<?php
session_start();
require_once('../../pdo/bdd.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Comptable') {
    header('Location: ../../login.php');
    exit();
}

if (!isset($_POST['fiche_id']) || !is_numeric($_POST['fiche_id'])) {
    header('Location: gestion_remboursement.php?onglet=attribuees&error=invalid_request');
    exit();
}

$ficheId = (int) $_POST['fiche_id'];
$remboursementLignes = $_POST['remboursement'] ?? [];
$motifsRefus = $_POST['motif'] ?? [];

try {
    $cnx->beginTransaction();

    $remboursementLignes = array_map('intval', $remboursementLignes);
    $motifsRefus = array_combine(array_map('intval', array_keys($motifsRefus)), array_values($motifsRefus));

    $stmtLignes = $cnx->prepare("SELECT id_lf FROM lignes_frais WHERE id_fiche = :fiche_id");
    $stmtLignes->execute([':fiche_id' => $ficheId]);
    $allLignes = array_map('intval', $stmtLignes->fetchAll(PDO::FETCH_COLUMN));

    $nonCochees = array_diff($allLignes, $remboursementLignes);

    foreach ($nonCochees as $id) {
        if (!isset($motifsRefus[$id]) || trim($motifsRefus[$id]) === '') {
            $_SESSION['remboursement_checked'] = $remboursementLignes;
            $_SESSION['motifs_refus_preserve'] = $motifsRefus;
            header("Location: edit_fiche.php?id=$ficheId&source=gestion_remboursement&error=missing_motif");
            exit();
        }
    }

    if (!empty($remboursementLignes)) {
        $placeholders = implode(',', array_fill(0, count($remboursementLignes), '?'));
        $stmt = $cnx->prepare("UPDATE lignes_frais 
                               SET refund_status = 1, motif_refus = NULL 
                               WHERE id_lf IN ($placeholders)");
        $stmt->execute($remboursementLignes);
    }

    foreach ($nonCochees as $id) {
        $stmt = $cnx->prepare("UPDATE lignes_frais 
                               SET refund_status = 0, motif_refus = :motif 
                               WHERE id_lf = :id");
        $stmt->execute([
            ':motif' => $motifsRefus[$id],
            ':id' => $id
        ]);
    }

    $stmtTotalR = $cnx->prepare("SELECT SUM(total) FROM lignes_frais 
                                 WHERE id_fiche = :fiche_id AND refund_status = 1");
    $stmtTotalR->execute([':fiche_id' => $ficheId]);
    $total_rembourse = $stmtTotalR->fetchColumn() ?? 0;

    $stmtTotalF = $cnx->prepare("SELECT SUM(total) FROM lignes_frais WHERE id_fiche = :fiche_id");
    $stmtTotalF->execute([':fiche_id' => $ficheId]);
    $total_frais = $stmtTotalF->fetchColumn() ?? 0;

    $stmtUpdate = $cnx->prepare("UPDATE fiches 
                                 SET total_frais = :total_frais, total_rembourse = :total_rembourse, status_id = 4 
                                 WHERE id_fiches = :fiche_id");
    $stmtUpdate->execute([
        ':total_frais' => $total_frais,
        ':total_rembourse' => $total_rembourse,
        ':fiche_id' => $ficheId
    ]);

    $cnx->commit();
    header("Location: gestion_remboursement.php?onglet=attribuees&success=fiche_remboursee");
    exit();

} catch (PDOException $e) {
    $cnx->rollBack();
    error_log("Erreur de remboursement fiche #$ficheId : " . $e->getMessage());
    header("Location: edit_fiche.php?id=$ficheId&source=gestion_remboursement&error=db_error");
    exit();
}
?>