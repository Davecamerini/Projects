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

// Debug: Log the received POST data
error_log("POST data received: " . print_r($_POST, true));

if (isset($_POST['action']) && $_POST['action'] == 'remove_image' && isset($_POST['image']) && isset($_POST['id'])) {
    $image = trim($conn->real_escape_string($_POST['image']));
    $fornitore_id = intval($_POST['id']);

    // Debug: Log the processed variables
    error_log("Processing image: '$image' for fornitore_id: $fornitore_id");

    // Recupera la gallery attuale
    $query = "SELECT gallery FROM fornitori_scheda WHERE id=$fornitore_id";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $fornitore = $result->fetch_assoc();
        $gallery = array_map('trim', array_filter(explode(',', $fornitore['gallery']))); // Remove empty values and trim each item
        
        // Debug: Log the current gallery
        error_log("Current gallery: " . print_r($gallery, true));
        error_log("Looking for image: '$image'");
        error_log("Image exists in gallery: " . (in_array($image, $gallery) ? 'yes' : 'no'));

        // Rimuovi l'immagine dalla gallery
        if (($key = array_search($image, $gallery)) !== false) {
            unset($gallery[$key]);
            
            // Try to delete the physical file
            $file_path = __DIR__ . '/uploads/' . $image;
            error_log("Attempting to delete file: $file_path");
            
            if (file_exists($file_path)) {
                if (!unlink($file_path)) {
                    $response['message'] = "Impossibile eliminare il file fisico. Controlla i permessi.";
                    error_log("Failed to delete file: $file_path");
                    echo json_encode($response);
                    exit;
                }
            }

            // Aggiorna la gallery nel database
            $new_gallery = implode(',', array_filter($gallery)); // Remove empty values and reindex
            $update_query = "UPDATE fornitori_scheda SET gallery='$new_gallery' WHERE id=$fornitore_id";
            
            error_log("Executing query: $update_query");

            if ($conn->query($update_query) === TRUE) {
                $response['success'] = true;
                $response['message'] = "Immagine rimossa con successo.";
            } else {
                $response['message'] = "Errore nel database: " . $conn->error;
                error_log("Database error: " . $conn->error);
            }
        } else {
            $response['message'] = "Immagine non trovata nella gallery. Immagine cercata: '$image'";
            error_log("Image not found in gallery: '$image'");
            error_log("Available images: " . implode(', ', $gallery));
        }
    } else {
        $response['message'] = "Fornitore non trovato.";
        error_log("Fornitore not found with ID: $fornitore_id");
    }
} else {
    $response['message'] = "Parametri mancanti o non validi.";
    error_log("Missing or invalid parameters in POST request");
}

$conn->close();
echo json_encode($response);
?>
