<?php
//il file serve per cancellare ricorsivamente dalla gallery della sezione modifica fornitore le immagini.
require_once('../../../wp-load.php'); // Cambia questo percorso secondo la tua installazione di WordPress

// Controlla se l'utente è loggato e se è amministratore
if (!is_user_logged_in() || !current_user_can('administrator')) {
    wp_redirect(wp_login_url());
    exit;
}

$servername = "db16.webme.it";
$username = "sitidi_759";
$password = "c2F1K5cd08442336";
$dbname = "sitidi_759";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

$response = ['success' => false];
if (isset($_POST['action']) && $_POST['action'] == 'remove_image' && isset($_POST['image']) && isset($_POST['id'])) {
    $image = $conn->real_escape_string($_POST['image']);
    $fornitore_id = intval($_POST['id']);

    // Recupera la gallery attuale
    $query = "SELECT gallery FROM fornitori_scheda WHERE id=$fornitore_id";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $fornitore = $result->fetch_assoc();
        $gallery = explode(',', $fornitore['gallery']);

        // Rimuovi l'immagine dalla gallery
        if (($key = array_search($image, $gallery)) !== false) {
            unset($gallery[$key]);
        }

        // Aggiorna la gallery nel database
        $new_gallery = implode(',', $gallery);
        $update_query = "UPDATE fornitori_scheda SET gallery='$new_gallery' WHERE id=$fornitore_id";

        if ($conn->query($update_query) === TRUE) {
            $response['success'] = true;
        } else {
            $response['message'] = "Errore: " . $conn->error;
        }
    } else {
        $response['message'] = "Fornitore non trovato.";
    }
}

$conn->close();
echo json_encode($response);
?>
