<?php
session_start();
require_once __DIR__ . '/../../pdo/bdd.php';

$search = $cnx->query("
    SELECT users.id_user, users.user_firstname, users.user_lastname, users.user_email, roles.role
    FROM users
    LEFT JOIN roles ON users.id_role = roles.id_role
    ORDER BY users.user_lastname ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">

<!-- Barre de navigation -->
<div class="bg-blue-600 text-white py-4 px-8 flex justify-between items-center">
    <div>
        <img src="../../public/images/logo.webp" alt="Logo" class="w-32">
    </div>
    <div class="flex-grow flex justify-center space-x-8">
        <a href="../../vues/admin.php" class="text-white hover:text-gray-300">Accueil</a>
        <a href="../fiches/gestion_fiche.php" class="text-white hover:text-gray-300">Gestion des fiches</a>
    </div>
    <div class="flex items-center space-x-4">
        <span class="text-white"><?= htmlspecialchars($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']); ?></span>
        <img src="assets/profil.jpg" alt="Profil" class="w-10 h-10 rounded-full border-2 border-white">
    </div>
</div>

<!-- Bouton Ajouter un utilisateur -->
<div class="p-8">
    <button id="openModalBtn" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 mb-4 inline-block">
        Ajouter un utilisateur
    </button>
</div>

<!-- Table des utilisateurs -->
<div class="p-8">
    <table class="w-full border-collapse border">
        <thead>
            <tr>
                <th class="border p-2">ID</th>
                <th class="border p-2">Nom</th>
                <th class="border p-2">Email</th>
                <th class="border p-2">Rôle</th>
                <th class="border p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($search as $data): ?>
                <tr>
                    <td class="border p-2"><?= $data['id_user'] ?></td>
                    <td class="border p-2"><?= htmlspecialchars($data['user_firstname'] . ' ' . $data['user_lastname']) ?></td>
                    <td class="border p-2"><?= htmlspecialchars($data['user_email']) ?></td>
                    <td class="border p-2"><?= htmlspecialchars($data['role']) ?></td>
                    <td class="border p-2 flex justify-center space-x-2">
                        <button class="edit-btn text-blue-600 font-bold" data-id="<?= $data['id_user']; ?>" title="Modifier">
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

<!-- Fenêtre modale pour modification -->
<div id="editUserModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Modifier un utilisateur</h2>
            <button id="closeEditModalBtn" class="text-gray-500 hover:text-gray-700 text-3xl font-bold">&times;</button>
        </div>
        <div id="editModalContent" class="mt-4">
            <!-- Contenu chargé dynamiquement -->
        </div>
    </div>
</div>

<!-- Fenêtre modale pour création -->
<div id="createUserModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Ajouter un utilisateur</h2>
            <button id="closeCreateModalBtn" class="text-gray-500 hover:text-gray-700 text-3xl font-bold">&times;</button>
        </div>
        <div id="createModalContent" class="mt-4">
            <!-- Contenu chargé dynamiquement -->
        </div>
    </div>
</div>

<!-- Fenêtre modale pour confirmation de suppression -->
<div id="deleteUserModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-red-600">Supprimer un utilisateur</h2>
            <button id="closeDeleteModalBtn" class="text-gray-500 hover:text-gray-700 text-3xl font-bold">&times;</button>
        </div>
        <p class="mt-4">Êtes-vous sûr de vouloir supprimer cet utilisateur ?</p>
        <input type="hidden" id="deleteUserId">
        <div class="flex justify-end space-x-4 mt-4">
            <button id="confirmDeleteBtn" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                Confirmer
            </button>
            <button id="cancelDeleteBtn" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
                Annuler
            </button>
        </div>
        <div id="deleteMessage" class="mt-4"></div>
    </div>
</div>

<script src="../../public/js/modal.js"></script>

</body>
</html>