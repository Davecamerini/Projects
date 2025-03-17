<?php
session_start();
$email_address = $_SESSION['email'];
if (empty($email_address)) {
    header("location:../login-form.php");
}

require('../database.php');
$main_categories_query = "SELECT * FROM video_categories";
$main_categories_result = $conn->query($main_categories_query);

$main_categories = [];
if ($main_categories_result->num_rows > 0) {
    while ($row = $main_categories_result->fetch_assoc()) {
        $main_categories[] = [
            'category_id' => $row['id'],
            'category_name' => $row['category_name'],
        ];
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["sub_category"])) {
    $sub_category_name = $_POST["sub_category"];
    $selected_main_category = $_POST["main_category_select"];

    // Handling cover image upload for subcategories
    $cover_image_path = '';
    if ($_FILES["cover_image"]["error"] == 0 && is_uploaded_file($_FILES["cover_image"]["tmp_name"])) {
        $allowed_mime_types = array("image/jpeg", "image/png", "image/gif");
        $max_file_size = 5242880;  // 5 MB

        // Check if file type and extension are valid
        $cover_extension = pathinfo($_FILES["cover_image"]["name"], PATHINFO_EXTENSION);
        if (in_array($_FILES["cover_image"]["type"], $allowed_mime_types) && in_array($cover_extension, array("jpg", "jpeg", "png", "gif"))) {
            // Check file size
            if ($_FILES["cover_image"]["size"] <= $max_file_size) {
                $cover_image_folder = "/srv/www/vhosts/siti_dinamici/www.luciailariaseglie.it/website/area-corsi-online/admin/sub_category_images/";
                $cover_image_path = $cover_image_folder . uniqid() . "_" . $_FILES["cover_image"]["name"];
                move_uploaded_file($_FILES["cover_image"]["tmp_name"], $cover_image_path);
            } else {
                echo "Error: Cover image size exceeds the allowed limit.";
            }
        } else {
            echo "Error: Invalid cover image type or extension.";
        }
    }

    // Replace '/srv/www/vhosts/siti_dinamici/www.luciailariaseglie.it/website' with an empty string in the cover image path
    $cover_image_path_relative = str_replace('/srv/www/vhosts/siti_dinamici/www.luciailariaseglie.it/website', '', $cover_image_path);

    // Insert data into the database for subcategories
    if (!empty($sub_category_name) && !empty($selected_main_category)) {
        $insert_sub_category_query = "INSERT INTO video_subcategories (subcategory_name, category_id, cover_image_path) VALUES ('$sub_category_name', '$selected_main_category', '$cover_image_path_relative')";
        $conn->query($insert_sub_category_query);
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_sub_category"])) {
    $sub_category_to_delete = $_POST["delete_sub_category"];

    // Delete subcategory and associated cover image from the database and server
    $delete_sub_category_query = "DELETE FROM video_subcategories WHERE subcategory_name = '$sub_category_to_delete'";
    $result = $conn->query($delete_sub_category_query);

    if ($result) {
        echo "Sottocategoria eliminata con successo.";
        // Aggiungi codice aggiuntivo per eliminare il file dell'immagine di copertina associato, se necessario
    } else {
        echo "Errore durante l'eliminazione della sottocategoria: " . $conn->error;
    }
}


$sub_categories_query = "SELECT video_subcategories.subcategory_name, video_categories.category_name, video_subcategories.cover_image_path
                        FROM video_subcategories
                        JOIN video_categories ON video_subcategories.category_id = video_categories.id";
$sub_categories_result = $conn->query($sub_categories_query);

$sub_categories = [];
if ($sub_categories_result->num_rows > 0) {
    while ($row = $sub_categories_result->fetch_assoc()) {
        $sub_categories[] = [
            'subcategory_name' => $row['subcategory_name'],
            'main_category' => $row['category_name'],
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
        .footer {
            background-color: #343a40;
            color: #ffffff;
            padding: 0px;
            text-align: center;
            position: sticky;
            bottom: 0;
            width: 100%;
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
        <div style="text-align: -webkit-center;"> <button id="showFormBtn" class="btn btn-primary" style="width: 20%;">Inserisci una nuova sottocategoria</button></div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Form for Subcategories -->
                <form method="post" enctype="multipart/form-data" id="form-display" style="display:none">
                    <div class="legend-box mt-4" style="margin-bottom:25px">
                        <div class="legend-content">
                            <h4 class="mb-3">Legenda:</h4>
                            <div class="legend-item">
                                <strong>1.</strong>
                                <div>Seleziona la categoria principale </div>
                            </div>
                            <div class="legend-item">
                                <strong>2.</strong>
                                <div>Inserisci il nome della sottocategoria  </div>
                            </div>
                            <div class="legend-item">
                                <strong>3.</strong>
                                <div>Carica l'immagine di copertina della sottocategoria</div>
                            </div>
                            <div class="legend-item">
                                <strong>4.</strong>
                                <div>Clicca il bottone "Aggiungi Sottocategoria" .</div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="main_category_select">Seleziona Categoria Principale:</label>
                        <select name="main_category_select" id="main_category_select" class="form-control">
                            <?php foreach ($main_categories as $main_category): ?>
                                <option value="<?php echo $main_category['category_id']; ?>"><?php echo $main_category['category_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sub_category">Nuova Sottocategoria:</label>
                        <input type="text" name="sub_category" id="sub_category" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="cover_image_sub">Carica Immagine di Copertina:</label>
                        <input type="file" name="cover_image" id="cover_image_sub" class="form-control">
                    </div>
                    <div class="form-group">
                        <input class="btn btn-primary" type="submit" value="Aggiungi Sottocategoria">
                    </div>
                </form>

                <div id="tabella-display" style="margin-top: 50px;">
                <!-- Table for Subcategories -->
                <table class="table table-striped" id="subCategoriesTable">
                    <thead>
                        <tr>
                            <th>Sottocategorie</th>
                            <th>Categoria Principale</th>
                            <th>Immagine di Copertina</th>
                            <th>Azioni</th> <!-- New column for actions -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sub_categories as $sub_category): ?>
                            <tr>
                                <td><?php echo $sub_category['subcategory_name']; ?></td>
                                <td><?php echo $sub_category['main_category']; ?></td>
                                <td><img src="<?php echo $sub_category['cover_image_path']; ?>" alt="Cover Image" style="max-width: 100px;"></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="delete_sub_category" value="<?php echo $sub_category['subcategory_name']; ?>">
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

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            $('#subCategoriesTable').DataTable();
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
            showFormBtn.textContent = "Inserisci una nuova sottocategoria";
        }
    }

    // Aggiungi un listener per l'evento click sul bottone
    document.getElementById("showFormBtn").addEventListener("click", toggleFormVisibility);

    </script>
    <?php include('footer.php'); ?>
</body>
</html>
