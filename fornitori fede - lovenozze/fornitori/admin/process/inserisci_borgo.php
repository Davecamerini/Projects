<?php
// Connessione al database
$servername = "db16.webme.it";
$username = "sitidi_759";
$password = "c2F1K5cd08442336";
$dbname = "sitidi_759";

// Creazione connessione
$conn = new mysqli($servername, $username, $password, $dbname);

// Controlla connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Raccolta dati dal form
$ragione_sociale = mysqli_real_escape_string($conn, $_POST['ragione_sociale']);
$slug = mysqli_real_escape_string($conn, $_POST['slug']);
$descrizione = mysqli_real_escape_string($conn, $_POST['descrizione']);
$citazione = mysqli_real_escape_string($conn, $_POST['citazione']);
$descrizione_due = mysqli_real_escape_string($conn, $_POST['descrizione_due']);
$categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
$tag = mysqli_real_escape_string($conn, $_POST['tag']);
$indirizzo = mysqli_real_escape_string($conn, $_POST['indirizzo']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$telefono = mysqli_real_escape_string($conn, $_POST['telefono']);
$whatsapp = mysqli_real_escape_string($conn, $_POST['whatsapp']);
$votazione_complessiva = mysqli_real_escape_string($conn, $_POST['votazione_complessiva']);
$latitudine = mysqli_real_escape_string($conn, $_POST['latitudine']);
$longitudine = mysqli_real_escape_string($conn, $_POST['longitudine']);
$regione = mysqli_real_escape_string($conn, $_POST['regione']);
$video_links = mysqli_real_escape_string($conn, $_POST['video_links']);

// Gestione dell'upload delle immagini
$img_copertina = '';
if (isset($_FILES['img_copertina']) && $_FILES['img_copertina']['error'] == 0) {
    $name_copertina = basename($_FILES['img_copertina']['name']);
    $img_copertina = 'uploads/' . basename($_FILES['img_copertina']['name']);  // Path relativo alla cartella 'uploads'
    if (move_uploaded_file($_FILES['img_copertina']['tmp_name'], __DIR__ . '/' . $img_copertina)) {
        echo "File caricato con successo.";
    } else {
        echo "Errore nel caricamento dell'immagine di copertina.";
    }
}

// Gestione dei video
$video_files = [];
if (!empty($_FILES['video_files']['name'][0])) {
    // Crea la directory per i video se non esiste
    $video_dir = 'uploads/videos';
    if (!file_exists($video_dir)) {
        mkdir($video_dir, 0777, true);
    }

    // Processa ogni video
    foreach ($_FILES['video_files']['tmp_name'] as $key => $tmp_name) {
        $video_name = $_FILES['video_files']['name'][$key];
        $video_path = $video_dir . '/' . $video_name;
        
        // Verifica se il video esiste già
        if (file_exists($video_path)) {
            // Se il video esiste, usa quello esistente
            $video_files[] = $video_name;
        } else {
            // Se il video non esiste, caricalo
            if (move_uploaded_file($tmp_name, $video_path)) {
                $video_files[] = $video_name;
            } else {
                $errori[] = "Errore nel caricamento del video: " . $video_name;
            }
        }
    }
}
$video_files_str = implode(',', $video_files);

// Gestione della galleria (multiple file upload)
$gallery = '';
if (isset($_FILES['gallery']) && count($_FILES['gallery']['name']) > 0) {
    $gallery_files = [];
    foreach ($_FILES['gallery']['name'] as $index => $gallery_name) {
        if ($_FILES['gallery']['error'][$index] == 0) {
            $gallery_titolo = basename($gallery_name);
            $gallery_path = 'uploads/' . basename($gallery_name);
            if (move_uploaded_file($_FILES['gallery']['tmp_name'][$index], __DIR__ . '/' . $gallery_path)) {
                $gallery_files[] = $gallery_titolo;
            } else {
                echo "Errore nel caricamento del file della galleria: " . $gallery_name;
            }
        }
    }
    // Concatena tutti i percorsi dei file della galleria come una stringa separata da virgole
    $gallery = implode(',', $gallery_files);
}

// Inserimento dati nel database
$sql = "INSERT INTO borghi_scheda (ragione_sociale, slug, descrizione, citazione, descrizione_due, categoria_id, img_copertina, gallery, tag, indirizzo, email, telefono, whatsapp, votazione_complessiva, latitudine, longitudine, regione, video_links, video_files)
VALUES ('$ragione_sociale', '$slug', '$descrizione', '$citazione', '$descrizione_due', '$categoria', '$name_copertina', '$gallery', '$tag', '$indirizzo', '$email', '$telefono', '$whatsapp', '$votazione_complessiva', '$latitudine', '$longitudine', '$regione', '$video_links', '$video_files_str')";

if ($conn->query($sql) === TRUE) {
    // Reindirizzamento alla pagina di successo o lista borghi
    header("Location: ../dashboard.php?page=borghi&success=1");
    exit;
} else {
    echo "Errore: " . $sql . "<br>" . $conn->error;
}

// Chiudi connessione
$conn->close();
?>
