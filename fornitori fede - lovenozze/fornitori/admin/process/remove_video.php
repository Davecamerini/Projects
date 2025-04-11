<?php
require_once('../../../wp-load.php');

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

if (isset($_POST['action']) && $_POST['action'] == 'remove_video' && isset($_POST['video']) && isset($_POST['id']) && isset($_POST['type'])) {
    $video = trim($conn->real_escape_string($_POST['video']));
    $id = intval($_POST['id']);
    $type = trim($conn->real_escape_string($_POST['type']));

    // Determina la tabella corretta in base al tipo
    $table = ($type === 'borgo') ? 'borghi_scheda' : 'fornitori_scheda';
    $id_field = ($type === 'borgo') ? 'id' : 'id';

    // Recupera i video attuali
    $query = "SELECT video_files FROM $table WHERE $id_field=$id";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $record = $result->fetch_assoc();
        $videos = array_map('trim', array_filter(explode(',', $record['video_files'])));
        
        // Rimuovi il video dalla lista
        $videos = array_diff($videos, [$video]);
        $new_videos = implode(',', $videos);
        
        // Aggiorna il database
        $update_query = "UPDATE $table SET video_files='$new_videos' WHERE $id_field=$id";
        if ($conn->query($update_query)) {
            // Rimuovi il file fisico
            $video_path = __DIR__ . '/uploads/videos/' . $video;
            if (file_exists($video_path)) {
                unlink($video_path);
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Errore nell\'aggiornamento del database']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Record non trovato']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Parametri mancanti']);
}

$conn->close();
?> 