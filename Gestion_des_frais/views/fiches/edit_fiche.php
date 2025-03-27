<?php
session_start();
require_once('../../pdo/bdd.php');

if (!isset($_SESSION['user'])) {
    header('Location: ../../login.php');
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$user_role = $user['role'];
$is_comptable = ($user_role === 'Comptable');
$is_visiteur = ($user_role === 'Visiteur');

// Déterminer l'URL de retour selon la source d'appel
if (isset($_GET['source'])) {
    switch ($_GET['source']) {
        case 'visiteur':
            $returnUrl = '../../templates/visiteur.php';
            break;
        case 'gestion_remboursement':
            $onglet = $_GET['onglet'] ?? 'attribuees';
            $returnUrl = 'gestion_remboursement.php?onglet=' . $onglet;
            break;
        case 'comptable':
            $returnUrl = '../../templates/comptable.php';
            break;
        case 'historique':
            $returnUrl = 'gestion_remboursement.php?onglet=historique';
            break;
        case 'gestion_fiche':
            // On récupère tous les paramètres sauf id et source
            $query = $_GET;
            unset($query['id'], $query['source']);
            $returnUrl = 'gestion_fiche.php' . (!empty($query) ? '?' . http_build_query($query) : '');
            break;
        case 'visiteur':
            $returnUrl = '../../templates/visiteur.php';
            break;
        default:
            $returnUrl = 'gestion_fiche.php';
    }
} else {
    $returnUrl = 'gestion_fiche.php';
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de fiche invalide.";
    exit();
}

$ficheId = $_GET['id'];

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

$isAttribueeAuComptable = $is_comptable && ($fiche['id_comptable'] == $user_id);

$ligneFraisSql = "SELECT lignes_frais.*, type_frais.type
                  FROM lignes_frais
                  LEFT JOIN type_frais ON lignes_frais.id_tf = type_frais.id_tf
                  WHERE lignes_frais.id_fiche = :id_fiche";
$ligneFraisStmt = $cnx->prepare($ligneFraisSql);
$ligneFraisStmt->bindValue(':id_fiche', $ficheId, PDO::PARAM_INT);
$ligneFraisStmt->execute();
$lignesFrais = $ligneFraisStmt->fetchAll(PDO::FETCH_ASSOC);

$total_frais = $fiche['total_frais'] ?? array_sum(array_column($lignesFrais, 'total'));
$total_rembourse = $fiche['total_rembourse'] ?? 0;
$status_traitee = ($fiche['status_id'] == 4);
$is_lecture_seule = ((isset($_GET['source']) && $_GET['source'] === 'historique') || $status_traitee || ($is_comptable && !$isAttribueeAuComptable));

$checkedIds = $_SESSION['remboursement_checked'] ?? [];
$motifsRefusPreserve = $_SESSION['motifs_refus_preserve'] ?? [];
unset($_SESSION['remboursement_checked'], $_SESSION['motifs_refus_preserve']);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de frais</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
    <?php if (isset($_GET['error']) && $_GET['error'] === 'missing_motif'): ?>
        <div class="bg-red-500 text-white px-4 py-2 rounded-md text-center mb-4">
            Veuillez fournir un motif de refus pour chaque ligne non cochée.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="bg-green-500 text-white px-4 py-2 rounded-md text-center mb-4" id="successMessage">
            Le remboursement a bien été effectué. Redirection en cours...
        </div>

        <script>
            setTimeout(function() {
                window.location.href = "gestion_remboursement.php?onglet=attribuees";
            }, 3000); // Redirige après 3 secondes
        </script>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto p-6 bg-white shadow-md rounded-md relative">
        <h1 class="text-2xl font-bold mb-4 text-center">Fiche de frais n°<?= htmlspecialchars($fiche['id_fiches']); ?></h1>
        <p><strong>ID utilisateur :</strong> <?= htmlspecialchars($fiche['user_id']); ?></p>
        <p><strong>Utilisateur :</strong> <?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']); ?></p>
        <p><strong>Status :</strong> <?= htmlspecialchars($fiche['status']); ?></p>

        <?php if (($is_visiteur || $is_comptable) && $fiche['status_id'] == 0 && !$is_lecture_seule): ?>
            <form action="cloturer_fiche.php" method="POST" class="mt-4">
                <input type="hidden" name="fiche_id" value="<?= $ficheId ?>">
                <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded-md">Clôturer la fiche</button>
                <?php if ($is_comptable): ?>
                    <p class="text-sm text-gray-600 italic mt-1">Clôture manuelle activée – Utilisez uniquement si le visiteur n’a pas validé la fiche.</p>
                <?php endif; ?>
            </form>
        <?php endif; ?>

        <h2 class="text-xl font-bold mt-6">Lignes de Frais</h2>
        <form method="POST" action="traitement_remboursement.php">
            <input type="hidden" name="fiche_id" value="<?= $ficheId ?>">
            <table class="border-collapse border border-gray-300 w-full mt-4">
                <thead>
                    <tr>
                        <?php if ($is_comptable && !$is_lecture_seule): ?><th class="border p-2 text-center">✔</th><?php endif; ?>
                        <th class="border p-2 text-center">Type</th>
                        <th class="border p-2 text-center">Quantité</th>
                        <th class="border p-2 text-center">Total</th>
                        <th class="border p-2 text-center">Justificatif</th>
                        <?php if ($is_comptable || $is_visiteur): ?><th class="border p-2 text-center">Motif de refus</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lignesFrais as $ligne): ?>
                        <tr>
                            <?php if ($is_comptable && !$is_lecture_seule): ?>
                                <td class="border p-2 text-center">
                                    <input type="checkbox" name="remboursement[]" value="<?= $ligne['id_lf'] ?>"
                                           class="rembourse-checkbox" data-total="<?= $ligne['total'] ?>"
                                           <?= in_array($ligne['id_lf'], $checkedIds) ? 'checked' : '' ?>>
                                </td>
                            <?php endif; ?>
                            <td class="border p-2 text-center"><?= htmlspecialchars($ligne['type']) ?></td>
                            <td class="border p-2 text-center"><?= htmlspecialchars($ligne['quantité']) ?></td>
                            <td class="border p-2 text-center"><?= htmlspecialchars($ligne['total']) ?> €</td>
                            <td class="border p-2 text-center">
                                <?php if ($ligne['justif']): ?>
                                    <a href="../../<?= htmlspecialchars($ligne['justif']) ?>" target="_blank" class="text-blue-500 underline">Voir</a>
                                <?php else: ?>Aucun<?php endif; ?>
                            </td>
                            <?php if ($is_comptable && !$is_lecture_seule): ?>
                                <td class="border p-2 text-center">
                                    <input type="text" name="motif[<?= $ligne['id_lf'] ?>]" class="motif-input p-2 border rounded w-full"
                                           value="<?= htmlspecialchars($motifsRefusPreserve[$ligne['id_lf']] ?? '') ?>">
                                </td>
                            <?php elseif ($is_lecture_seule): ?>
                                <td class="border p-2 text-center">
                                    <?= !empty($ligne['motif_refus']) ? htmlspecialchars($ligne['motif_refus']) : '-' ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($is_comptable && !$is_lecture_seule): ?>
                <p class="mt-4 text-lg font-bold">Total Frais : <span id="totalFrais"><?= $total_frais ?></span> €</p>
                <p class="mt-2 text-lg font-bold">Total Remboursé : <span id="totalRembourse">0</span> €</p>
                <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-md mt-4">Rembourser</button>
            <?php elseif (($is_visiteur && $status_traitee) || $is_lecture_seule): ?>
                <div class="mt-6 text-left">
                    <p class="text-lg font-bold">Total des frais : <?= number_format($total_frais, 2) ?> €</p>
                    <p class="text-lg font-bold">Total remboursé : <?= number_format($total_rembourse, 2) ?> €</p>
                </div>
            <?php endif; ?>
        </form>

        <div class="absolute bottom-6 right-6">
             <a href="<?= $returnUrl ?>" class="bg-gray-500 text-white px-4 py-2 rounded-md">Retour</a>
        </div>
    </div>

    <script src="../../public/js/remboursement.js"></script>
</body>
</html>