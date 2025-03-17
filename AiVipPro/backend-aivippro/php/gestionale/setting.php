<?php
session_start();
$host = "db28.webme.it";
$username = "sitidi_571";
$password = "NL9GP5tq";
$database = "sitidi_571";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

$sql = "SELECT corsi_online.*, categorie_corsi_online.categoria AS categoria_nome
        FROM corsi_online
        INNER JOIN categorie_corsi_online ON corsi_online.categoria = categorie_corsi_online.id";
$result = $conn->query($sql);

$conn->close();

if (!isset($_SESSION["session_user"]) || !isset($_SESSION["session_id"])) {
    header("Location: https://www.zoiyoga.it/area-corsi-online/login.php");
    exit();
}

$cookie_name = "utente_id_admin";
if (!isset($_COOKIE[$cookie_name]) || $_COOKIE[$cookie_name] != base64_encode("autenticato")) {
    header("Location: https://www.zoiyoga.it/area-corsi-online/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        .dashboard {
            justify-content: center;
            align-items: center;
            background-color: #fff;
            padding: 90px 20px;
            gap: 20px;
        }

        .dashboard a {
            color: white;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 28px 15px;
            background-color: #991B0F;
            border-radius: 8px;
            width: 150px;
            transition: background-color 0.3s ease;
            width: 100%;
            margin: auto 10px;
        }

        .dashboard a:hover {
            background-color: #510903;
        }

        .dashboard img {
            width: 40px;
            height: 40px;
            margin-bottom: 10px;
        }
        .dashboard a {
          font-size: 16px;
          line-height: 25px;
          font-weight: bold;
        }
        .uno_row, .due_row {
          display: flex;
          justify-content: space-around;
          align-items: center;
          align-content: space-around;
          flex-direction: row;
          clear: both;
          padding: 30px 10px;
        }
        @media only screen and (max-width:990px) {
          .uno_row, .due_row {
            display: flex;
            justify-content: center;
            align-items: center;
            align-content: center;
            flex-direction: column;
            clear: both;
            padding: 0;
            flex-wrap: nowrap;
            max-width: 90%;
            margin: auto;
          }
          .dashboard a {
            font-size: 16px;
            line-height: 25px;
            font-weight: bold;
            margin: 10px;
          }
        }
    </style>
</head>

<body>
    <?php include "../../partial/header.php"; ?>

    <div class="dashboard">
      <div class="uno_row">
        <a href="/area-corsi-online/php/gestionale/panel/gestione_video.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="46" width="30" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M0 128C0 92.7 28.7 64 64 64H320c35.3 0 64 28.7 64 64V384c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V128zM559.1 99.8c10.4 5.6 16.9 16.4 16.9 28.2V384c0 11.8-6.5 22.6-16.9 28.2s-23 5-32.9-1.6l-96-64L416 337.1V320 192 174.9l14.2-9.5 96-64c9.8-6.5 22.4-7.2 32.9-1.6z"/></svg>
            Lista Video
        </a>

        <a href="/area-corsi-online/php/gestionale/panel/gestione_audio.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="46" width="28" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 80C149.9 80 62.4 159.4 49.6 262c9.4-3.8 19.6-6 30.4-6c26.5 0 48 21.5 48 48V432c0 26.5-21.5 48-48 48c-44.2 0-80-35.8-80-80V384 336 288C0 146.6 114.6 32 256 32s256 114.6 256 256v48 48 16c0 44.2-35.8 80-80 80c-26.5 0-48-21.5-48-48V304c0-26.5 21.5-48 48-48c10.8 0 21 2.1 30.4 6C449.6 159.4 362.1 80 256 80z"/></svg>
            Lista Audio
        </a>

        <a href="inserimento_video.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="46" width="26" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg>
            Inserisci Video
        </a>
      </div>
        <div class="due_row">
        <a href="inserimento_audio.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="46" width="26" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg>
            Inserisci Audio
        </a>

        <a href="lista_categorie.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="46" width="26" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M64 256V160H224v96H64zm0 64H224v96H64V320zm224 96V320H448v96H288zM448 256H288V160H448v96zM64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64z"/></svg>
            Gestione Categorie
        </a>

        <a href="/area-corsi-online/index.php">
            <svg xmlns="http://www.w3.org/2000/svg" height="46" width="28" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>
            Torna in Home
        </a>
      </div>
    </div>

    <?php include "../../partial/footer.php"; ?>
</body>

</html>
