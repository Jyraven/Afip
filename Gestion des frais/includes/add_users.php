<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <!-- Lien vers Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Inscription</h2>
        <form action="index.php?action=create" method="post">
            <div class="form-group">
                <label for="userEmail">Email</label>
                <input type="text" class="form-control" name="email" id="userEmail" required>
            </div>
            <div class="form-group">
                <label for="userFirstname">Prénom</label>
                <input type="text" class="form-control" name="firstname" id="userFirstname" required>
            </div>
            <div class="form-group">
                <label for="userLastname">Nom</label>
                <input type="text" class="form-control" name="lastname" id="userLastname" required>
            </div>
            <div class="form-group">
                <label for="userPassword">Mot de passe</label>
                <input type="password" class="form-control" name="password" id="userPassword" required>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer</button>
        </form>
    </div>

    <!-- Lien vers Bootstrap JS et jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>