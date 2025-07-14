<?php
require 'db.php';
session_start();

$id_objet = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Détails de l'objet
$sql_objet = "
    SELECT o.id_objet, o.nom_objet, c.nom_categorie, m.nom AS nom_membre,
           (SELECT id_emprunt FROM emprunt WHERE id_objet = o.id_objet AND date_retour IS NULL LIMIT 1) AS id_emprunt,
           (SELECT date_retour FROM emprunt WHERE id_objet = o.id_objet AND date_retour IS NULL LIMIT 1) AS date_retour,
           (SELECT mem.nom FROM emprunt e JOIN membre mem ON e.id_membre = mem.id_membre WHERE e.id_objet = o.id_objet AND e.date_retour IS NULL LIMIT 1) AS nom_emprunteur
    FROM objet o
    JOIN categorie_objet c ON o.id_categorie = c.id_categorie
    JOIN membre m ON o.id_membre = m.id_membre
    WHERE o.id_objet = $id_objet
";
$result_objet = mysqli_query($dataBase, $sql_objet);
$objet = mysqli_fetch_assoc($result_objet);

if (!$objet) die("Objet introuvable.");

// Images
$sql_images = "SELECT nom_image FROM images_objet WHERE id_objet = $id_objet ORDER BY is_principale DESC, id_image ASC";
$result_images = mysqli_query($dataBase, $sql_images);
$images = [];
while ($img = mysqli_fetch_assoc($result_images)) {
    $images[] = $img['nom_image'];
}
$image_principale = $images[0] ?? 'default.jpg';

// Historique des emprunts
$sql_historique = "
    SELECT m.nom, e.date_emprunt, e.date_retour
    FROM emprunt e
    JOIN membre m ON e.id_membre = m.id_membre
    WHERE e.id_objet = $id_objet
    ORDER BY e.date_emprunt DESC
";
$result_historique = mysqli_query($dataBase, $sql_historique);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($objet['nom_objet']) ?> - Détails</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #0d1b2a;
      color: white;
    }
    .container {
      margin-top: 50px;
      background-color: #1b263b;
      padding: 30px;
      border-radius: 15px;
      max-width: 850px;
    }
    .image-main {
      width: 100%;
      height: 350px;
      object-fit: cover;
      border-radius: 15px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.4);
    }
    .thumbs img {
      height: 80px;
      width: 120px;
      object-fit: cover;
      margin-right: 10px;
      border-radius: 10px;
      cursor: pointer;
      transition: border 0.3s;
    }
    .thumbs img.active,
    .thumbs img:hover {
      border: 2px solid #38b000;
    }
    .badge-available {
      background-color: #38b000;
    }
    .badge-borrowed {
      background-color: #d00000;
    }
    .history-list li {
      background-color: #162447;
      margin-bottom: 8px;
      border-radius: 8px;
      padding: 10px;
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
  <a href="liste_objets.php" class="btn btn-outline-light mb-4">Retour à la liste</a>

  <h2><?= htmlspecialchars($objet['nom_objet']) ?></h2>
  <p><strong>Catégorie :</strong> <?= htmlspecialchars($objet['nom_categorie']) ?></p>
  <p><strong>Propriétaire :</strong> <?= htmlspecialchars($objet['nom_membre']) ?></p>
  <p>
    <strong>État :</strong>
    <?php if ($objet['id_emprunt']) : ?>
      <span class="badge badge-borrowed">Emprunté jusqu’au <?= htmlspecialchars($objet['date_retour']) ?></span>
      <span class="ms-2 badge bg-warning text-dark">Par <?= htmlspecialchars($objet['nom_emprunteur']) ?></span>
    <?php else : ?>
      <span class="badge badge-available"> Disponible</span>
    <?php endif; ?>
  </p>

  
  <?php if (!$objet['id_emprunt']) : ?>
    <a href="emprunter.php?id=<?= $objet['id_objet'] ?>" class="btn btn-success mt-2 w-100">Emprunter</a>
  <?php endif; ?>

  
  <img src="uploads/<?= htmlspecialchars($image_principale) ?>" id="mainImage" class="image-main mb-3" alt="Image principale">

  
  <?php if (count($images) > 1): ?>
    <div class="d-flex thumbs mb-4">
      <?php foreach ($images as $img): ?>
        <img src="uploads/<?= htmlspecialchars($img) ?>" alt="miniature" onclick="showImage(this)">
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  
  <h4>Historique des emprunts</h4>
  <?php if (mysqli_num_rows($result_historique) > 0): ?>
    <ul class="list-unstyled history-list">
      <?php while ($hist = mysqli_fetch_assoc($result_historique)) : ?>
        <li>
          <strong><?= htmlspecialchars($hist['nom']) ?></strong> — 
          du <?= htmlspecialchars($hist['date_emprunt']) ?> 
          au <?= $hist['date_retour'] ? htmlspecialchars($hist['date_retour']) : '<em>en cours</em>' ?>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php else: ?>
    <p class="fst-italic text-muted">Aucun emprunt enregistré pour cet objet.</p>
  <?php endif; ?>
</div>

<script>
  function showImage(img) {
    document.getElementById('mainImage').src = img.src;
    const allThumbs = document.querySelectorAll('.thumbs img');
    allThumbs.forEach(i => i.classList.remove('active'));
    img.classList.add('active');
  }
  window.onload = () => {
    const firstThumb = document.querySelector('.thumbs img');
    if (firstThumb) firstThumb.classList.add('active');
  };
</script>

</body>
</html>
