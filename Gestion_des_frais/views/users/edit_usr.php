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
<form id="editUserForm" method="post" class="space-y-4 font-body">
  <input type="hidden" name="id" value="<?= $result['id_user']; ?>">

  <div>
    <label for="userEmail" class="form-label text-gsb-blue">Email</label>
    <input type="email" name="email" id="userEmail" value="<?= htmlspecialchars($result['user_email']); ?>" class="form-input" required>
  </div>

  <div>
    <label for="userFirstname" class="form-label text-gsb-blue">Prénom</label>
    <input type="text" name="firstname" id="userFirstname" value="<?= htmlspecialchars($result['user_firstname']); ?>" class="form-input" required>
  </div>

  <div>
    <label for="userLastname" class="form-label text-gsb-blue">Nom</label>
    <input type="text" name="lastname" id="userLastname" value="<?= htmlspecialchars($result['user_lastname']); ?>" class="form-input" required>
  </div>

  <div>
    <label for="password" class="form-label text-gsb-blue">Mot de passe</label>
    <input type="password" name="password" id="password" placeholder="Laisser vide pour ne pas modifier" class="form-input">
  </div>

  <div>
    <label for="userRole" class="form-label text-gsb-blue">Rôle</label>
    <select name="role" id="userRole" class="form-input" required>
      <?php foreach ($roles as $role): ?>
        <option value="<?= $role['id_role'] ?>" <?= ($role['id_role'] == $result['id_role']) ? 'selected' : ''; ?>>
          <?= htmlspecialchars($role['role']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="pt-2">
    <button type="submit" id="submitBtn" class="btn-primary w-full" disabled>
      Modifier
    </button>
  </div>
</form>

<!-- Zone d'affichage des messages -->
<div id="editMessage" class="mt-4 font-body text-sm"></div>