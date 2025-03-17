<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Includi le librerie di PHPMailer
require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

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


// Ottenere i dati email dal corpo della richiesta POST
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
} else {
    $email = $_POST['email'] ?? '';
}

if (empty($email)) {
    $msg = 'Inserisci l\'indirizzo email %s';
} else {
    // Verifica se l'email è associata a un account
    $sql = "SELECT * FROM abbonamenti_online WHERE email = '$email'";
   
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Genera un token di recupero univoco
        $tokenRecupero = bin2hex(random_bytes(32));

        // Aggiorna il token_recupero nel database
        $sqlUpdateToken = "UPDATE abbonamenti_online SET token_recupero = '$tokenRecupero' WHERE email = '$email'";
        $conn->query($sqlUpdateToken);

        // Invia l'email con il link di recupero
        $linkRecupero = "https://www.zoiyoga.it/area-corsi-online/reset_password.php?token=$tokenRecupero";
        $subject = "Recupero Password per ZoiYoga";

        // Creazione di un oggetto PHPMailer
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
       //  $mail->SMTPDebug = 1; // Dettaglio del debug (0 per disabilitare il debug)
 
         // Mittente e destinatario
         $mail->setFrom('info@zoiyoga.it', 'ZoiYoga');
         $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;

            // Messaggio email
            $mail->Body = "
            <html>
            <head>
              <title>$subject</title>
            </head>
            <body>
              <p>Gentile Utente,</p>
              <p>Ti stiamo inviando questo messaggio perché hai richiesto il recupero della password per il tuo account su ZoiYoga.</p>
              <p>Clicca sul seguente link per reimpostare la tua password:</p>
              <p><a href='$linkRecupero'>$linkRecupero</a></p>
              <p>Una volta cliccato sul link, sarai reindirizzato a una pagina dove potrai scegliere e confermare la tua nuova password.</p>
              <p>Se non hai richiesto il recupero della password o se hai domande, contattaci immediatamente.</p>
              <br>
              <img src='https://www.zoiyoga.it/wp-content/uploads/2023/08/lodo-zoi-yoga.png' alt='ZoiYoga Logo' width='150'>
              <br>
              <p>Grazie,<br>Il Team di ZoiYoga</p>
            </body>
            </html>
            ";

            // Invio dell'email
            $mail->send();

            // Messaggio di successo
            echo "<div style='background:#fff;height: -webkit-fill-available;height: fill-available;'><div style='padding-top:10%;font-size:20px;text-align:center;margin:auto;font-weight:700;color:#e50914;width:100%;font-family: open-sans,sans-serif;text-transform: uppercase;' class='errore'>Abbiamo inviato una mail con le istruzioni per il recupero della password.</div><br><div style='margin:auto;text-align:center;width:100%;'><a href='https://www.zoiyoga.it/area-corsi-online/login.php'> <button style='background:#e50914;color:white;padding:10px;font-size:16px;text-transform:uppercase;border-radius:3px;border-width:0px;margin:auto;text-align:center;'>Torna al login</button></a></div></div>";
        } catch (Exception $e) {
            // Errore nell'invio dell'email
            echo "Errore nell'invio dell'email: {$mail->ErrorInfo}";
        }
    } else {
        echo "<div style='background:#fff;height: -webkit-fill-available;height: fill-available;'><div style='padding-top:10%;font-size:20px;text-align:center;margin:auto;font-weight:700;color:#e50914;width:100%;font-family: open-sans,sans-serif;text-transform: uppercase;' class='errore'>Nessun account trovato con questo indirizzo email</div><br><div style='margin:auto;text-align:center;width:100%;'><a href='https://www.zoiyoga.it/area-corsi-online/login.php'> <button style='background:#e50914;color:white;padding:10px;font-size:16px;text-transform:uppercase;border-radius:3px;border-width:0px;margin:auto;text-align:center;'>Torna al login</button></a></div></div>";
    }
}
printf($msg, '<a href="../login.php">torna indietro</a>');
?>