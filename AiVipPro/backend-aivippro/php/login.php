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

if (isset($_SESSION['session_id'])) {
    header('Location: ../index.php');
    exit;
}


if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo "<div class='login-error' style='background:#fff;height: -webkit-fill-available;height: fill-available;'><div style='padding-top:10%;font-size:20px;text-align:center;margin:auto;font-weight:700;color:#e50914;width:100%;font-family: open-sans,sans-serif;text-transform: uppercase;' class='errore'>Inserisci correttamente email e password.</div><br><div style='margin:auto;text-align:center;width:100%;'><a href='https://www.zoiyoga.it/area-corsi-online/login.php'> <button style='background:#e50914;color:white;padding:10px;font-size:16px;text-transform:uppercase;border-radius:3px;border-width:0px;margin:auto;text-align:center;'>Torna al login</button></a></div><br><div style='margin:auto;text-align:center;width:100%;'><a href='https://www.zoiyoga.it/area-corsi-online/password_dimenticata.php'> <button style='background:#e50914;color:white;padding:10px;font-size:16px;text-transform:uppercase;border-radius:3px;border-width:0px;margin:auto;text-align:center;'>Recupera Password</button></a></div></div>";
    } else {
          // Query per ottenere l'utente con l'email specificata
          $sql = "SELECT * FROM abbonamenti_online WHERE email = '$username'";
          $result = $conn->query($sql);

          if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              // Verifica la password con password_verify
              $storedHash = $row['password'];

              if ($storedHash && password_verify($password, $storedHash)) {
                $abbonamento_valido = abbonamentoValido($row["duration"], $row["order_date"]);

                if ($abbonamento_valido) {
                  $_SESSION["session_user"] = htmlspecialchars($_SESSION['session_user'], ENT_QUOTES, 'UTF-8');
                  $_SESSION["session_id"] =  htmlspecialchars($_SESSION['session_id']);
                  // Verifica se l'utente ha ID 1 e imposta un cookie
                  if ($row['order_id'] <= 10) {
                      setcookie('utente_id_admin', 'YXV0ZW50aWNhdG8=', time() + (86400 * 30), "/"); // Cookie valido per 30 giorni
                      setcookie('uidAb', base64_encode($row["email"]), time() + (86400 * 30), "/"); // Cookie valido per 30 giorni
                  } else {
                      setcookie('utente_id_standard', 'YXV0ZW50aWNhdG8=', time() + (86400 * 30), "/"); // Cookie valido per 30 giorni
                  }
                  header('Location: ../index.php');
              } else {
               echo "<div class='login-error' style='background:#fff;height: -webkit-fill-available;height: fill-available;'><div style='padding-top:10%;font-size:20px;text-align:center;margin:auto;font-weight:700;color:#e50914;width:100%;font-family: open-sans,sans-serif;text-transform: uppercase;' class='errore'>Spiacenti, l'abbonamento è scaduto. Impossibile effettuare l'accesso.</div><br><div style='margin:auto;text-align:center;width:100%;'><a href='https://www.zoiyoga.it/area-corsi-online/login.php'> <button style='background:#e50914;color:white;padding:10px;font-size:16px;text-transform:uppercase;border-radius:3px;border-width:0px;margin:auto;text-align:center;'>Torna al login</button></a></div></div>";
                        }
              } else {
                echo "<div class='login-error' style='background:#fff;height: -webkit-fill-available;height: fill-available;'><div style='padding-top:10%;font-size:20px;text-align:center;margin:auto;font-weight:700;color:#e50914;width:100%;font-family: open-sans,sans-serif;text-transform: uppercase;' class='errore'>Credenziali non valide, riprova o effettua un recupero della password.</div><br><div style='margin:auto;text-align:center;width:100%;'><a href='https://www.zoiyoga.it/area-corsi-online/login.php'> <button style='background:#e50914;color:white;padding:10px;font-size:16px;text-transform:uppercase;border-radius:3px;border-width:0px;margin:auto;text-align:center;'>Torna al login</button></a></div><br><div style='margin:auto;text-align:center;width:100%;'><a href='https://www.zoiyoga.it/area-corsi-online/password_dimenticata.php'> <button style='background:#e50914;color:white;padding:10px;font-size:16px;text-transform:uppercase;border-radius:3px;border-width:0px;margin:auto;text-align:center;'>Recupera Password</button></a></div></div>";

              }
          } else {
              echo "<div class='login-error' style='background:#fff;height: -webkit-fill-available;height: fill-available;'><div style='padding-top:10%;font-size:20px;text-align:center;margin:auto;font-weight:700;color:#e50914;width:100%;font-family: open-sans,sans-serif;text-transform: uppercase;' class='errore'>Credenziali non valide, riprova o effettua un recupero della password.</div><br><div style='margin:auto;text-align:center;width:100%;'><a href='https://www.zoiyoga.it/area-corsi-online/login.php'> <button style='background:#e50914;color:white;padding:10px;font-size:16px;text-transform:uppercase;border-radius:3px;border-width:0px;margin:auto;text-align:center;'>Torna al login</button></a></div><br><div style='margin:auto;text-align:center;width:100%;'><a href='https://www.zoiyoga.it/area-corsi-online/password_dimenticata.php'> <button style='background:#e50914;color:white;padding:10px;font-size:16px;text-transform:uppercase;border-radius:3px;border-width:0px;margin:auto;text-align:center;'>Recupera Password</button></a></div></div>";
          }
      }
    }

    printf($msg, '<a href="../login.php">torna indietro</a>');
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
<style>
.login-error{
  background-image: url("https://www.zoiyoga.it/area-corsi-online/images/sfondo.jpg")!important;
  background-size: cover!important;
}
</style>
