<?php
require_once('../../wp-load.php');

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


// Variabili per la notifica di risposta
$success = false;
$message = "";

// Controlla se è stato fornito l'ID del borgo da eliminare
if (isset($_GET['id'])) {
    $borgo_id = intval($_GET['id']);

    // Query per eliminare il borgo
    $delete_query = "DELETE FROM borghi_scheda WHERE id=$borgo_id";

    if ($conn->query($delete_query)) {
        $success = true;
        $message = "borgo eliminato con successo!";
    } else {
        $success = false;
        $message = "Errore durante l'eliminazione del borgo: " . $conn->error;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elimina borgo</title>

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
    <h1>Eliminazione borgo</h1>
    <p>Operazione di eliminazione borgo in corso...</p>
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
        window.location.href = 'dashboard.php?page=borghi';
    }, 3000);
});
</script>

</body>
</html>
