<?php
session_start();
require_once('../pdo/bdd.php');

// Vérifier si l'utilisateur est connecté et a le rôle "Visiteur"
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Visiteur') {
    header('Location: login.php'); 
    exit();
}

// Récupérer les informations de l'utilisateur connecté
$user = $_SESSION['user'];
$user_id = $user['id'];

// Récupérer les fiches créées par l'utilisateur
$sql = "SELECT f.*, s.name_status AS status
        FROM fiches f
        LEFT JOIN status_fiche s ON f.status_id = s.status_id
        WHERE f.id_users = :id_user
        AND f.status_id IN (2, 3)
        ORDER BY f.op_date DESC";

$stmt = $cnx->prepare($sql);
$stmt->bindValue(':id_user', $user_id, PDO::PARAM_INT);
$stmt->execute();
$fiches = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Visiteur - Gestion des Fiches de Frais</title>

  <!-- Tailwind -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <!-- GSB CSS -->
  <link rel="stylesheet" href="../public/css/style.css">
</head>
<body class="page-admin bg-gray-100 font-body">

<!-- Menu visiteur -->
<?php include('../includes/menu_visiteur.php'); ?>

<!-- Contenu principal -->
<div class="container mx-auto px-6 py-8">
  <h1 class="text-3xl text-center font-title text-gsb-blue mb-8">
    Bonjour <?= htmlspecialchars($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']); ?>, vous êtes <strong>Visiteur</strong>.
  </h1>

    <!-- Bouton de création -->
    <div class="flex justify-center mb-6">
        <a href="../views/fiches/fiche_frais.php?source=visiteur" class="btn-primary inline-flex items-center justify-center">
            Créer une nouvelle fiche de frais
        </a>
    </div>


  <!-- Tableau des fiches -->
  <div class="bg-white rounded shadow-md overflow-hidden">
    <table class="w-full border-collapse text-sm font-body table-centered">
      <thead class="bg-gsb-blue text-white">
        <tr>
          <th class="p-3">ID</th>
          <th class="p-3">Date d'ouverture</th>
          <th class="p-3">Date de clôture</th>
          <th class="p-3">Statut</th>
          <th class="p-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($fiches)): ?>
          <?php foreach ($fiches as $fiche): ?>
            <tr class="border-b hover:bg-gray-50 transition">
              <td class="p-3"><?= $fiche['id_fiches'] ?></td>
              <td class="p-3"><?= date('d/m/Y', strtotime($fiche['op_date'])) ?></td>
              <td class="p-3">
                <?= $fiche['cl_date'] ? date('d/m/Y', strtotime($fiche['cl_date'])) : 'Non clôturé' ?>
              </td>
              <td class="p-3"><?= htmlspecialchars($fiche['status']) ?></td>
              <td class="p-3">
                <div class="flex justify-center space-x-4">
                  <?php
                    $ficheIsCloturee = ($fiche['status_id'] != 2);
                    $ficheUrl = $ficheIsCloturee 
                      ? "../views/fiches/edit_fiche.php?id=" . $fiche['id_fiches'] . "&source=visiteur"
                      : "../views/fiches/fiche_frais.php?id_fiche=" . $fiche['id_fiches'] . "&source=visiteur";
                  ?>
                  <a href="<?= $ficheUrl ?>" class="text-gsb-blue hover:text-gsb-light" title="Voir">
                    <i class="fas fa-eye"></i>
                  </a>
                  <a href="../views/fiches/delete_fiche.php?id=<?= $fiche['id_fiches'] ?>" class="text-red-600 hover:text-red-800 font-bold" title="Supprimer">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="p-4 text-center text-gray-500 italic">Aucune fiche de frais trouvée.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include('../includes/footer.php'); ?>

</body>
</html>