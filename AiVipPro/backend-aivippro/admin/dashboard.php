<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lucia Ilaria Seglie</title>
    <!-- Includi il tuo stile Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<style>
    .btn-primary{
    width: 90% !important;
    height: 200px;
    font-size : 40px !important;
    }
</style>
    </head>

<body>

<?php
    include('menu.php');
    ?>   

    <!-- Bottoni rettangolari -->
    <div class="container mt-4">
        <div class="row">
            <!-- Bottone Inserimento video -->
            <div class="col-md-4">
                <a href="/area-corsi-online/admin/video.php" class="btn btn-primary btn-lg btn-block">Inserimento
                    video</a>
            </div>
            <!-- Bottone Categorie -->
            <div class="col-md-4">
                <a href="/area-corsi-online/admin/categorie.php" class="btn btn-primary btn-lg btn-block">Categorie</a>
            </div>
            <!-- Bottone Sottocategorie -->
            <div class="col-md-4">
                <a href="/area-corsi-online/admin/sottocategorie.php"
                    class="btn btn-primary btn-lg btn-block">Sottocategorie</a>
            </div>
        </div>
        <div class="row mt-4">
            <!-- Bottone Utenti -->
            <div class="col-md-6">
                <a href="/area-corsi-online/admin/utenti.php" class="btn btn-primary btn-lg btn-block">Utenti</a>
            </div>
            <!-- Bottone Logout -->
            <div class="col-md-6">
                <a href="/area-corsi-online/logout.php" class="btn btn-primary btn-lg btn-block">Logout</a>
            </div>
        </div>
    </div>

    <!-- Includi i tuoi script Bootstrap -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
        crossorigin="anonymous"></script>
</body>

</html>
