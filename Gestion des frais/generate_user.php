<?php
// backend/init_users.php
require_once 'config.php';

// Tableau des utilisateurs par défaut
$users = [
    [
        'user_firstname' => 'Jean-Michel',
        'user_lastname' => 'Crapaud',
        'user_email' => 'jm.crapaud@fragilité.com',
        'user_password' => 'river',
        'id_role' => '1'
    ],
    [
        'user_firstname' => 'tst',
        'user_lastname' => 'tst',
        'user_email' => 'tst@gsb.com',
        'user_password' => '1',
        'id_role' => '1'
    ],
    [
        'user_firstname' => 'Caro',
        'user_lastname' => 'Bichon',
        'user_email' => 'compta@gsb.com',
        'user_password' => '3',
        'id_role' => '3'
    ],
    [
        'user_firstname' => 'Michel',
        'user_lastname' => 'Ménan',
        'user_email' => 'visiteur@gsb.com',
        'user_password' => '2',
        'id_role' => '2'
    ]
];

// Fonction pour insérer les utilisateurs
function insertUsers($conn, $users) {
    foreach ($users as $user) {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $conn->prepare("SELECT id_user FROM users WHERE user_email = ?");
        $stmt->bind_param("s", $user['user_email']);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 0) {
            // Hacher le mot de passe
            $hashedPassword = password_hash($user['user_password'], PASSWORD_DEFAULT);
            
            // Insérer l'utilisateur
            $stmt_insert = $conn->prepare("INSERT INTO users (prenom, nom, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssss", $user['user_firstname'], $user['user_lastname'], $user['user_email'], $hashedPassword, $user['id_role']);
            $stmt_insert->execute();
            $stmt_insert->close();
        } else {
            echo "L'utilisateur {$user['user_email']} existe déjà.<br>";
        }
        $stmt->close();
    }
}

// Exécuter l'insertion
insertUsers($conn, $users);

// Fermer la connexion
$conn->close();
?>