<?php
session_start();
$email_address = $_SESSION['email'];
if (empty($email_address)) {
    header("location:../login-form.php");
}

require('../database.php');

// Recupera le categorie dal database
$query = "SELECT * FROM video_categories";
$result = $conn->query($query);

// Verifica se ci sono categorie disponibili
$categories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}


// Recupera le categorie dal database 
$query = "SELECT * FROM video_subcategories";
$result = $conn->query($query);

// Verifica se ci sono categorie disponibili
$subcategories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row;
    }
}

    // Query to retrieve videos based on the user's category
$query = "SELECT * FROM videos INNER JOIN video_categories on video_categories.id = videos.category";

$result = $conn->query($query);

// Verifica se ci sono video disponibili
$uploadedVideos = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $uploadedVideos[] = $row;
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
            form {
                margin: 5% auto;
                border-radius: .3rem;
                padding: 1.3rem;
                border: #2274ac40 1px solid;
                text-align:center;
                
            }
            input {
                width: 90%;
                border: 0;
                padding: 20px;
                border-radius: 6px;
                margin-bottom: 10px;
                border: 1px solid #839af5;
            }
            .btn {
                width: 100%;
                padding: .5rem;
                border: 0;
                background: #fe6f27;
                font-size: 1.2em;
                color: #fff;
                text-shadow: 1px 1px 0px rgba(0, 0, 0, .4);
                
                margin-top: 1.2rem;
            }
            .btn:hover {
                background: #00398c;
                color: #b5b5b5;
                box-shadow: none;
            }
            .form-control {
            display: block;
            width: 100%;
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #d6d8db;
            
            background-clip: padding-box;
            border: 1px solid #72a7db;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        }
            .progress {
                background-color: #3fee8c;
                position: relative;
                margin: 20px;
                height: 1.2rem;
            }
            .progress-bar {
                background-color: #7eeed8;
                width: 100%;
                height: 1.2rem;
            }
            progress::-webkit-progress-value {
                background: #3fee8c;
            }
            progress::-webkit-progress-bar {
                background: #1e1e3c;
            }
            progress::-moz-progress-bar {
                background: #3fee8c;
            }
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
            .user-box {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
            }   
            .user-detail {
                text-align: center;
            }   
            .upload-form {
                margin-top: 20px;
                text-align: center;
            }   
            .upload-form label {
                font-size: 18px;
                color: #495057;
            }   
            .upload-form input[type="file"] {
                margin-top: 8px;
            }   
            .upload-form input[type="submit"] {
                margin-top: 12px;
                padding: 10px 20px;
                background-color: #007bff;
                color: #ffffff;
                border: none;
                cursor: pointer;
                font-size: 16px;
            }   
            .upload-message {
                color: #28a745;
                margin-top: 10px;
                font-weight: bold;
            }   
            a {
                color: #ffffff;
                text-decoration: none;
            }   
            a:hover {
                text-decoration: underline;
            }
            
        /* Stili DataTables */
        #usersTable {
            width: 100%;
            margin-top: 20px;
        }

        #usersTable th,
        #usersTable td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        #usersTable th {
            background-color: #007bff;
            color: #ffffff;
        }

        #usersTable tbody tr:hover {
            background-color: #f5f5f5;
        }
        /* Your existing styles here */

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

        .upload-form {
            margin-top: 20px;
        }

        .upload-form label {
            font-size: 18px;
            color: #495057;
        }

        .upload-form input[type="file"] {
            margin-top: 8px;
        }

        .upload-form input[type="submit"] {
            margin-top: 12px;
            font-size: 16px;
        }

        .progress {
            background-color: #3fee8c;
            position: relative;
            margin-top: 20px;
            height: 1.2rem;
        }

        .progress-bar {
            background-color: #7eeed8;
        }

        progress::-webkit-progress-value {
            background: #3fee8c;
        }

        progress::-webkit-progress-bar {
            background: #1e1e3c;
        }

        progress::-moz-progress-bar {
            background: #3fee8c;
        }

        #status {
            font-size: 1.2em;
            margin-top: 10px;
        }

        #uploaded_progress {
            margin-top: 10px;
            font-weight: bold;
        }

        #uploadedVideosTable {
            width: 100%;
            margin: 20px auto; /* Center the table */
        }

    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css" crossorigin="anonymous">
