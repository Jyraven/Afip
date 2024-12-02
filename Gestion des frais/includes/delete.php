<?php
if ($_GET) {
    $ins = $cnx->prepare("DELETE FROM users WHERE id_users = ?");
    $ins->execute([$_GET['id']]);
    echo '<div class="alert alert-success" role="alert">Utilisateur supprimé avec succès !</div>';
} else {
    echo '<div class="alert alert-danger" role="alert">Erreur : Aucun utilisateur spécifié à supprimer.</div>';
}
?>
<a href="index.php" class="btn btn-secondary mt-2">Retour au formulaire</a>