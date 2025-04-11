<?php
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

$success = false;
$message = "";
$fornitore_id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Esegui la logica per aggiornare il fornitore nel database
    $ragione_sociale = $conn->real_escape_string($_POST['ragione_sociale']);
    $descrizione = $conn->real_escape_string($_POST['descrizione']);
    $citazione = $conn->real_escape_string($_POST['citazione']);
    $descrizione_due = $conn->real_escape_string($_POST['descrizione_due']);
    $tag = $conn->real_escape_string($_POST['tag']);
    $indirizzo = $conn->real_escape_string($_POST['indirizzo']);
    $regione = $conn->real_escape_string($_POST['regione']);
    $email = $conn->real_escape_string($_POST['email']);
    $telefono = $conn->real_escape_string($_POST['telefono']);
    $whatsapp = $conn->real_escape_string($_POST['whatsapp']);
    $votazione_complessiva = $conn->real_escape_string($_POST['votazione_complessiva']);
    $categoria_id = intval($_POST['categoria']); // Nuovo campo per la categoria
    $video_links = $conn->real_escape_string($_POST['video_links']);

    // Recupera la gallery attuale dal database
    $query = "SELECT img_copertina, gallery FROM fornitori_scheda WHERE id=$fornitore_id";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $fornitore = $result->fetch_assoc();
        $current_gallery = $fornitore['gallery'];
        $current_img_copertina = $fornitore['img_copertina'];
    }

    // Gestione dell'immagine di copertina
    if (isset($_FILES['img_copertina']) && $_FILES['img_copertina']['error'] == UPLOAD_ERR_OK) {
        $img_copertina_name = basename($_FILES['img_copertina']['name']);
        $img_copertina_tmp = $_FILES['img_copertina']['tmp_name'];
        $img_copertina_upload_path = "uploads/" . $img_copertina_name; // Cambia questo percorso

        // Sposta il file nella directory di upload
        if (move_uploaded_file($img_copertina_tmp, $img_copertina_upload_path)) {
            $img_copertina = $img_copertina_name; // Aggiorna la variabile dell'immagine di copertina
        } else {
            $message = "Errore nel caricamento dell'immagine di copertina.";
            $success = false;
        }
    } else {
        // Se non è stato caricato un nuovo file, mantieni l'immagine esistente
        $img_copertina = $current_img_copertina;
    }

    // Se sono state caricate nuove immagini
    if (isset($_FILES['gallery'])) {
        // Crea un array per tenere traccia delle nuove immagini
        $new_gallery_images = [];
        $total_files = count($_FILES['gallery']['name']);

        // Itera attraverso i file caricati
        for ($i = 0; $i < $total_files; $i++) {
            $tmp_file = $_FILES['gallery']['tmp_name'][$i];
            $file_name = basename($_FILES['gallery']['name'][$i]);
            $upload_path = "uploads/" . $file_name; // Cambia questo percorso

            // Sposta il file nella directory di upload
            if (move_uploaded_file($tmp_file, $upload_path)) {
                $new_gallery_images[] = $file_name; // Aggiungi il nome del file caricato all'array
            }
        }

        // Aggiungi le nuove immagini alla gallery esistente
        if (!empty($current_gallery)) {
            $current_images = explode(',', $current_gallery);
            $current_images = array_map('trim', $current_images); // Rimuovi spazi bianchi
            $new_gallery_images = array_merge($current_images, $new_gallery_images); // Combina le due gallery
        }

        // Crea la stringa della gallery finale
        $final_gallery = implode(',', $new_gallery_images);
    } else {
        // Se non sono state caricate nuove immagini, mantieni la gallery esistente
        $final_gallery = $current_gallery;
    }

    // Gestione dell'upload dei video
    $video_files = '';
    if (isset($_FILES['video_files']) && count($_FILES['video_files']['name']) > 0) {
        // Crea la directory videos se non esiste
        $videos_dir = __DIR__ . '/uploads/videos';
        if (!file_exists($videos_dir)) {
            mkdir($videos_dir, 0777, true);
        }

        $video_files_array = [];
        foreach ($_FILES['video_files']['name'] as $index => $video_name) {
            if ($_FILES['video_files']['error'][$index] == 0) {
                $video_titolo = basename($video_name);
                $video_path = 'uploads/videos/' . basename($video_name);
                if (move_uploaded_file($_FILES['video_files']['tmp_name'][$index], __DIR__ . '/' . $video_path)) {
                    $video_files_array[] = $video_titolo;
                } else {
                    echo "Errore nel caricamento del video: " . $video_name;
                }
            }
        }
        // Concatena tutti i percorsi dei video come una stringa separata da virgole
        $video_files = implode(',', $video_files_array);
    }

    // Recupera i video esistenti dal database
    $query = "SELECT video_files FROM fornitori_scheda WHERE id=$fornitore_id";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $fornitore = $result->fetch_assoc();
        $current_video_files = $fornitore['video_files'];
        if (!empty($current_video_files) && !empty($video_files)) {
            $video_files = $current_video_files . ',' . $video_files;
        } elseif (empty($video_files)) {
            $video_files = $current_video_files;
        }
    }

    // Query per aggiornare il fornitore nel database
    $update_query = "UPDATE fornitori_scheda SET
        ragione_sociale='$ragione_sociale',
        descrizione='$descrizione',
        citazione='$citazione',
        descrizione_due='$descrizione_due',
        img_copertina='$img_copertina',
        gallery='$final_gallery',
        tag='$tag',
        indirizzo='$indirizzo',
        email='$email',
        telefono='$telefono',
        whatsapp='$whatsapp',
        votazione_complessiva='$votazione_complessiva',
        categoria_id='$categoria_id',
        regione='$regione',
        video_links='$video_links',
        video_files='$video_files'
        WHERE id=$fornitore_id";

    if ($conn->query($update_query) === TRUE) {
        $success = true;
        $message = "Fornitore aggiornato con successo!";
    } else {
        $message = "Errore: " . $conn->error;
    }
}

$conn->close();

if ($success) {
    // Reindirizza alla lista fornitori dopo 2 secondi
    header("Location: ../dashboard.php?page=fornitori&success=1");
    exit();
} else {
    // Ritorna al form di modifica con un messaggio di errore
    header("Location: ../modifica_fornitore.php?id=$fornitore_id&error=1");
    exit();
}