</head>
<body>
    <?php
    include('menu.php');
    ?>   

    <div class="header">
        <div style="text-align: -webkit-center;"> <button id="showFormBtn" class="btn btn-primary" style="width: 20%;">Inserisci un nuovo video</button></div>
    </div>

    <div class="container">
        <div id="form-display" style="display:none">
            <div class="row text-center">
                <div class="col-2"></div>
                <div class="col-8">
                    <form id="upload_form" enctype="multipart/form-data" method="post">
                    <div class="legend-box mt-4" style="margin-bottom:25px">
                        <div class="legend-content">
                            <h4 class="mb-3">Legenda:</h4>
                            <div class="legend-item">
                                <strong>1.</strong>
                                <div>Inserisci il titolo del video</div>
                            </div>
                            <div class="legend-item">
                                <strong>2.</strong>
                                <div> Seleziona la categoria di appartenenza </div>
                            </div>
                            <div class="legend-item">
                                <strong>3.</strong>
                                <div>  Seleziona la sottocategoria di appartenenza</div>
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
                        <!-- Aggiunta di nuovi campi -->
                        <div class="form-group">
                            <label for="video_title">Titolo del video:</label>
                            <input type="text" name="video_title" id="video_title" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="video_category">Categoria del video:</label>
                            <select name="video_category" id="video_category" class="form-select">
                                <?php
                                // Aggiungi opzioni della select per le categorie
                                foreach ($categories as $category) {
                                    echo "<option value='{$category['id']}'>{$category['category_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="video_subcategory">Sottocategoria del video:</label>
                            <select name="video_subcategory" id="video_subcategory" class="form-select">
                                <?php
                                // Aggiungi opzioni della select per le categorie
                                foreach ($subcategories as $subcategory) {
                                    echo "<option value='{$subcategory['id']}'>{$subcategory['subcategory_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group" style="padding-top:15px">
                            <input type="file" name="uploadingfile" id="uploadingfile" class="form-control">
                        </div>
                        <div class="form-group">
                            <input class="btn btn-primary" type="button" value="Carica" name="btnSubmit"
                                onclick="uploadFileHandler()">
                        </div>
                        <div class="form-group">
                            <div class="progress" id="progressDiv" style="display:none;">
                                <progress id="progressBar" value="0" max="100" style="width:100%; height: 1.2rem;"></progress>
                            </div>
                        </div>
                        <div class="form-group">
                            <h3 id="status"></h3>
                            <p id="uploaded_progress"></p>
                        </div>
                    </form>
                </div>
                <div class="col-2"></div>
            </div>
        </div>

        <div class="row video-list" id="tabella-display">
            <div class="col-12" style="margin-top: 50px;">
                <!-- Aggiungi l'ID alla tua tabella -->
                <table id="uploadedVideosTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Titolo</th>
                            <th>Categoria</th>
                            <th>Sottocategoria</th>
                            <!-- Aggiungi altre colonne se necessario -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($uploadedVideos as $video): ?>
                            <tr>
                                <td><?php echo $video['title']; ?></td>
                                <td><?php echo $video['category_name']; ?></td>
                                <td><?php echo $video['subcategory']; ?></td>
                                <!-- Aggiungi altre colonne se necessario -->
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
          <!-- Bootstrap JavaScript e script personalizzato -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>
        <script>
    function _(abc) {
        return document.getElementById(abc);
    }

    function uploadFileHandler() {
        document.getElementById('progressDiv').style.display = 'block';
        var file = _("uploadingfile").files[0];
        var videoTitle = _("video_title").value;  // Ottieni il titolo del video
        var videoCategory = _("video_category").value;  // Ottieni la categoria del video
        var videoSubcategory = _("video_subcategory").value;  // Ottieni la sottocategoria del video

        var formdata = new FormData();
        formdata.append("uploadingfile", file);
        formdata.append("video_title", videoTitle);  // Aggiungi il titolo del video ai dati del modulo
        formdata.append("video_category", videoCategory);  // Aggiungi la categoria del video ai dati del modulo
        formdata.append("video_subcategory", videoSubcategory);  // Aggiungi la sottocategoria del video ai dati del modulo

        var ajax = new XMLHttpRequest();
        ajax.upload.addEventListener("progress", progressHandler, false);
        ajax.addEventListener("load", completeHandler, false);
        ajax.addEventListener("error", errorHandler, false);
        ajax.addEventListener("abort", abortHandler, false);
        ajax.open("POST", "../upload.php");
        ajax.send(formdata);
    }

    function progressHandler(event) {
        var loaded = new Number((event.loaded / 1048576));  // Make loaded a "number" and divide bytes to get Megabytes
        var total = new Number((event.total / 1048576));  // Make total file size a "number" and divide bytes to get Megabytes
        _("uploaded_progress").innerHTML = "Uploaded " + loaded.toPrecision(5) + " Megabytes of " + total.toPrecision(5);  // String output
        var percent = (event.loaded / event.total) * 100;  // Get percentage of upload progress
        _("progressBar").value = Math.round(percent);  // Round value to solid
        _("status").innerHTML = Math.round(percent) + "% uploaded";  // String output
    }

    function completeHandler(event) {
        _("status").innerHTML = event.target.responseText;  // Build and show response text
        _("progressBar").value = 0;  // Set progress bar to 0
        document.getElementById('progressDiv').style.display = 'none';  // Hide progress bar
    }

    function errorHandler(event) {
        _("status").innerHTML = "Upload Failed";  // Switch status to upload failed
    }

    function abortHandler(event) {
        _("status").innerHTML = "Upload Aborted";  // Switch status to aborted
    }

    $(document).ready(function() {
        $('#uploadedVideosTable').DataTable({
                "order": [[0, 'desc']] // Order by the first column (ID) in descending order
            });
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
            showFormBtn.textContent = "Inserisci un nuovo video";
        }
    }

    // Aggiungi un listener per l'evento click sul bottone
    document.getElementById("showFormBtn").addEventListener("click", toggleFormVisibility);

</script>

    </body>
</html>