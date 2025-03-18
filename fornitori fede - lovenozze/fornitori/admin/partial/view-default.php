<?php
// Connetti al database
$servername = "db16.webme.it";
$username = "sitidi_759";
$password = "c2F1K5cd08442336";
$dbname = "sitidi_759";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// Query per fornitori totali
$fornitori_query = "SELECT COUNT(*) as total FROM fornitori_scheda";
$result_fornitori = $conn->query($fornitori_query);
$total_fornitori = $result_fornitori->fetch_assoc()['total'];

// Query per categorie totali
$categorie_query = "SELECT COUNT(*) as total FROM fornitori_categorie";
$result_categorie = $conn->query($categorie_query);
$total_categorie = $result_categorie->fetch_assoc()['total'];

// Query per borghi totali
$borghi_query = "SELECT COUNT(*) as total FROM borghi_scheda";
$result_borghi = $conn->query($borghi_query);
$total_borghi = $result_borghi->fetch_assoc()['total'];

// Inserisci i dati nelle card
?>

<h2 class="mb-2">Panoramica Statistiche</h2>
<div class="content-card">
  <h4 class="mt-2">Statistiche Fornitori</h4>
        <hr class="mb-2 pb-2" />
    <div class="row mt-2 mb-5">
        <!-- Card Fornitori Totali -->
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-header">Fornitori Totali</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_fornitori; ?></h5> <!-- Inserisci qui il numero totale di fornitori -->
                    <p class="card-text">Fornitori attualmente registrati.</p>
                </div>
            </div>
        </div>
        <!-- Card Borghi Totali -->
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-header">Borghi Totali</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_borghi; ?></h5>
                    <p class="card-text">Borghi attualmente registrati.</p>
                </div>
            </div>
        </div>
        <!-- Card Categorie Totali -->
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-header">Categorie Totali</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $total_categorie; ?></h5> <!-- Inserisci qui il numero totale di categorie -->
                    <p class="card-text">Categorie fornitore disponibili.</p>
                </div>
            </div>
        </div>
        <!-- Card Interazioni -->
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-header">Interazioni Totali</div>
                <div class="card-body">
                    <h5 class="card-title">0</h5> <!-- Inserisci qui il numero di interazioni -->
                    <p class="card-text">Interazioni recenti.</p>
                </div>
            </div>
        </div>
        <!-- Card Visite -->
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-header">Visite Totali</div>
                <div class="card-body">
                    <h5 class="card-title">0</h5> <!-- Inserisci qui il numero di visite -->
                    <p class="card-text">Visite totali schede.</p>
                </div>
            </div>
        </div>
    </div>
</div>
