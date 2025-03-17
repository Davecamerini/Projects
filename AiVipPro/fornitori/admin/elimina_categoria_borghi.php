<?php
// Includi WordPress
require_once('../../wp-load.php'); // Modifica il percorso a seconda della tua configurazione

// Controlla se l'utente è loggato e se è amministratore
if (!is_user_logged_in() || !current_user_can('administrator')) {
    wp_redirect(wp_login_url());
    exit;
}

// Connessione al database
$servername = "db16.webme.it";
$username = "sitidi_759";
$password = "c2F1K5cd08442336";
$dbname = "sitidi_759";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

// Ottieni l'ID della categoria da eliminare
if (!isset($_GET['id'])) {
    die("ID categoria non specificato.");
}

$id = (int) $_GET['id'];

// Verifica se la categoria ha sottocategorie collegate
$subcategories_query = "SELECT id FROM borghi_categorie WHERE genitore = $id";
$subcategories = $conn->query($subcategories_query);

// Variabili per la notifica di risposta
$success = false;
$message = "";

// Controlla se ci sono sottocategorie
if ($subcategories->num_rows > 0) {
    $success = false;
    $message = "Non puoi eliminare questa categoria perché ha sottocategorie collegate. Rimuovi prima le sottocategorie.";
} else {
    // Elimina la categoria
    $delete_query = "DELETE FROM borghi_categorie WHERE id = $id";

    if ($conn->query($delete_query) === TRUE) {
        $success = true;
        $message = "Categoria eliminata con successo!";
    } else {
        $success = false;
        $message = "Errore durante l'eliminazione della categoria: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elimina Categoria</title>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <style>
        /* Stile per centrare la pagina */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .content-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
    </style>
</head>
<body>

<div class="content-card">
    <h1>Eliminazione Categoria</h1>
    <p>Operazione di eliminazione categoria in corso...</p>
</div>

<script>
// Funzione per mostrare notifiche con Toastr
$(document).ready(function() {
    <?php if (isset($success)) { ?>
        <?php if ($success) { ?>
            toastr.success("<?php echo $message; ?>", "Successo", {
                closeButton: true,
                progressBar: true,
                timeOut: 5000,
                positionClass: "toast-top-right",
                showEasing: "swing",
                hideEasing: "linear",
                showMethod: "fadeIn",
                hideMethod: "fadeOut"
            });
        <?php } else { ?>
            toastr.error("<?php echo $message; ?>", "Errore", {
                closeButton: true,
                progressBar: true,
                timeOut: 5000,
                positionClass: "toast-top-right",
                showEasing: "swing",
                hideEasing: "linear",
                showMethod: "fadeIn",
                hideMethod: "fadeOut"
            });
        <?php } ?>
    <?php } ?>

    // Redirect automatico dopo 3 secondi
    setTimeout(function() {
        window.location.href = 'dashboard.php?page=categorie-borghi';
    }, 3000);
});
</script>

</body>
</html>
