<?php
require 'db.php';
session_start();
$id_objet = $_GET['id'];
?>

<h3>Ajouter des images à chaque objet</h3>
<form action="upload_images.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id_objet" value="<?= $id_objet ?>">
    <input type="file" name="images[]" multiple required>
    <label>
        <input type="checkbox" name="set_principale" value="1">
    </label>
    <br>
    <button type="submit">Envoyer</button>
</form>
