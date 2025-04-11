<?php
// Includi WordPress
require_once('../../wp-load.php'); // Cambia questo percorso secondo la tua installazione di WordPress

// Controlla se l'utente è loggato e se è amministratore
if (!is_user_logged_in() || !current_user_can('administrator')) {
    wp_redirect(wp_login_url());
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Fornitori</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Stile generale */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        /* Stile del container principale */
        .dashboard-container {
            display: flex;
            height: 100vh;
        }

        /* Barra laterale */
        .sidebar {
            width: 250px;
            background-color: #1c1c1c;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 30px 20px;
        }
        .sidebar a {
            color: white;
            padding: 10px 0;
            text-decoration: none;
            font-size: 18px;
            border-bottom: 1px solid #444;
            margin-bottom: 10px;
        }
        .sidebar a:hover {
            color: #c0c0c0;
            text-decoration: none;
            transition: color 0.3s ease-in-out;
        }

        /* Stile del contenuto principale */
        .main-content {
            flex-grow: 1;
            padding: 40px;
            background-color: #f3f3f3;
            overflow-y: auto;
        }
        h1 {
            color: #444;
        }

        /* Stile per i contenuti della sezione fornitori/categorie */
        .content-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <!-- Container principale della dashboard -->
    <div class="dashboard-container">
        <!-- Barra laterale -->
        <?php include "partial/sidebar.php"; ?>

        <!-- Contenuto principale -->
        <div class="main-content">
            <?php
            // Determina quale pagina caricare in base al parametro 'page' nell'URL
            if (isset($_GET['page'])) {
                switch ($_GET['page']) {
                    case 'fornitori':
                        include 'partial/view-fornitori.php';
                        break;
                    case 'categorie':
                        include 'partial/view-categorie.php';
                        break;
                    case 'borghi':
                        include 'partial/view-borghi.php';
                        break;
                    case 'categorie-borghi':
                        include 'partial/view-categorie-borghi.php';
                        break;
                    case 'statistiche':
                        include 'partial/view-statistiche.php';
                        break;
                    case 'statistiche-borghi':
                        include 'partial/view-statistiche-borghi.php';
                        break;
                    default:
                        include 'partial/view-default.php';
                }
            } else {
                include 'partial/view-default.php';
            }
            ?>
        </div>
    </div>

    <!-- Script per Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome per le icone -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</body>
</html>
