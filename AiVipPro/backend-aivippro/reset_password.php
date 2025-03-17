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


if (isset($_GET['token'])) {
    $tokenRecupero = $_GET['token'];

    // Utilizza un prepared statement per evitare SQL injection
    $stmt = $conn->prepare("SELECT * FROM abbonamenti_online WHERE token_recupero = ?");
    $stmt->bind_param("s", $tokenRecupero);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Visualizza il modulo per l'impostazione della nuova password
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nuovaPassword = $_POST['nuova_password'];
            // Aggiorna la password nel database
            aggiornaPassword($row, $nuovaPassword);
            // Rimuovi il token di recupero dal database
            rimuoviTokenRecupero($row);
            echo "<div><h3 style='display:block;'>Password aggiornata con successo!</h3><br><div style='margin:auto;text-align:center;width:100%;display:block;'><a href='https://www.zoiyoga.it/area-corsi-online/login.php'> <button style='background:#e50914;color:white;padding:10px;font-size:16px;text-transform:uppercase;border-radius:3px;border-width:0px;margin:auto;text-align:center;'>Torna al login</button></a></div></div>";

        } else {
            echo '<form method="post">
                    Nuova Password: <input type="password" name="nuova_password" required>
                    <input type="submit" value="Imposta Password">
                  </form>';
        }
    } else {
        echo "<div><h3 style='display:block;'>Link di recupero non valido o scaduto.</h3><br><div style='margin:auto;text-align:center;width:100%;display:block;'><a href='https://www.zoiyoga.it/area-corsi-online/login.php'> <button style='background:#e50914;color:white;padding:10px;font-size:16px;text-transform:uppercase;border-radius:3px;border-width:0px;margin:auto;text-align:center;'>Torna al login</button></a></div></div>";
    }
}

function aggiornaPassword($utente, $nuovaPassword) {
    global $conn;
    // Implementa la logica per aggiornare la password nel database
    $passwordHash = password_hash($nuovaPassword, PASSWORD_DEFAULT);
    $idUtente = $utente['order_id']; // Assumi che l'ID sia disponibile nel risultato della query
    $stmt = $conn->prepare("UPDATE abbonamenti_online SET password = ? WHERE order_id = ?");
    $stmt->bind_param("si", $passwordHash, $idUtente);
    $stmt->execute();
}

function rimuoviTokenRecupero($utente) {
    global $conn;
    // Implementa la logica per rimuovere il token di recupero dal database
    $idUtente = $utente['order_id']; // Assumi che l'ID sia disponibile nel risultato della query
    $stmt = $conn->prepare("UPDATE abbonamenti_online SET token_recupero = NULL WHERE order_id = ?");
    $stmt->bind_param("i", $idUtente);
    $stmt->execute();
}
?>
<style>
       body {
           font-family: 'Arial', sans-serif;
           background-color: #141414;
           color: #fff;
           display: flex;
           align-items: center;
           justify-content: center;
           height: 100vh;
           margin: 0;
       }

       .container {
           background-color: #333;
           border-radius: 8px;
           padding: 20px;
           width: 300px;
           text-align: center;
       }

       h2 {
           color: #e50914;
       }

       form {
           display: flex;
           flex-direction: column;
           align-items: center;
           margin-top: 20px;
       }

       input {
           padding: 10px;
           margin: 10px 0;
           width: 100%;
           box-sizing: border-box;
           border: 1px solid #ccc;
           border-radius: 4px;
       }

       input[type="submit"] {
           background-color: #990011;
           color: #fff;
           cursor: pointer;
       }

       input[type="submit"]:hover {
           background-color: #ff1f1f;
       }
   </style>
