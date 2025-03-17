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

// Query per recuperare i video dal database
$query = "SELECT * FROM video_categories ORDER BY id ASC";
$result = $conn->query($query);

// Verifica se ci sono video disponibili
$uploadedVideos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $uploadedVideos[] = $row;
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
            flex-wrap: wrap;
        }

        .video-item {
            margin-right: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 100%;
            height: auto;
        }

        .video-item img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .video-item .card-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .video-item .card-body .card-title {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php
    include('menu.php');
    ?>   

    <div class="header">
        <h1 class="text-center">Scopri tutti i nostri video</h1>
    </div>

    <div class="container">
        <div class="row video-list">
            <?php foreach ($uploadedVideos as $video): ?>
                <div class="col-md-4">
                    <div class="card video-item">
                        <img src="<?php echo $video['cover_image_path']; ?>" class="card-img-top" alt="Category Cover Image">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $video['category_name']; ?></h5>
                            <?php
                            $categoryId = $video['id'];
                            $dashboardLink = "sottocategoria.php?id_categoria=".$video['id'];
                            ?>
                            <a href="<?php echo $dashboardLink; ?>" class="btn btn-primary">Vai alla Categoria</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
