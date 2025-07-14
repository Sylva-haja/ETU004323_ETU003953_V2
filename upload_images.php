<?php
require 'db.php';
session_start();

$id_objet = intval($_POST['id_objet']);
$set_principale = isset($_POST['set_principale']);
$upload_dir = "uploads/";

foreach ($_FILES['images']['tmp_name'] as $index => $tmp_name) {
    $filename = basename($_FILES['images']['name'][$index]);
    $target_path = $upload_dir . $filename;

    if (move_uploaded_file($tmp_name, $target_path)) {
        $is_principale = ($set_principale && $index === 0) ? 1 : 0;

        if ($is_principale) {
            mysqli_query($dataBase, "UPDATE images_objet SET is_principale = 0 WHERE id_objet = $id_objet");
        }

        $stmt = $dataBase->prepare("INSERT INTO images_objet (id_objet, nom_image, is_principale) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $id_objet, $filename, $is_principale);
        $stmt->execute();
    }
}

header("Location: fiche_objet.php?id=$id_objet");
exit;
