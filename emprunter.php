<?php
require 'db.php';
session_start();

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['id_membre'])) {
    header("Location: login.php");
    exit;
}

// Vérifie que l'ID de l'objet est bien passé
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Identifiant d'objet invalide.");
}
$id_objet = intval($_GET['id']);

// Vérifie si l'objet est bien disponible (pas emprunté)
$sql = "
    SELECT o.nom_objet
    FROM objet o
    LEFT JOIN emprunt e ON o.id_objet = e.id_objet AND e.date_retour IS NULL
    WHERE o.id_objet = $id_objet AND e.id_emprunt IS NULL
";
$result = mysqli_query($dataBase, $sql);
$objet = mysqli_fetch_assoc($result);

if (!$objet) {
    die("Cet objet n'existe pas ou est déjà emprunté.");
}

// Si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jours = max(1, intval($_POST['duree'])); // min 1 jour
    $date_emprunt = date('Y-m-d');
    $date_retour_prevue = date('Y-m-d', strtotime("+$jours days"));

    // Insertion de l'emprunt (on garde date_retour NULL jusqu’au retour réel)
    $stmt = $dataBase->prepare("INSERT INTO emprunt (id_objet, id_membre, date_emprunt, date_retour) VALUES (?, ?, ?, NULL)");
    $stmt->bind_param("iis", $id_objet, $_SESSION['id_membre'], $date_emprunt);
    $stmt->execute();

    header("Location: liste_objets.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emprunter l'objet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #0d1b2a;
            color: white;
        }
        .container {
            background-color: #1b263b;
            margin-top: 60px;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
        }
        label {
            font-weight: bold;
        }
        .btn-success {
            background-color: #38b000;
            border: none;
        }
        .btn-success:hover {
            background-color: #2f6c00;
        }
    </style>
</head>
<body>

<div class="container">
    <h3>Emprunter : <?= htmlspecialchars($objet['nom_objet']) ?></h3>
    <form method="POST">
        <div class="mb-3">
            <label for="duree" class="form-label">Durée de l'emprunt (en jours)</label>
            <input type="number" class="form-control" id="duree" name="duree" value="7" min="1" required>
        </div>
        <button type="submit" class="btn btn-success">Confirmer l'emprunt</button>
        <a href="fiche_objet.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

</body>
</html>
