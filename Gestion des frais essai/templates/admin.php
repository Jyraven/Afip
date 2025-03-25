<?php
session_start();
require_once('../pdo/bdd.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Administrateur') {
    header('Location: login.php'); 
    exit();
}

// Récupération des fiches créées par mois (12 derniers mois)
$sql = "SELECT DATE_FORMAT(op_date, '%Y-%m') as mois, COUNT(*) as total 
        FROM fiches 
        GROUP BY mois 
        ORDER BY mois DESC 
        LIMIT 12";
$stmt = $cnx->query($sql);
$stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$data = [];
foreach (array_reverse($stats) as $row) {
    $labels[] = date('M Y', strtotime($row['mois'] . '-01'));
    $data[] = $row['total'];
}

// Derniers utilisateurs (par ID décroissant)
$usersSql = "SELECT user_firstname, user_lastname, user_email, id_user 
             FROM users 
             ORDER BY id_user DESC 
             LIMIT 5";
$lastUsers = $cnx->query($usersSql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrateur</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">

<?php include('../includes/menu_admin.php'); ?>

<div class="container mx-auto p-8">
    <h1 class="text-3xl font-semibold text-center text-blue-600 mb-8">
        Bonjour <?= htmlspecialchars($_SESSION['user']['firstname'] . ' ' . $_SESSION['user']['lastname']); ?>, vous êtes <strong>Administrateur</strong>.
    </h1>

    <!-- Stats + utilisateurs côte à côte -->
    <div class="flex flex-col md:flex-row gap-6 mb-10 h-[420px]">
        <!-- Graphique -->
        <div class="bg-white p-6 shadow-md rounded w-full md:w-1/2 h-full flex flex-col">
            <h2 class="text-xl font-semibold mb-4">Fiches créées par mois</h2>
            <div class="flex-1">
                <canvas id="ficheChart" class="max-h-[300px] w-full"></canvas>
            </div>
        </div>

        <!-- Derniers utilisateurs -->
        <div class="bg-white p-6 shadow-md rounded w-full md:w-1/2 h-full flex flex-col">
            <h2 class="text-xl font-semibold mb-4">Derniers utilisateurs créés</h2>
            <ul class="space-y-2 overflow-y-auto">
                <?php foreach ($lastUsers as $user): ?>
                    <li class="border-b pb-2">
                        <?= htmlspecialchars($user['user_firstname'] . ' ' . $user['user_lastname']) ?> —
                        <?= htmlspecialchars($user['user_email']) ?> —
                        <span class="text-sm text-gray-500">ID : <?= $user['id_user'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<!-- 📈 Script Chart.js -->
<script>
const ctx = document.getElementById('ficheChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Fiches créées',
            data: <?= json_encode($data) ?>,
            borderColor: 'rgb(37, 99, 235)',
            backgroundColor: 'rgba(37, 99, 235, 0.2)',
            fill: true,
            tension: 0.3,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                precision: 0
            }
        }
    }
});
</script>

</body>
</html>