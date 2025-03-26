<?php
require_once __DIR__ . '/../../pdo/bdd.php';

// Récupération des rôles depuis la base de données
$roles = $cnx->query("SELECT id_role, role FROM roles")->fetchAll();
?>

<!-- Formulaire de création d'utilisateur -->
<form id="createUserForm" method="post">
    <div>
        <label for="firstname" class="block font-medium">Prénom</label>
        <input type="text" name="firstname" id="firstname" class="w-full p-2 border rounded" required>
    </div>
    <div>
        <label for="lastname" class="block font-medium">Nom</label>
        <input type="text" name="lastname" id="lastname" class="w-full p-2 border rounded" required>
    </div>
    <div>
        <label for="email" class="block font-medium">Email</label>
        <input type="email" name="email" id="email" class="w-full p-2 border rounded" required>
    </div>
    <div>
        <label for="password" class="block font-medium">Mot de passe</label>
        <input type="password" name="password" id="password" class="w-full p-2 border rounded" required>
    </div>
    <div>
        <label for="role" class="block font-medium">Rôle</label>
        <select name="role" id="role" class="w-full p-2 border rounded" required>
            <option value="">Sélectionner un rôle</option>
            <?php foreach ($roles as $role): ?>
                <option value="<?= $role['id_role'] ?>"><?= htmlspecialchars($role['role']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mt-4">
        Ajouter
    </button>
</form>

<!-- Zone pour afficher le message de confirmation -->
<div id="createMessage" class="mt-4"></div>