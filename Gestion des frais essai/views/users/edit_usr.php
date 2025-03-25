<?php
include ('../../pdo/bdd.php');

if (!isset($_GET['id'])) {
    die("<div class='text-red-600'>Erreur : Aucun utilisateur spécifié.</div>");
}

try {
    // Sélectionner les données de l'utilisateur
    $search = $cnx->prepare("SELECT * FROM users WHERE id_user = ?");
    $search->execute([$_GET['id']]);
    $result = $search->fetch();
} catch (PDOException $e) {
    die("<div class='text-red-600'>Erreur : " . $e->getMessage() . "</div>");
}

// Récupération des rôles
$roles = $cnx->query("SELECT id_role, role FROM roles")->fetchAll();
?>

<!-- Formulaire de modification -->
<form id="editUserForm" method="post">
    <input type="hidden" name="id" value="<?= $result['id_user']; ?>">

    <div>
        <label for="userEmail" class="block font-medium">Email</label>
        <input type="email" name="email" id="userEmail" value="<?= htmlspecialchars($result['user_email']); ?>" class="w-full p-2 border rounded" required>
    </div>

    <div>
        <label for="userFirstname" class="block font-medium">Prénom</label>
        <input type="text" name="firstname" id="userFirstname" value="<?= htmlspecialchars($result['user_firstname']); ?>" class="w-full p-2 border rounded" required>
    </div>

    <div>
        <label for="userLastname" class="block font-medium">Nom</label>
        <input type="text" name="lastname" id="userLastname" value="<?= htmlspecialchars($result['user_lastname']); ?>" class="w-full p-2 border rounded" required>
    </div>

    <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
        <input type="password" name="password" id="password" placeholder="Laisser vide pour ne pas modifier"
            class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200">
    </div>

    <div>
        <label for="userRole" class="block font-medium">Rôle</label>
        <select name="role" id="userRole" class="w-full p-2 border rounded" required>
            <?php foreach ($roles as $role): ?>
                <option value="<?= $role['id_role'] ?>" <?= ($role['id_role'] == $result['id_role']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($role['role']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit"
            id="submitBtn"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mt-4"
            disabled>
        Modifier
    </button>
</form>

<!-- Zone d'affichage des messages -->
<div id="editMessage" class="mt-4"></div>