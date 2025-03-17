<?php
session_start();
$email_address = $_SESSION['email'];
if (empty($email_address)) {
    header("location:login-form.php");
}

require('database.php');

if ($conn->connect_error) {
    die("Connessione al database fallita: " . $conn->connect_error);
}

$userCategoryID = isset($_GET['id_categoria']) ? $_GET['id_categoria'] : '';

// Query per ottenere tutte le sottocategorie e i relativi percorsi delle immagini di copertina
$subcategoriesQuery = "SELECT * FROM video_subcategories WHERE category_id = $userCategoryID";
$subcategoriesResult = $conn->query($subcategoriesQuery);

// Verifica se ci sono sottocategorie disponibili
$subcategories = [];
if ($subcategoriesResult->num_rows > 0) {
    while ($row = $subcategoriesResult->fetch_assoc()) {
        $subcategories[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>Login Dashboard</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .header {
            background-color: #343a40;
            color: #ffffff;
            padding: 15px;
            text-align: right;
        }

        .video-list {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap; /* Allow items to wrap to the next line */
        }

        .video-item {
            margin-bottom: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .video-item a {
            display: block;
            margin-bottom: 10px;
        }

        .video-item img {
            width: 100% !important;
            height: auto !important;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            /* Imposta la larghezza fissa per rendere le immagini quadrate */
            max-width: 100%;
            width: 200px; /* Scegli la larghezza desiderata */
            height: 200px; /* Altezza uguale alla larghezza per rendere l'immagine quadrata */
            object-fit: cover; /* Mantiene l'aspetto quadrato, coprendo l'area specificata */
        }
    </style>
</head>
<body>
    <?php include('menu.php'); ?>

    <div class="header">
        <h1 class="mr-3">Benvenuto <a href="/area-corsi-online/logout.php" class="text-light">Logout ?</a></h1>
    </div>

    <div class="container">
        <div class="video-list row">
            <?php foreach ($subcategories as $subcategory): ?>
                <div class="col-md-4">
                    <div class="card video-item">
                        <img src="<?php echo $subcategory['cover_image_path']; ?>" class="card-img-top" alt="Cover Image">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $subcategory['subcategory_name']; ?></h5>
                            <?php
                            $dashboardLink = "dashboard.php?subcategoria=" . $subcategory['subcategory_name'];
                            ?>
                            <a href="<?php echo $dashboardLink; ?>" class="btn btn-outline-primary">Vai alla Sottocategoria</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
