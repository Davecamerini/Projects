<?php
session_start();
$email_address = $_SESSION['email'];
if (empty($email_address)) {
    header("location:../login-form.php");
}

require('../database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["main_category"])) {
    $main_category_name = $_POST["main_category"];

    // Handling cover image upload for main categories
    $cover_image_path_main = '';
    if ($_FILES["cover_image"]["error"] == 0 && is_uploaded_file($_FILES["cover_image"]["tmp_name"])) {
        $allowed_mime_types = array("image/jpeg", "image/png", "image/gif");
        $max_file_size = 5242880;  // 5 MB

        // Check if file type and extension are valid
        $cover_extension = pathinfo($_FILES["cover_image"]["name"], PATHINFO_EXTENSION);
        if (in_array($_FILES["cover_image"]["type"], $allowed_mime_types) && in_array($cover_extension, array("jpg", "jpeg", "png", "gif"))) {
            // Check file size
            if ($_FILES["cover_image"]["size"] <= $max_file_size) {
                $cover_image_folder_main = "/srv/www/vhosts/siti_dinamici/www.luciailariaseglie.it/website/area-corsi-online/admin/main_category_images/";
                $cover_image_path_main = $cover_image_folder_main . uniqid() . "_" . $_FILES["cover_image"]["name"];
                move_uploaded_file($_FILES["cover_image"]["tmp_name"], $cover_image_path_main);
            } else {
                echo "Error: Cover image size exceeds the allowed limit.";
            }
        } else {
            echo "Error: Invalid cover image type or extension.";
        }
    }

    // Replace '/srv/www/vhosts/siti_dinamici/www.luciailariaseglie.it/website' with an empty string in the cover image path
    $cover_image_path_relative_main = str_replace('/srv/www/vhosts/siti_dinamici/www.luciailariaseglie.it/website', '', $cover_image_path_main);

    // Insert data into the database for main categories
    if (!empty($main_category_name)) {
        $insert_main_category_query = "INSERT INTO video_categories (category_name, cover_image_path) VALUES ('$main_category_name', '$cover_image_path_relative_main')";
        $conn->query($insert_main_category_query);
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_main_category"])) {
    $main_category_id_to_delete = $_POST["delete_main_category"];

    // Delete main category, its subcategories, and associated cover images from the database and server
    $delete_main_category_query = "DELETE FROM video_categories WHERE id = '$main_category_id_to_delete'";
    $result = $conn->query($delete_main_category_query);

    if ($result) {
        // Delete subcategories of the main category from the database and associated cover images
        $delete_subcategories_query = "DELETE FROM video_subcategories WHERE category_id = '$main_category_id_to_delete'";
        $conn->query($delete_subcategories_query);

        echo "Categoria principale e relative sottocategorie eliminate con successo.";
        // Aggiungi codice aggiuntivo per eliminare i file dell'immagine di copertina associati alle sottocategorie, se necessario
    } else {
        echo "Errore durante l'eliminazione della categoria principale: " . $conn->error;
    }
}

$main_categories_query = "SELECT * FROM video_categories";
$main_categories_result = $conn->query($main_categories_query);

$main_categories = [];
if ($main_categories_result->num_rows > 0) {
    while ($row = $main_categories_result->fetch_assoc()) {
        $main_categories[] = [
            'category_id' => $row['id'],
            'category_name' => $row['category_name'],
            'cover_image_path' => $row['cover_image_path'],
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>Lucia Ilaria Seglie</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .header {
            background-color: #343a40;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .container {
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .btn {
            margin-top: 1.2rem;
        }

        .category-table {
            margin-top: 20px;
            width: 100%;
        }

        .category-table th,
        .category-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .category-table th {
            background-color: #007bff;
            color: #ffffff;
        }

        .category-table tbody tr:hover {
            background-color: #f5f5f5;
        }
        .legend-box {
            border: 1px solid #dee2e6;
            border-radius: 0.3rem;
            padding: 1rem;
            background-color: #f8f9fa;
        }

        .legend-content h4 {
            color: #007bff;
        }

        .legend-item {
            display: flex;
            margin-bottom: 0.5rem;
        }

        .legend-item strong {
            min-width: 1.5rem;
            text-align: center;
            margin-right: 0.5rem;
            color: #007bff;
        }
    </style>
</head>
<body>
    <?php include('menu.php'); ?>
    <div class="header">
        <div style="text-align: -webkit-center;"> <button id="showFormBtn" class="btn btn-primary" style="width: 20%;">Inserisci una nuova categoria</button></div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Form for Main Categories with Cover Image -->
                <form method="post" enctype="multipart/form-data" id="form-display"  style="display:none">
                    <div class="legend-box mt-4" style="margin-bottom:25px">
                        <div class="legend-content">
                            <h4 class="mb-3">Legenda:</h4>
                            <div class="legend-item">
                                <strong>1.</strong>
                                <div>Inserisci il titolo della categoria</div>
                            </div>
                            <div class="legend-item">
                                <strong>2.</strong>
                                <div> Seleziona carica l'immagine di copertina della categoria </div>
                            </div>
                            <div class="legend-item">
                                <strong>3.</strong>
                                <div>  Salva i dati premendo il tasto "Aggiungi categoria principale"</div>
                            </div>
                            <div class="legend-item">
                                <strong>4.</strong>
                                <div>Carica il file</div>
                            </div>
                            <div class="legend-item">
                                <strong>5.</strong>
                                <div>Clicca su Carica .</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="main_category">Nuova Categoria Principale:</label>
                        <input type="text" name="main_category" id="main_category" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="cover_image">Carica Immagine di Copertina:</label>
                        <input type="file" name="cover_image" id="cover_image" class="form-control">
                    </div>
                    <div class="form-group">
                        <input class="btn btn-primary" type="submit" value="Aggiungi Categoria Principale">
                    </div>
                </form>

                <div id="tabella-display" style="margin-top: 50px;">
                <!-- Table for Main Categories -->
                <table class="table table-striped" id="mainCategoriesTable">
                    <thead>
                        <tr>
                            <th>Categorie Principali</th>
                            <th>Immagine di Copertina</th>
                            <th>Azioni</th> 
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($main_categories as $main_category): ?>
                            <tr>
                                <td><?php echo $main_category['category_name']; ?></td>
                                <td>
                                    <?php if (!empty($main_category['cover_image_path'])): ?>
                                        <img src="<?php echo $main_category['cover_image_path']; ?>" alt="Cover Image" style="max-width: 100px;">
                                    <?php else: ?>
                                        Nessuna immagine
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="delete_main_category" value="<?php echo $main_category['category_id']; ?>">
                                        <input class="btn btn-danger" type="submit" value="Elimina">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#mainCategoriesTable').DataTable();
        });

             // Funzione per mostrare/nascondere il form
     function toggleFormVisibility() {
        var form = document.getElementById("form-display");
        var tabella = document.getElementById("tabella-display");
        var showFormBtn = document.getElementById("showFormBtn");

        // Cambia la visibilità del form
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "block";
            tabella.style.display = "none";
            showFormBtn.textContent = "Visualizza Tabella";
        } else {
            tabella.style.display = "block";
            form.style.display = "none";
            showFormBtn.textContent = "Inserisci una nuova categoria";
        }
    }

    // Aggiungi un listener per l'evento click sul bottone
    document.getElementById("showFormBtn").addEventListener("click", toggleFormVisibility);

    </script>
</body>
</html>
