<?php
if ($_POST) {    if (isset($_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['id_users'])) {
        $update = $cnx->prepare("
            UPDATE users 
            SET user_firstname = ?, user_lastname = ?, user_email = ?
            WHERE id_users = ?
        ");
        $success = $update->execute([
            $_POST['firstname'], 
            $_POST['lastname'], 
            $_POST['email'], 
            $_POST['id_users']
        ]);
        if ($success) {
            echo "Utilisateur mis à jour avec succès.";
        } else {
            echo "Erreur lors de la mise à jour de l'utilisateur.";
        }
    } else {
        die("Données manquantes pour mettre à jour l'utilisateur.");
    }
} else {
    die("Aucune donnée reçue.");
}
?>
<a href="index.php">Retour au formulaire</a>