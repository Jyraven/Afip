<?php
// Récupération des utilisateurs avec leurs rôles
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
    <title>Liste des utilisateurs</title>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Liste des utilisateurs</h2>
        <?php foreach($search as $data): ?>
            <div class="user-card">
                <div class="user-card-body">
                    <p class="user-info">
                        <strong><?= htmlspecialchars($data['user_firstname'] . ' ' . $data['user_lastname']); ?></strong><br>
                        <em><?= htmlspecialchars($data['user_email']); ?></em><br>
                        <span>Rôle : <?= htmlspecialchars($data['role']); ?></span>
                    </p>
                    <div class="btn-group">
                        <a href="index.php?action=edit&id=<?= $data['id_users']; ?>" class="btn-modifier">Modifier</a>
                        <a href="index.php?action=delete&id=<?= $data['id_users']; ?>" class="btn-supprimer">Supprimer</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>