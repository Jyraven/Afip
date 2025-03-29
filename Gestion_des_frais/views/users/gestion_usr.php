<?php
session_start();
require_once __DIR__ . '/../../pdo/bdd.php';

$search = $cnx->query("
    SELECT users.id_user, users.user_firstname, users.user_lastname, users.user_email, roles.role
    FROM users
    LEFT JOIN roles ON users.id_role = roles.id_role
    ORDER BY users.id_user ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des Utilisateurs</title>

  <!-- Tailwind -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body class="page-admin bg-gray-100 font-body">

  <!-- Menu -->
  <?php include('../../includes/menu_admin.php'); ?>

    <!-- Tableau -->
    <div class="p-8">
    <h1 class="text-2xl text-gsb-blue font-title mb-9">Liste des utilisateurs</h1>

    <!-- Bouton Ajouter -->
    <div class="mb-4">
        <button id="openModalBtn" class="btn-primary">
        Ajouter un utilisateur
        </button>
    </div>

    <table class="w-full border-collapse shadow-md rounded overflow-hidden bg-white text-sm font-body">
        <thead class="bg-gsb-blue text-white">
        <tr>
            <th class="p-3 text-left">ID</th>
            <th class="p-3 text-left">Nom</th>
            <th class="p-3 text-left">Email</th>
            <th class="p-3 text-left">Rôle</th>
            <th class="p-3 text-center">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($search as $data): ?>
            <tr class="border-b hover:bg-gray-50 transition">
            <td class="p-3"><?= $data['id_user'] ?></td>
            <td class="p-3"><?= htmlspecialchars($data['user_firstname'] . ' ' . $data['user_lastname']) ?></td>
            <td class="p-3"><?= htmlspecialchars($data['user_email']) ?></td>
            <td class="p-3"><?= htmlspecialchars($data['role']) ?></td>
            <td class="p-3 flex justify-center items-center space-x-2">
                <button class="edit-btn text-gsb-blue font-bold" data-id="<?= $data['id_user']; ?>" title="Modifier">
                <i class="fas fa-edit"></i>
                </button>
                <button class="delete-btn text-red-600 font-bold" data-id="<?= $data['id_user']; ?>" title="Supprimer">
                <i class="fas fa-trash"></i>
                </button>
            </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>


  <!-- Modale Modifier -->
  <div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md relative">
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-ui font-semibold text-gsb-blue-dark">Modifier un utilisateur</h2>
        <button id="closeEditModalBtn" class="text-gray-500 hover:text-gray-700 text-3xl font-bold">&times;</button>
      </div>
      <div id="editModalContent" class="mt-4"></div>
    </div>
  </div>

  <!-- Modale Ajouter -->
  <div id="createUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md relative">
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-ui font-semibold text-gsb-blue-dark">Ajouter un utilisateur</h2>
        <button id="closeCreateModalBtn" class="text-gray-500 hover:text-gray-700 text-3xl font-bold">&times;</button>
      </div>
      <div id="createModalContent" class="mt-4"></div>
    </div>
  </div>

  <!-- Modale Supprimer -->
  <div id="deleteUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md relative">
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-ui font-semibold text-red-600">Supprimer un utilisateur</h2>
        <button id="closeDeleteModalBtn" class="text-gray-500 hover:text-gray-700 text-3xl font-bold">&times;</button>
      </div>
      <p class="mt-4">Êtes-vous sûr de vouloir supprimer cet utilisateur ?</p>
      <input type="hidden" id="deleteUserId">
      <div class="flex justify-end space-x-4 mt-4">
        <button id="confirmDeleteBtn" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 font-ui">Confirmer</button>
        <button id="cancelDeleteBtn" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 font-ui">Annuler</button>
      </div>
      <div id="deleteMessage" class="mt-4"></div>
    </div>
  </div>

  <?php include('../../includes/footer.php'); ?>

  <script src="../../public/js/modal.js"></script>
</body>
</html>