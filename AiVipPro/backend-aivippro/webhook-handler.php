<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';




function getAllOrders() {
    $url = 'https://www.zoiyoga.it/wp-json/wc/v3/orders';
    $username = 'ck_aaed1e6833f6286d7568544abd9f273f334792ff';
    $password = 'cs_816002fc468bc6f5f93c8dc8dd7cd35f3b7dd36c';

    // Configura l'autenticazione di base
    $auth = base64_encode("$username:$password");

    // Configura le opzioni della richiesta
    $options = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Basic ' . $auth,
        ],
    ];

    // Inizializza e esegui la richiesta cURL
    $curl = curl_init();
    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);

    // Gestisci eventuali errori nella richiesta cURL
    if (curl_errno($curl)) {
        echo 'Errore nella richiesta cURL: ' . curl_error($curl);
    }

    // Chiudi la risorsa cURL
    curl_close($curl);

    // Decodifica la risposta JSON
    $orders = json_decode($response, true);

    return $orders;
}

// Utilizza la funzione per ottenere tutte le ordinazioni
$allOrders = getAllOrders();
//print_r($allOrders);
// Filtra gli ordini con prodotti di ID 661 e 726
$filteredOrders = array_filter($allOrders, function ($order) {
    foreach ($order['line_items'] as $item) {
        if ($item['product_id'] == 661 && $order['status'] == "completed" || $item['product_id'] == 726 && $order['status'] == "completed") {
            return true;
        }
    }
    return false;
});
//print_r($allOrders);
// Stampa le informazioni sugli ordini filtrati
/*echo "Informazioni sugli ordini con prodotti di ID 661 e 726:\n";
foreach ($filteredOrders as $order) {
    echo "ID Ordine: {$order['id']}\n";
    echo "Nome Utente: {$order['billing']['first_name']}\n";
    echo "Cognome Utente: {$order['billing']['last_name']}\n";
    echo "ID Prodotti Acquistati:\n";

    foreach ($order['line_items'] as $item) {
        echo "- {$item['product_id']}\n";
    }

    echo "Stato Ordine: {$order['status']}\n";
    echo "Data Ordine: {$order['date_created']}\n";
    echo "-----------------------\n";
}*/

////////////////////////////////////////////////////Fine stampa API///////////////////////////////////////


// Funzione per connettersi al database
function connectToDatabase() {
    $host = "db28.webme.it";  // Inserisci il tuo host del database
    $username = "sitidi_571";  // Inserisci il tuo username del database
    $password = "NL9GP5tq";  // Inserisci la tua password del database
    $database = "sitidi_571";  // Inserisci il nome del tuo database

    $conn = new mysqli($host, $username, $password, $database);

    // Verifica la connessione
    if ($conn->connect_error) {
        die("Connessione al database fallita: " . $conn->connect_error);
    }

    return $conn;
}



