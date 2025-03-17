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

$userCategoryID = isset($_GET['subcategoria']) ? $_GET['subcategoria'] : '';

    // Query to retrieve videos based on the user's category
    $query = "SELECT * FROM videos WHERE subcategory = '$userCategoryID' ORDER BY id ASC";
    
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
        flex-wrap: wrap; /* Allow items to wrap to the next line */
    }

    .video-item {
        margin-right: 20px; /* Adjust the spacing between videos */
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%; /* Set a fixed width for each video item */
        height: auto; /* Set a fixed height for each video item */
    }

    .video-item video {
        width: 100%;
        height: auto; /* Ensure the video takes up the full height of the card */
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
</style>
</head>
<body>
    <?php
    include('menu.php');
    ?>   

    <div class="container">
        <div class="row video-list">
            <?php foreach ($uploadedVideos as $video): ?>
                <div class="col-md-4">
                    <div class="card video-item">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $video['title']; ?></h5>
                            <video class="card-img-top" controls>
                                <source src="<?php echo $video['file_path']; ?>" type="video/mp4">
                                Il tuo browser non supporta il tag video.
                            </video>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    
    
<script>
    function _(abc) {
    return document.getElementById(abc);
}
function uploadFileHandler() {
    document.getElementById('progressDiv').style.display='block';
    var file = _("uploadingfile").files[0];
    var formdata = new FormData();
    formdata.append("uploadingfile", file);
    var ajax = new XMLHttpRequest();
    ajax.upload.addEventListener("progress", progressHandler, false);
    ajax.addEventListener("load", completeHandler, false);
    ajax.addEventListener("error", errorHandler, false);
    ajax.addEventListener("abort", abortHandler, false);
    ajax.open("POST", "../upload.php");
    ajax.send(formdata);
}
function progressHandler(event) {
    var loaded = new Number((event.loaded / 1048576));//Make loaded a "number" and divide bytes to get Megabytes
    var total = new Number((event.total / 1048576));//Make total file size a "number" and divide bytes to get Megabytes
    _("uploaded_progress").innerHTML = "Uploaded " + loaded.toPrecision(5) + " Megabytes of " + total.toPrecision(5);//String output
    var percent = (event.loaded / event.total) * 100;//Get percentage of upload progress
    _("progressBar").value = Math.round(percent);//Round value to solid
    _("status").innerHTML = Math.round(percent) + "% uploaded";//String output
}
function completeHandler(event) {
    _("status").innerHTML = event.target.responseText;//Build and show response text
    _("progressBar").value = 0;//Set progress bar to 0
    document.getElementById('progressDiv').style.display = 'none';//Hide progress bar
}
function errorHandler(event) {
    _("status").innerHTML = "Upload Failed";//Switch status to upload failed
}
function abortHandler(event) {
    _("status").innerHTML = "Upload Aborted";//Switch status to aborted
}
</script>
</body>
</html>