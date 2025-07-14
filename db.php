<?php
$hote = 'localhost';
$utilisateur = 'root';
$motDePasse = '';
$base = 'membres';

$dataBase = mysqli_connect($hote, $utilisateur, $motDePasse, $base);

if (!$dataBase) {
    die("Échec de connexion : " . mysqli_connect_error());
}
?>
