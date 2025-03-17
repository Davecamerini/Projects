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


if (!isset($_SESSION["session_user"]) || !isset($_SESSION["session_id"])) {
    header("Location: https://www.zoiyoga.it/area-corsi-online/login.php");
    exit();
}


//controllo se scaduto
    if (isset($_COOKIE['uidAb'])) {
        $usernames = base64_decode(urldecode($_COOKIE['uidAb']));

        $sqly = "SELECT * FROM abbonamenti_online WHERE email = '$usernames'";
        $resulty = $conn->query($sqly);

        if ($resulty->num_rows > 0) {
            $rowy = $resulty->fetch_assoc();
            $abbonamento_valido = abbonamentoValido($rowy["duration"], $rowy["order_date"]);
        } else {
          echo "<div style='background:#fff;height: -webkit-fill-available;height: fill-available;'><div style='padding-top:10%;font-size:20px;text-align:center;margin:auto;font-weight:700;color:#e50914;width:100%;font-family: open-sans,sans-serif;text-transform: uppercase;' class='errore'>Spiacenti, l'abbonamento è scaduto. Impossibile effettuare l'accesso.</div><br><div style='margin:auto;text-align:center;width:100%;'><a href='https://www.zoiyoga.it/area-corsi-online/login.php'> <button style='background:#e50914;color:white;padding:10px;font-size:16px;text-transform:uppercase;border-radius:3px;border-width:0px;margin:auto;text-align:center;'>Torna al login</button></a></div></div>";
             }
             if(!$abbonamento_valido){
             echo "<div style='background:#fff;height: -webkit-fill-available;height: fill-available;'><div style='padding-top:10%;font-size:20px;text-align:center;margin:auto;font-weight:700;color:#e50914;width:100%;font-family: open-sans,sans-serif;text-transform: uppercase;' class='errore'>Spiacenti, l'abbonamento è scaduto. Impossibile effettuare l'accesso.</div><br><div style='margin:auto;text-align:center;width:100%;'><a href='https://www.zoiyoga.it/area-corsi-online/login.php'> <button style='background:#e50914;color:white;padding:10px;font-size:16px;text-transform:uppercase;border-radius:3px;border-width:0px;margin:auto;text-align:center;'>Torna al login</button></a></div></div>";
             }
    }
//fine controllo


$sql = "SELECT *
FROM categorie_corsi_online
ORDER BY 
  CAST(SUBSTRING(categoria, 1, 3) AS UNSIGNED),
  categoria ASC;
";
$result = $conn->query($sql);

// Chiudi la connessione al database
$conn->close();

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Aggiunta delle librerie Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body id="indexCourses">
    <?php include "partial/header.php"; ?>
    <div class="container">
        <h1 class="mt-5 mb-10" style="padding: 60px 0 20px;">Seleziona un corso</h1>
        <div class="category-container">

            <?php
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="category">';
                    echo '<a href="categoria.php?id=' . $row["id"] . '" class="text-decoration-none text-dark">';
                    echo '<img src="https://www.zoiyoga.it/area-corsi-online/php/gestionale/' . $row["copertina"] . '" alt="' . $row["categoria"] . '" class="img-fluid">';
                    echo '<h5 class="mt-2 " style="color:#991b0f;">' . $row["categoria"] . '</h3>';
                    echo '</a>';
                    echo '</div>';
                }
            ?>
        </div>
    </div>

    <!-- Aggiunta delle librerie Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include "partial/footer.php"; ?>
</body>
</html>
