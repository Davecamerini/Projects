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

// Recupera tutti gli utenti dalla tabella abbonamenti_online
$sql = "SELECT * FROM abbonamenti_scaduti";
$result = $conn->query($sql);

// Chiudi la connessione al database
$conn->close();

if (!isset($_SESSION["session_user"]) || !isset($_SESSION["session_id"])) {
    header("Location: https://www.zoiyoga.it/area-corsi-online/login.php");
    exit();
}

$cookie_name = "utente_id_admin"; // Sostituisci con il nome del tuo cookie
if (!isset($_COOKIE[$cookie_name]) || $_COOKIE[$cookie_name] != base64_encode("autenticato")) {
    header("Location: https://www.zoiyoga.it/area-corsi-online/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Utenti Abbonamenti Online</title>
    <?php include "../../partial/header.php"; ?>
    <style>
        .hidden{
        display: none!important;
        }
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
</head>
<body>
  <header style="justify-content: center;">
      <div class="action-menu">
      <h1 class="h1-gestionale">Lista Utenti Abbonamenti Scaduti</h1>
      <div class="menu-action-categories">
          <div class="info-icon"><a href="/area-corsi-online/php/gestionale/profile.php" style="color:white;text-decoration:none;"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M64 256V160H224v96H64zm0 64H224v96H64V320zm224 96V320H448v96H288zM448 256H288V160H448v96zM64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64z"/></svg> Tutti gli abbonamenti attivi</a></div>
      </div>
      </div>
  </header>
    <div style="display:flex;flex-wrap: nowrap;align-content: center;justify-content: center;">
      <div class="info-icon"><a href="/area-corsi-online/index.php" style="color:white;text-decoration:none;"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg> Torna in home</a></div>
    </div>
    <div class="container-table">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>ID prodotto</th>
                    <th>Data acquisto</th>
                    <th>Durata Abbonamento</th>
                    <th>Email</th>
                    <th>Abbonamento Valido</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $row["id"] . '</td>';
                        echo '<td>' . $row["first_name"] . '</td>';
                        echo '<td>' . $row["last_name"] . '</td>';
                        echo '<td>' . $row["product_ids"] . '</td>';
                        echo '<td>' . date("d-m-Y", strtotime($row["order_date"])) . '</td>';
                        echo '<td>' . $row["duration"] . '</td>';
                        echo '<td>' . $row["email"] . '</td>';


                        // Calcola se l'utente ha un abbonamento ancora valido
                        $abbonamento_valido = abbonamentoValido($row["duration"], $row["order_date"]);
                        $abbonamento_valido ? $color = "green" : $color = "red";
                        $isAdmin = $row["order_id"] > 10 ? "" : "hidden";
                        echo '<td style="font-weight:700;color:'.$color.'">' . ($abbonamento_valido ? 'Abbonamento Attivo' : 'Abbonamento Scaduto') . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="8">Nessun utente disponibile</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
        <?php include "../../partial/footer.php"; ?>
</body>
</html>

<?php
// Funzione per verificare se l'abbonamento è ancora valido
function abbonamentoValido($duration, $data_acquisto) {
    // Converti la data di acquisto in un oggetto DateTime
    $data_acquisto = new DateTime($data_acquisto);

    // Aggiungi la durata dell'abbonamento in giorni alla data di acquisto
    $data_scadenza = $data_acquisto->modify("+$duration days");

    // Ottieni la data corrente
    $data_corrente = new DateTime();

    // Confronta la data di scadenza con la data corrente
    return $data_scadenza > $data_corrente;
}
?>
