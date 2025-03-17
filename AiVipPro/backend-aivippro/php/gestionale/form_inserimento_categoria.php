<?php
session_start();
if (!isset($_SESSION["session_user"]) || !isset($_SESSION["session_id"])) {
    header("Location: https://www.zoiyoga.it/area-corsi-online/login.php");
    exit();
}

$cookie_name = "utente_id_admin"; // Sostituisci con il nome del tuo cookie
if (!isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] != base64_encode("autenticato")) {
  header("Location: https://www.zoiyoga.it/area-corsi-online/index.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserisci Nuova Categoria</title>
    <style>
        h2 {
            text-align: center;
        }

        label {
            display: block;
            margin-top: 10px;
            color: #333;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

    </style>
</head>
<body>
    <?php include "../../partial/header.php"; ?>
    <h2>Inserisci Nuova Categoria</h2>
    <form class="form-insert" action="inserimento_categoria.php" method="post" enctype="multipart/form-data">
        <label for="categoria">Categoria:</label>
        <input type="text" name="categoria" required>

        <label for="copertina">Copertina (JPG):</label>
        <input type="file" name="copertina" accept="image/jpeg" required>

        <button type="submit">Inserisci Categoria</button>
    </form>
      <?php include "../../partial/footer.php"; ?>
</body>
</html>
