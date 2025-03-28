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

  <!-- Tailwind -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <!-- Custom GSB CSS -->
  <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body class="page-admin bg-gray-100 font-body">

<?php
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

<!-- Alertes -->
<?php if (isset($_GET['error']) && $_GET['error'] === 'missing_motif'): ?>
  <div class="bg-red-500 text-white px-4 py-2 rounded-md text-center mb-4 max-w-4xl mx-auto mt-6 font-ui">
    Veuillez fournir un motif de refus pour chaque ligne non cochée.
  </div>
<?php endif; ?>

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
  <div class="bg-green-500 text-white px-4 py-2 rounded-md text-center mb-4 max-w-4xl mx-auto mt-6 font-ui" id="successMessage">
    Le remboursement a bien été effectué. Redirection en cours...
  </div>
  <script>
    setTimeout(function() {
      window.location.href = "gestion_remboursement.php?onglet=attribuees";
    }, 3000);
  </script>
<?php endif; ?>

<!-- Carte fiche -->
<div class="w-full max-w-5xl min-h-[80vh] mx-auto p-8 bg-white shadow-md rounded-md mt-8 relative font-body">
  <h1 class="text-2xl font-title text-gsb-blue text-center mb-4">
    Fiche de frais n°<?= htmlspecialchars($fiche['id_fiches']); ?>
  </h1>

  <div class="space-y-2 mb-6 text-sm">
    <p><strong>ID utilisateur :</strong> <?= htmlspecialchars($fiche['user_id']); ?></p>
    <p><strong>Utilisateur :</strong> <?= htmlspecialchars($fiche['user_firstname'] . ' ' . $fiche['user_lastname']); ?></p>
    <p><strong>Statut :</strong> <?= htmlspecialchars($fiche['status']); ?></p>
  </div>

  <!-- Clôturer -->
  <?php if (($is_visiteur || $is_comptable) && $fiche['status_id'] == 0 && !$is_lecture_seule): ?>
    <form action="cloturer_fiche.php" method="POST" class="mb-6">
      <input type="hidden" name="fiche_id" value="<?= $ficheId ?>">
      <button type="submit" class="btn-primary hover:bg-yellow-600 bg-yellow-500">
        Clôturer la fiche
      </button>
      <?php if ($is_comptable): ?>
        <p class="text-xs text-gray-600 italic mt-1">Clôture manuelle activée – Utilisez uniquement si le visiteur n’a pas validé la fiche.</p>
      <?php endif; ?>
    </form>
  <?php endif; ?>

  <!-- Lignes de frais -->
  <h2 class="text-xl font-ui font-semibold text-gsb-blue-dark mb-3">Lignes de Frais</h2>

  <form method="POST" action="traitement_remboursement.php">
    <input type="hidden" name="fiche_id" value="<?= $ficheId ?>">

    <table class="w-full border-collapse table-centered text-sm mt-4">
      <thead class="bg-gsb-blue text-white">
        <tr>
          <?php if ($is_comptable && !$is_lecture_seule): ?><th class="p-3">✔</th><?php endif; ?>
          <th class="p-3">Type</th>
          <th class="p-3">Quantité</th>
          <th class="p-3">Total</th>
          <th class="p-3">Justificatif</th>
          <?php if ($is_comptable || $is_visiteur): ?><th class="p-3">Motif de refus</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($lignesFrais as $ligne): ?>
          <tr class="border-b hover:bg-gray-50 transition">
            <?php if ($is_comptable && !$is_lecture_seule): ?>
              <td class="p-3 text-center">
                <input type="checkbox" name="remboursement[]" value="<?= $ligne['id_lf'] ?>"
                       class="rembourse-checkbox" data-total="<?= $ligne['total'] ?>"
                       <?= in_array($ligne['id_lf'], $checkedIds) ? 'checked' : '' ?>>
              </td>
            <?php endif; ?>
            <td class="p-3"><?= htmlspecialchars($ligne['type']) ?></td>
            <td class="p-3"><?= htmlspecialchars($ligne['quantité']) ?></td>
            <td class="p-3"><?= htmlspecialchars($ligne['total']) ?> €</td>
            <td class="p-3">
              <?php if ($ligne['justif']): ?>
                <a href="../../<?= htmlspecialchars($ligne['justif']) ?>" target="_blank" class="text-gsb-blue underline">Voir</a>
              <?php else: ?>Aucun<?php endif; ?>
            </td>
            <?php if ($is_comptable && !$is_lecture_seule): ?>
              <td class="p-3">
                <input type="text" name="motif[<?= $ligne['id_lf'] ?>]" class="form-input"
                       value="<?= htmlspecialchars($motifsRefusPreserve[$ligne['id_lf']] ?? '') ?>">
              </td>
            <?php elseif ($is_lecture_seule): ?>
              <td class="p-3">
                <?= !empty($ligne['motif_refus']) ? htmlspecialchars($ligne['motif_refus']) : '-' ?>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Totaux + bouton -->
    <?php if ($is_comptable && !$is_lecture_seule): ?>
      <div class="mt-6 space-y-2">
        <p class="text-lg font-semibold">Total Frais : <span id="totalFrais"><?= $total_frais ?></span> €</p>
        <p class="text-lg font-semibold">Total Remboursé : <span id="totalRembourse">0</span> €</p>
        <button type="submit" class="btn-primary bg-green-600 hover:bg-green-700">
          Rembourser
        </button>
      </div>
    <?php elseif (($is_visiteur && $status_traitee) || $is_lecture_seule): ?>
      <div class="mt-6">
        <p class="text-lg font-semibold">Total des frais : <?= number_format($total_frais, 2) ?> €</p>
        <p class="text-lg font-semibold mt-1">Total remboursé : <?= number_format($total_rembourse, 2) ?> €</p>
      </div>
    <?php endif; ?>
  </form>

  <!-- Bouton retour -->
  <div class="absolute bottom-6 right-6">
    <a href="<?= $returnUrl ?>" class="bg-gray-500 text-white px-4 py-2 rounded-md font-ui hover:bg-gray-600 transition">
      Retour
    </a>
  </div>
</div>

<script src="../../public/js/remboursement.js"></script>
<?php include('../../includes/footer.php'); ?>

</body>
</html>