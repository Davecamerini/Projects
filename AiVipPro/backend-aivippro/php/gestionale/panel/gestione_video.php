<?php
session_start();
$host = "db28.webme.it";  // Inserisci il tuo host del database
$username = "sitidi_571";  // Inserisci il tuo username del database
$password = "NL9GP5tq";  // Inserisci la tua password del database
$database = "sitidi_571";  // Inserisci il nome del tuo database


$conn = new mysqli($host, $username, $password, $database);

// Verifica la connessione
if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Recupera tutti i video dal database
$sql = "SELECT corsi_online.*, categorie_corsi_online.categoria AS categoria_nome
        FROM corsi_online
        INNER JOIN categorie_corsi_online ON corsi_online.categoria = categorie_corsi_online.id WHERE LOWER(SUBSTRING_INDEX(corsi_online.file_video, '.', -1)) = 'mp4'";
$result = $conn->query($sql);

// Chiudi la connessione al database
$conn->close();


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
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Video</title>
    <style>

        .action-buttons {
            display: flex;
            justify-content: space-between;
        }
        .action-buttons a {
            color: #fff;
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid #e50914;
            border-radius: 4px;
        }
        .action-buttons a:hover {
            background-color: #990011;
            color: #000;
        }
        .info-icon{
          padding:15px;
        }
        /* Stile CSS rimasto invariato */
        .categoria-toggle {
            cursor: pointer;
            color: #e50914;
            font-weight: 700;
            font-size:20px;
        }
        .action-menu{
          background-color: #991B0F;
          padding: 0px 0;
          text-align: center;
          display: flex;
          justify-content: space-between;
          align-items: center;
          flex-direction: column;
        }
    </style>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Ottieni tutti gli elementi con la classe "categoria-toggle"
        var toggleElements = document.getElementsByClassName("categoria-toggle");

        // Aggiungi un listener per ciascun elemento toggle
        Array.from(toggleElements).forEach(function (element) {
            element.addEventListener("click", function () {
                // Trova l'elemento tbody associato all'elemento toggle
                var tbody = this.nextElementSibling;
                tbody = tbody.className;

                // Ottieni tutti gli elementi con la classe del tbody
                var allVideo = document.getElementsByClassName(tbody);

                // Itera attraverso la collezione e toggle della visibilità di ciascun elemento
                Array.from(allVideo).forEach(function (video) {
                    video.style.display = (video.style.display === "none" || video.style.display === "") ? "table-row" : "none";
                });
            });
        });
    });

 </script>
</head>
<body>
    <?php include "../../../partial/header.php"; ?>
    <div class="action-menu">
        <h1 class="h1-gestionale">Gestione Video</h1>

        <div class="menu-action-categories">
            <div class="info-icon"><a href="/area-corsi-online/php/gestionale/lista_categorie.php" style="color:white;text-decoration:none;"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M64 256V160H224v96H64zm0 64H224v96H64V320zm224 96V320H448v96H288zM448 256H288V160H448v96zM64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64z"/></svg> Lista categorie</a></div>
            <div class="info-icon"><a href="/area-corsi-online/php/gestionale/inserimento_video.php" style="color:white;text-decoration:none;"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg> Aggiungi video</a></div>
            <div class="info-icon"><a href="/area-corsi-online/index.php" style="color:white;text-decoration:none;"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg> Torna in home</a></div>
        </div>

    </div>
    <div class="container-table ">
        <table>
            <thead>
                <tr>
                  <th>Titolo</th>
                  <th>Categoria</th>
                  <th>Data di Inserimento</th>
                  <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                    <?php
                    $categoriaCorrente = null;

                    while ($row = $result->fetch_assoc()) {

                        // Verifica se la categoria è cambiata
                        if ($categoriaCorrente !== $row["categoria_nome"]) {
                            // Se la categoria è cambiata, mostra il toggle
                            echo '<tr class="categoria-toggle">';
                            echo '<td colspan="5">' . $row["categoria_nome"] . '</td>';
                            echo '</tr>';
                        }

                        // Mostra il video
                        echo '<tr style="display:none;" class="categoria-' . $row["categoria_nome"] . '">';
                        echo '<td>' . $row["titolo"] . '</td>';
                        echo '<td>' . $row["categoria_nome"] . '</td>';
                        echo '<td>' . $row["data_inserimento"] . '</td>';
                        echo '<td class="action-buttons"><a href="../elimina.php?id=' . $row["id"] . '">Elimina</a></td>';
                        echo '<td class="action-buttons"><a href="../modifica.php?id=' . $row["id"] . '&cat=' . $row["categoria"] .'">Modifica</a></td>';
                        echo '</tr>';

                        // Aggiorna la categoria corrente
                        $categoriaCorrente = $row["categoria_nome"];
                    }
                    ?>
                </tbody>
        </table>
    </div>
      <?php include "../../../partial/footer.php"; ?>
</body>
</html>
