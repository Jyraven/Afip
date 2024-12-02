<?php
try {
    // Sélectionner les données de l'utilisateur avec son id
    $search = $cnx->prepare("SELECT * FROM users WHERE id_users = ?");
    $search->execute([$_GET['id']]);
    $result = $search->fetch();
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
// Récupération des rôles pour le select
$roles = $cnx->query("SELECT id_role, role FROM roles")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit users</title>
</head>
<body>
    <h2>Modification de l'utilisateur</h2>
    <form action="index.php?action=update" method="post">
        <input type="hidden" name="id" value="<?= $result['id_users']; ?>">

        <label for="userEmail">Email</label>
        <input type="email" name="email" id="userEmail" value="<?= htmlspecialchars($result['user_email']); ?>" required>

        <label for="userFirstname">Prénom</label>
        <input type="text" name="firstname" id="userFirstname" value="<?= htmlspecialchars($result['user_firstname']); ?>" required>

        <label for="userLastname">Nom</label>
        <input type="text" name="lastname" id="userLastname" value="<?= htmlspecialchars($result['user_lastname']); ?>" required>

        <label for="userRole">Rôle</label>
        <select name="role" id="userRole" required>
            <?php
            foreach ($roles as $role) {
                $selected = ($role['id_role'] == $result['id_role']) ? 'selected' : '';
                echo "<option value=\"{$role['id_role']}\" $selected>{$role['role']}</option>";
            }
            ?>
        </select>
        <button type="submit">Envoyer</button>
    </form>
</body>
</html>