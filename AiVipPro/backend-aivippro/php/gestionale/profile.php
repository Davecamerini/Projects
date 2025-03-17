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
$sql = "SELECT * FROM abbonamenti_online";
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


//restituisce il numero dei giorni mancanti alla fine dell'abbonamento già presente in abbonamenti attivi
function get_abbonamento_attivo($email) {
  $host = "db28.webme.it";  // Inserisci il tuo host del database
  $username = "sitidi_571";  // Inserisci il tuo username del database
  $password = "NL9GP5tq";  // Inserisci la tua password del database
  $database = "sitidi_571";  // Inserisci il nome del tuo database

  $conn = new mysqli($host, $username, $password, $database);

  // Verifica la connessione
  if ($conn->connect_error) {
      die("Connessione al database fallita: " . $conn->connect_error);
  }
    // Prepara la query con un'istruzione preparata
    $stmt = $conn->prepare("SELECT *, DATEDIFF(DATE_ADD(order_date, INTERVAL duration DAY), NOW()) AS giorni_mancanti FROM abbonamenti_online WHERE email = ?");
    $stmt->bind_param("s", $email);

    // Esegui la query
    $stmt->execute();
    $result = $stmt->get_result();

    // Inizializza un array per memorizzare i giorni mancanti per ogni riga
    $giorniMancantiArray = array();

    // Estrai i risultati
    while ($row = $result->fetch_assoc()) {
        $giorniMancantiArray[] = $row["giorni_mancanti"];
    }

    // Chiudi la connessione al database
    $conn->close();

    // Calcola la somma di tutti gli elementi dell'array
    $sommaGiorniMancanti = array_sum($giorniMancantiArray);

    return $sommaGiorniMancanti;
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
        .spiegazione-pag {
            background: #fff7ed;
            max-width: 90%;
            border-radius: 14px;
            box-shadow: 0 0 6px 0px #e0e0e0;
            text-align: center;
            padding: 15px;
            margin: 23px auto;
            color: #434343;
            font-weight: 300;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css" integrity="sha512-q3eWabyZPc1XTCmF+8/LuE1ozpg5xxn7iO89yfSOd5/oKvyqLngoNGsx8jq92Y8eXJ/IRxQbEC+FGSYxtk2oiw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  </script>
  <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>

  <script>
$(document).ready(function() {
    $('#userTable').DataTable({
        "order": [[0, 'desc']],
    });
});
</script>

</head>
<body>
    <header style="justify-content: center;">
        <div class="action-menu">
        <h1 class="h1-gestionale">Lista Utenti Abbonamenti Online</h1>
        <div class="menu-action-categories">
            <div class="info-icon"><a href="/area-corsi-online/php/gestionale/lista_utenti_storico.php" style="color:white;text-decoration:none;"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M64 256V160H224v96H64zm0 64H224v96H64V320zm224 96V320H448v96H288zM448 256H288V160H448v96zM64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64z"/></svg> Tutti gli abbonamenti scaduti</a></div>
        </div>
        </div>
    </header>
    <div style="display:flex;flex-wrap: nowrap;align-content: center;justify-content: center;">
      <div class="info-icon"><a href="/area-corsi-online/index.php" style="color:white;text-decoration:none;"><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><style>svg{fill:#ffffff}</style><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg> Torna in home</a></div>
    </div>
    <div class="container-table">
        <p class="spiegazione-pag">
            Di seguito, una tabella riepilogativa dello <b>stato abbonamenti</b>.
            <br>La tabella è ordinata per data di acquisto (dall'ultima prenotazione ricevuta alla più datata). <br>
            Puoi fare una ricerca più rapida dei dati, inserendolo direttamente il termine di ricerca nella sezione “Search” <br>
        </p>

        <table id="userTable">
            <thead>
                <tr>
                    <th title="Numero ordine riportato da Woocommerce. ">ID Ordine</th>
                    <th title="Nome cliente">Nome</th>
                    <th title="Cognome cliente">Cognome</th>
                    <th title="Data in cui il cliente ha effettuato un rinnovo.">Data Ordine</th>
                    <th title="Giorni rimanenti alla scadenza dell'abbonamento.">Scadenza (gg)</th>
                    <th title="Mail utiizzata per acquisto e accesso al portale .">Email</th>
                    <th title="Validità abbonamento (Attivo/Scaduto).">Abbonamento Valido</th>
                    <th title="Effettua la modifica o la visualizzazione dati cliente.">Azione</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $email = $row["email"];
                        $giorniRimanenti = get_abbonamento_attivo($email, $conn);
                        echo '<tr>';
                        echo '<td>' . $row["order_id"] . '</td>';
                        echo '<td>' . $row["first_name"] . '</td>'; 
                        echo '<td>' . $row["last_name"] . '</td>';
                        echo '<td>' . date("d-m-Y", strtotime($row["order_date"])) . '</td>';
                        echo '<td>' .  $giorniRimanenti . '</td>';
                        echo '<td>' . $row["email"] . '</td>';

                        // Calcola se l'utente ha un abbonamento ancora valido
                        $abbonamento_valido = abbonamentoValido($row["duration"], $row["order_date"]);
                        $abbonamento_valido ? $color = "green" : $color = "red";
                        $isAdmin = $row["order_id"] > 10 ? "" : "hidden";
                        echo '<td style="font-weight:700;color:'.$color.'">' . ($abbonamento_valido ? 'Abbonamento Attivo' : 'Abbonamento Scaduto') . '</td>';

                        echo '<td class="action-buttons ' . $isAdmin . '"><a onclick="return confirm()" href="modifica_utente.php?id=' . $row["order_id"] . '">Modifica</a><a onclick="return confirm()" href="elimina_utente.php?id=' . $row["order_id"] . '">Elimina</a> <button class="reset-password-button" data-email="' . $row["email"] . '">Reset Password</button></td></tr>';

                    }
                } else {
                    echo '<tr><td colspan="8">Nessun utente disponibile</td></tr>';
                }
                ?>
            </tbody>
        </table>
        <div  class="spiegazione-pag">
            <h1 style=" text-align: -webkit-center;">Legenda Tabella</h1>
            <div style="padding: 15px 0px; display: flex; justify-content: center; text-align: center; flex-wrap: wrap;">
                <div style="width: 30%;">
                <p>
                        <b>ID ordine :</b> Numero ordine riportato da Woocommerce. <br>
                        <b>Nome :</b> Nome cliente. <br>
                        <b>Cognome :</b> Cognome cliente. <br>
                    </p>
                </div>
                <div style="width: 30%;">
                    <p>
                        <b>Data Ordine :</b> Data in cui il cliente ha effettuato un rinnovo. <br>
                        <b>Scadenza :</b> Giorni rimanenti alla scadenza dell'abbonamento. <br>
                        <b>Email :</b> Mail utilizzata per acquisto e accesso al portale . <br>
                    </p>
                </div>
                <div style="width: 30%;">
                    <p>
                        <b>Abbonamento Valido :</b> Validità abbonamento (Attivo/Scaduto). <br>
                        <b>Azione :</b> Effettua la modifica o la visualizzazione dati cliente. <br>
                    </p>
                </div>
            </div>
        </div>

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


<script>
$('.reset-password-button').on('click', function () {
    var email = $(this).data('email');

    var apiUrl = '../process_reset_password.php'; // Sostituisci con il tuo indirizzo API

    if (confirm('Sei sicuro di voler reimpostare la password per l\'utente con email ' + email + '?')) {
        $.ajax({
            type: 'POST',
            url: apiUrl,
            contentType: 'application/json',  // Imposta il tipo di contenuto come JSON
            data: JSON.stringify({ email: email }),  // Converti l'oggetto in una stringa JSON
            success: function (response) {
                alert('Password reimpostata con successo!');
            },
            error: function (error) {
                console.error('Errore durante la reimpostazione della password:', error);
                alert('Si è verificato un errore durante la reimpostazione della password.');
            }
        });
    }
});

</script>
