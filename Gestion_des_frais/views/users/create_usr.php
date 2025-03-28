<?php
require_once __DIR__ . '/../../pdo/bdd.php';

// Récupération des rôles depuis la base de données
$roles = $cnx->query("SELECT id_role, role FROM roles")->fetchAll();
?>

<!-- Formulaire de création d'utilisateur -->
<form id="createUserForm" method="post" class="space-y-4 font-body">
  <div>
    <label for="firstname" class="form-label text-gsb-blue">Prénom</label>
    <input type="text" name="firstname" id="firstname" class="form-input" required>
  </div>

  <div>
    <label for="lastname" class="form-label text-gsb-blue">Nom</label>
    <input type="text" name="lastname" id="lastname" class="form-input" required>
  </div>

  <div>
    <label for="email" class="form-label text-gsb-blue">Email</label>
    <input type="email" name="email" id="email" class="form-input" required>
  </div>

  <div>
    <label for="password" class="form-label text-gsb-blue">Mot de passe</label>
    <input type="password" name="password" id="password" class="form-input" required>
  </div>

  <div>
    <label for="role" class="form-label text-gsb-blue">Rôle</label>
    <select name="role" id="role" class="form-input" required>
      <option value="">Sélectionner un rôle</option>
      <?php foreach ($roles as $role): ?>
        <option value="<?= $role['id_role'] ?>"><?= htmlspecialchars($role['role']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="pt-2">
    <button type="submit" class="btn-primary w-full">
      Ajouter
    </button>
  </div>
</form>

<!-- Zone pour afficher le message de confirmation -->
<div id="createMessage" class="mt-4"></div>