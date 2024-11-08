<?php
include ('includedb.php');

$username = $_GET["username"];

// TIRO FUORI L'UTENTE DAL DB
$query = "SELECT * FROM area_ris WHERE IG_user = '$username'";
$result = mysqli_query($conn, $query);

// SE ESISTE LO CANCELLO
if (mysqli_num_rows($result) != 0) {
    $querydel = "DELETE FROM area_ris WHERE IG_user='$username'";
    mysqli_query($conn, $querydel);
    header("Location: https://www.artisnotdead.it/areariservata/admin.php");
} else {
    header("Location: https://www.artisnotdead.it/areariservata/admin.php");
}
?>