function insertDataIntoDatabase($orderId, $firstName, $lastName, $productIds, $orderStatus, $orderDate, $email, $duration, $randomPassword, $randomPassword_crypt) {
    $conn = connectToDatabase();

    // Calcola la data di scadenza in base alla durata
    $abbonamento_valido = abbonamentoValido($duration, $orderDate);

    // Verifica se l'utente è già presente nel database
    $userExists = userExists($email);

    if ($userExists) {

      $dati_vecchio_utente_attivo = get_data_utente_presente($email);
      $email_old = $dati_vecchio_utente_attivo["email"];
      $orderId_old = $dati_vecchio_utente_attivo["order_id"];
      $firstName_old = $dati_vecchio_utente_attivo["first_name"];
      $lastName_old = $dati_vecchio_utente_attivo["last_name"];
      $productIds_old = $dati_vecchio_utente_attivo["product_ids"];
      $orderStatus_old = $dati_vecchio_utente_attivo["order_status"];
      $orderDate_old = $dati_vecchio_utente_attivo["order_date"];
      $duration_old = $dati_vecchio_utente_attivo["duration"];
        // Se l'utente esiste, verifica se l'ordine è scaduto
        if (!$abbonamento_valido) {
            // Se l'ordine è scaduto, aggiorna l'utente con i nuovi dati

              insertUserHistory($email, $orderId, $firstName, $lastName, $productIds, $orderStatus, $orderDate, $duration);
              deleteDataFromActive($orderId);

            // Invia una mail di ringraziamento per il rinnovo
            //sendRenewalThankYouEmail($email);
        } else {
          //se l'user ha già un abbonamento valido
          //durata da aggiungere a quella ancora attiva
          $sum_duration = $duration;
          //la funzione restituisce il numero intero dei giorni mancanti nell'abbonamento ancora attivo.
          $duration_abbonamento_valido = get_abbonamento_attivo($email);
          //devo spostare l'abbonamento vecchio in user history e modificare la duration di quello nuovo.

          //print_r($dati_vecchio_utente_attivo);
          //aggiorna utente con nuova data_scadenza
          if ($orderDate > $orderDate_old && $email_old == $email){
            $sumDuration = $duration_abbonamento_valido + $duration;

            uploadUserHistory($email_old, $orderId_old, $firstName_old, $lastName_old, $productIds_old, $orderStatus_old, $orderDate_old, $duration_old);
            uploadNewRecord($orderId, $firstName, $lastName, $productIds, $orderStatus, $orderDate, $email, $sumDuration);
            //sendRenewalThankYouEmail($email, $randomPassword);
          }
        }

      } else if ($abbonamento_valido) {

            // Se l'utente non esiste, inserisci i nuovi dati nel database
            insertNewUserData($orderId, $firstName, $lastName, $productIds, $orderStatus, $orderDate, $email, $duration, $randomPassword, $randomPassword_crypt);
            // Invia una mail di benvenuto ai nuovi iscritti
            $result = findEmailInUserHistory($emailToSearch);
            if($result){
              sendRenewalThankYouEmail($email, $randomPassword);
            } else {
              sendWelcomeEmail($email, $randomPassword);
            }
        }

    // Chiudi la connessione al database
    $conn->close();
}

