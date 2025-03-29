<?php
require_once __DIR__ . '/../../pdo/bdd.php';

// Récupération des rôles depuis la base de données
$roles = $cnx->query("SELECT id_role, role FROM roles")->fetchAll();
?>

<!-- Formulaire d’ajout -->
<form id="createUserForm" method="post" class="space-y-4 font-body">

  <div class="mb-4">
    <label for="userEmail" class="form-label text-gsb-blue">Email</label>
    <input type="email" name="email" id="userEmail" class="form-input w-full" required>
  </div>

  <div class="mb-4">
    <label for="userFirstname" class="form-label text-gsb-blue">Prénom</label>
    <input type="text" name="firstname" id="userFirstname" class="form-input w-full" required>
  </div>

  <div class="mb-4">
    <label for="userLastname" class="form-label text-gsb-blue">Nom</label>
    <input type="text" name="lastname" id="userLastname" class="form-input w-full" required>
  </div>

  <div class="mb-4">
    <label for="password" class="form-label text-gsb-blue">Mot de passe</label>
    <input type="password" name="password" id="password" class="form-input w-full" required>
  </div>

  <div class="mb-4">
    <label for="userRole" class="form-label text-gsb-blue">Rôle</label>
    <select name="role" id="userRole" class="form-input w-full" required>
      <?php foreach ($roles as $role): ?>
        <option value="<?= $role['id_role'] ?>"><?= htmlspecialchars($role['role']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div>
    <button type="submit" id="submitBtn" class="btn-primary w-full">
      Ajouter
    </button>
  </div>

  <div id="createMessage" class="mt-2 text-sm font-body"></div>

</form>

<!-- Zone pour afficher le message de confirmation -->
<div id="createMessage" class="mt-4"></div>