//funzione che inserisce nello storico per sommare il rimanente al nuovo acquisto
function uploadUserHistory($email_old, $orderId_old, $firstName_old, $lastName_old, $productIds_old, $orderStatus_old, $orderDate_old, $duration_old) {
    $conn = connectToDatabase();

    // Verifica se il record esiste già
    if (userHistory_old_RecordExists($email_old, $orderDate_old)) {
        echo "Il record per l'email $email_old e la data $orderDate_old esiste già nello storico.\n";
        $check = false;
    } else {
        // Prepara la query per aggiornare i dati dell'utente
        $stmt = $conn->prepare("INSERT INTO abbonamenti_scaduti (first_name, last_name, product_ids, order_status, order_date, email, duration, id)
                               VALUES  (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssss", $firstName_old, $lastName_old, $productIds_old, $orderStatus_old, $orderDate_old, $email_old, $duration_old, $orderId_old);

        // Esegui la query
        if ($stmt->execute()) {
            echo "Dati dell'utente inseriti in storico.\n";
            $check = true;
        } else {
            echo "Errore nell'inserimento dello storico: " . $stmt->error . "\n";
            $check = false;
        }
    }

    // Chiudi la connessione al database
    $conn->close();
    return $check;
}

function uploadUserHistoryOldInsert($email, $orderId, $firstName, $lastName, $productIds, $orderStatus, $orderDate, $duration) {
    $conn = connectToDatabase();

    // Verifica se il record esiste già
    if (userHistoryRecordExists($email, $orderDate)) {
        echo "Il record per l'email $email e la data $orderDate esiste già nello storico.\n";
        $check = false;
    } else {
        // Prepara la query per aggiornare i dati dell'utente
        $stmt = $conn->prepare("INSERT INTO abbonamenti_scaduti (first_name, last_name, product_ids, order_status, order_date, email, duration, id)
                               VALUES  (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssss", $firstName, $lastName, $productIds, $orderStatus, $orderDate, $email, $duration, $orderId);

        // Esegui la query
        if ($stmt->execute()) {
            echo "Dati dell'utente inseriti in storico.\n";
            $check = true;
        } else {
            echo "Errore nell'inserimento dello storico: " . $stmt->error . "\n";
            $check = false;
        }
    }

    // Chiudi la connessione al database
    $conn->close();
    return $check;
}


function uploadNewRecordOldInsert($orderId_old, $firstName_old, $lastName_old, $productIds_old, $orderStatus_old, $orderDate_old, $email, $sumDuration){
  $conn = connectToDatabase();

  // Prepara la query di inserimento
  $stmt = $conn->prepare("UPDATE abbonamenti_online
                          SET order_id = ?, first_name = ?, last_name = ?, product_ids = ?, order_status = ?,
                              order_date = ?, duration = ?
                          WHERE email = ?");
  $stmt->bind_param("ssssssss", $orderId_old, $firstName_old, $lastName_old, $productIds_old, $orderStatus_old,
                    $orderDate_old, $sumDuration, $email);

  // Esegui la query
  if ($stmt->execute()) {
      echo "Nuovi dati dell'utente inseriti con successo nel database.\n";
  } else {
      echo "Errore nell'inserimento dei nuovi dati dell'utente nel database: " . $stmt->error . "\n";
  }

  // Chiudi la connessione al database
  $conn->close();
}


  //restituisce i dati del record utente già presente in abbonamenti attivi
  function get_data_utente_presente($email){
    $conn = connectToDatabase();

    // Prepara la query con un'istruzione preparata
    $stmt = $conn->prepare("SELECT * FROM abbonamenti_online WHERE email = ?");
    $stmt->bind_param("s", $email);

    // Esegui la query
    $stmt->execute();
    $result = $stmt->get_result();

    // Chiudi la connessione al database
    $conn->close();

    // Restituisci true se l'utente esiste, altrimenti false
    return $result->num_rows > 0 ? $result->fetch_assoc() : false;
  }


//restituisce il numero dei giorni mancanti alla fine dell'abbonamento già presente in abbonamenti attivi
function get_abbonamento_attivo($email) {
    $conn = connectToDatabase();

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




function findEmailInUserHistory($email) {
    $conn = connectToDatabase();

    // Prepara la query con un'istruzione preparata
    $stmt = $conn->prepare("SELECT * FROM abbonamenti_scaduti WHERE email = ?");
    $stmt->bind_param("s", $email);

    // Esegui la query
    $stmt->execute();
    $result = $stmt->get_result();

    // Chiudi la connessione al database
    $conn->close();

    // Restituisci i risultati della query
    return $result->fetch_assoc();
}

function userExists($email) {
    $conn = connectToDatabase();

    // Prepara la query con un'istruzione preparata
    $stmt = $conn->prepare("SELECT * FROM abbonamenti_online WHERE email = ?");
    $stmt->bind_param("s", $email);

    // Esegui la query
    $stmt->execute();
    $result = $stmt->get_result();

    // Chiudi la connessione al database
    $conn->close();

    // Restituisci true se l'utente esiste, altrimenti false
    return $result->num_rows > 0;
}

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


function deleteDataFromActive($orderId) {
    $conn = connectToDatabase();
    // Prepara la query per eliminare i dati dell'utente
    $stmt = $conn->prepare("DELETE FROM abbonamenti_online WHERE order_id = ?");
    $stmt->bind_param("s", $orderId);
    // Esegui la query
    if ($stmt->execute()) {
        echo "Dati eliminati da online.\n";
        $check = true;
    } else {
        echo "Errore nell'eliminazione da lista attivi: " . $stmt->error . "\n";
        $check = false;
    }
    // Chiudi la connessione al database
    $conn->close();
    return $check;
}

function deleteDataFromActiveOld($orderId_old) {
    $conn = connectToDatabase();
    // Prepara la query per eliminare i dati dell'utente
    $stmt = $conn->prepare("DELETE FROM abbonamenti_online WHERE order_id = ?");
    $stmt->bind_param("s", $orderId);
    // Esegui la query
    if ($stmt->execute()) {
        echo "Dati eliminati da online.\n";
        $check = true;
    } else {
        echo "Errore nell'eliminazione da lista attivi: " . $stmt->error . "\n";
        $check = false;
    }
    // Chiudi la connessione al database
    $conn->close();
    return $check;
}


function insertUserHistory($email, $orderId, $firstName, $lastName, $productIds, $orderStatus, $orderDate, $duration) {
    $conn = connectToDatabase();

    // Verifica se il record esiste già
    if (userHistoryRecordExists($email, $orderDate)) {
        echo "Il record per l'email $email e la data $orderDate esiste già nello storico.\n";
        $check = false;
    } else {
        // Prepara la query per aggiornare i dati dell'utente
        $stmt = $conn->prepare("INSERT INTO abbonamenti_scaduti (first_name, last_name, product_ids, order_status, order_date, email, duration)
                               VALUES  (?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssss", $firstName, $lastName, $productIds, $orderStatus, $orderDate, $email, $duration);

        // Esegui la query
        if ($stmt->execute()) {
            echo "Dati dell'utente inseriti in storico.\n";
            $check = true;
        } else {
            echo "Errore nell'inserimento dello storico: " . $stmt->error . "\n";
            $check = false;
        }
    }

    // Chiudi la connessione al database
    $conn->close();
    return $check;
}

// Funzione per verificare se il record esiste già nello storico
function userHistoryRecordExists($email, $orderDate) {
    $conn = connectToDatabase();

    // Prepara la query con un'istruzione preparata
    $stmt = $conn->prepare("SELECT * FROM abbonamenti_scaduti WHERE email = ? AND order_date = ?");
    $stmt->bind_param("ss", $email, $orderDate);

    // Esegui la query
    $stmt->execute();
    $result = $stmt->get_result();

    // Chiudi la connessione al database
    $conn->close();

    // Restituisci true se il record esiste, altrimenti false
    return $result->num_rows > 0;
}

function userHistory_old_RecordExists($email_old, $orderDate_old) {
    $conn = connectToDatabase();

    // Prepara la query con un'istruzione preparata
    $stmt = $conn->prepare("SELECT * FROM abbonamenti_scaduti WHERE email = ? AND order_date = ?");
    $stmt->bind_param("ss", $email, $orderDate);

    // Esegui la query
    $stmt->execute();
    $result = $stmt->get_result();

    // Chiudi la connessione al database
    $conn->close();

    // Restituisci true se il record esiste, altrimenti false
    return $result->num_rows > 0;
}

//inserisci utente negli abbonamenti attivi solo se non presente già nella tabella abbonamenti attivi
function insertNewUserData($orderId, $firstName, $lastName, $productIds, $orderStatus, $orderDate, $email, $duration, $randomPassword, $randomPassword_crypt) {
    $conn = connectToDatabase();

    // Prepara la query di inserimento
    $stmt = $conn->prepare("INSERT INTO abbonamenti_online
                            (order_id, first_name, last_name, product_ids, order_status, order_date, duration, email, password)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $orderId, $firstName, $lastName, $productIds, $orderStatus,
                      $orderDate, $duration, $email, $randomPassword_crypt);

    // Esegui la query
    if ($stmt->execute()) {
        echo "Nuovi dati dell'utente inseriti con successo nel database.\n";
    } else {
        echo "Errore nell'inserimento dei nuovi dati dell'utente nel database: " . $stmt->error . "\n";
    }

    // Chiudi la connessione al database
    $conn->close();
}

//aggiorna l'utente con il suo nuovo acquisto se ha già un abbonamento attivo ed ha acquistato prima della scadenza
function uploadNewRecord($orderId, $firstName, $lastName, $productIds, $orderStatus, $orderDate, $email, $sumDuration) {
    $conn = connectToDatabase();

    // Prepara la query di inserimento
    $stmt = $conn->prepare("UPDATE abbonamenti_online
                            SET order_id = ?, first_name = ?, last_name = ?, product_ids = ?, order_status = ?,
                                order_date = ?, duration = ?
                            WHERE email = ?");
    $stmt->bind_param("ssssssss", $orderId, $firstName, $lastName, $productIds, $orderStatus,
                      $orderDate, $sumDuration, $email);

    // Esegui la query
    if ($stmt->execute()) {
        echo "Nuovi dati dell'utente inseriti con successo nel database.\n";
    } else {
        echo "Errore nell'inserimento dei nuovi dati dell'utente nel database: " . $stmt->error . "\n";
    }

    // Chiudi la connessione al database
    $conn->close();
}


function sendWelcomeEmail($email, $randomPassword) {
    $mail = new PHPMailer(true);

    try {
        // Configura le impostazioni del server SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.webme.it'; // Inserisci il tuo server SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@zoiyoga.it'; // Inserisci il tuo indirizzo email
        $mail->Password   = 'ammyoga2020!'; // Inserisci la tua password email
        $mail->SMTPSecure = 'ssl'; // Può essere 'tls' o 'ssl', verifica con il tuo provider
        $mail->Port       = 465; // Porta SMTP del tuo provider


        // Debug
        $mail->SMTPDebug = 0; // Dettaglio del debug (0 per disabilitare il debug)

        // Mittente e destinatario
        $mail->setFrom('info@zoiyoga.it', 'ZoiYoga');
        $mail->addAddress($email);

        // Contenuto dell'email
        $mail->isHTML(true);
        $mail->Subject = "Benvenuto su ZoiYoga";
        $mail->Body    = "
        <html>
        <head>
        </head>
        <body>
            <div class='container'>
                <img width='220' height='auto' src='https://www.zoiyoga.it/wp-content/uploads/2023/08/lodo-zoi-yoga.png' alt='ZoiYoga Logo'>
                <h2>Grazie per esserti iscritto/a a ZoiYoga!</h2>
                <p>Ciao $email,</p>
                <p>Benvenuto/a su ZoiYoga. Siamo felici di averti con noi!</p>
                <p>Scopri tutti i corsi che abbiamo preparato per te.</p>
                <p>Per accedere alla tua area riservata, segui questi passaggi:</p>
                <p>
                    1. Visita il link al bottone
                    <a href='https://www.zoiyoga.it/area-corsi-online/' class='button' style='background-color:#990011;
                    color: white;
                    padding: 10px 20px;
                    text-decoration: none;
                    display: inline-block;
                    font-size: 16px;
                    margin-top: 20px;'>Accedi</a>.
                </p>
                <p>2. Inserisci la mail utilizzata per l'acquisto e questa password generata automaticamente :</p>
                <h3>$randomPassword </h3>
                <p>(potrai cambiarla effettuando un recupero password dall'area login)</p>
                <p>3. Goditi tutti i nostri corsi proposti nell'area Dashboard.</p>
                <p>Grazie e buona pratica!</p>
            </div>
        </body>
    </html>

        ";

        // Invia l'email
        $mail->send();
        echo 'Email inviata con successo!';
    } catch (Exception $e) {
        echo "Errore nell'invio dell'email: {$mail->ErrorInfo}";
    }
}

function sendRenewalThankYouEmail($email, $randomPassword) {
    // Crea un'istanza di PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configura le impostazioni del server SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.webme.it'; // Inserisci il tuo server SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@zoiyoga.it'; // Inserisci il tuo indirizzo email
        $mail->Password   = 'ammyoga2020!'; // Inserisci la tua password email
        $mail->SMTPSecure = 'ssl'; // Può essere 'tls' o 'ssl', verifica con il tuo provider
        $mail->Port       = 465; // Porta SMTP del tuo provider

        // Debug
        $mail->SMTPDebug = 1; // Dettaglio del debug (0 per disabilitare il debug)

        $subject = "Grazie per il Rinnovo su ZoiYoga";


        // Mittente e destinatario
        $mail->setFrom('info@zoiyoga.it', 'ZoiYoga');
        $mail->addAddress($email);


        // Contenuto dell'email
        $mail->isHTML(true);
        $mail->Subject = "Grazie per il Rinnovo";
        $mail->Body = "
        <html>
        <head>
            <style>
            body {
                font-family: 'Arial', sans-serif;
                background-color: #f4f4f4;
                text-align: center;
            }

            .container {
                background-color: #fff;
                border-radius: 8px;
                padding: 20px;
                width: 400px;
                margin: 20px auto;
            }

            img {
                max-width: 100%;
                height: auto;
            }

            h2 {
                color: #e50914;
            }
            </style>
        </head>
        <body>
            <div class='container'>
                <img src='https://www.zoiyoga.it/wp-content/uploads/2023/08/lodo-zoi-yoga.png' alt='ZoiYoga Logo'>
                <h2>Grazie per il Rinnovo su ZoiYoga!</h2>
                <p>Ciao $email,</p>
                <p>Grazie per aver rinnovato il tuo abbonamento su ZoiYoga. Apprezziamo la tua fedeltà!</p>
                <p>Per questioni di sicurezza abbiamo generato una password casuale che potrai cambiare effettuando un recupero password.</p>
                <p>Per accedere all'area riservata, inserisci la mail utilizzata per l'acquisto e questa password generata automaticamente $randomPassword.</p>
                <p>Continua a praticare e goditi i nostri corsi.</p>
                <p>Grazie ancora e buona pratica!</p>
            </div>
        </body>
        </html>
    ";

        // Invia l'email
        $mail->send();
        echo 'Email inviata con successo!';
    } catch (Exception $e) {
        echo "Errore nell'invio dell'email: {$mail->ErrorInfo}";
    }
}


// Utilizza la funzione per ottenere tutte le ordinazioni
$allOrders = getAllOrders();

// Filtra gli ordini con prodotti di ID 661
$filteredOrders = array_filter($allOrders, function ($order) {
    foreach ($order['line_items'] as $item) {
      if ($item['product_id'] == 661 && $order['status'] == "completed" || $item['product_id'] == 726 && $order['status'] == "completed") {
            return true;
        }
    }
    return false;
});

$prodotto_anno = 365;
//$prodotto_tre_mesi = 90;
$prodotto_un_mese = 30;
$characters = '0123456789abcdefghilmnopqrstuvz';
$passwordLength = 8;
// Genera la password casuale
$randomPassword = '';
for ($i = 0; $i < $passwordLength; $i++) {
 $randomPassword .= $characters[rand(0, strlen($characters) - 1)];
}
// Inserisci i dati nel database per ogni ordine filtrato
foreach ($filteredOrders as $order) {
    $orderId = $order['id'];
    $firstName = $order['billing']['first_name'];
    $lastName = $order['billing']['last_name'];
    $email = $order['billing']['email'];
    $productIds = implode(',', array_column($order['line_items'], 'product_id'));
    $orderStatus = $order['status'];
    $orderDate = $order['date_created'];
    $randomPassword_crypt = password_hash($randomPassword, PASSWORD_DEFAULT);
    $duration = $order['line_items'][0]['product_id'] == 661 ? $prodotto_anno : $prodotto_un_mese; //abbonamento annuale
    // Inserisci dati nel database
    insertDataIntoDatabase($orderId, $firstName, $lastName, $productIds, $orderStatus, $orderDate, $email, $duration, $randomPassword, $randomPassword_crypt);
}

?>
