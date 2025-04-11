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

// Get the selected borgo ID from GET parameter
$selected_borgo = isset($_GET['borgo']) ? intval($_GET['borgo']) : 0;

// Query per ottenere lista borghi per il dropdown
$borghi_query = "SELECT id, ragione_sociale FROM borghi_scheda ORDER BY ragione_sociale";
$result_borghi = $conn->query($borghi_query);

// Query per totali aggregati
$totali_query = "
    SELECT 
        azione,
        SUM(contatore) as totale_contatore
    FROM borghi_statistiche";

// Add filter if a specific borgo is selected
if ($selected_borgo > 0) {
    $totali_query .= " WHERE id_borgo = " . $selected_borgo;
}

$totali_query .= " GROUP BY azione";
$result_totali = $conn->query($totali_query);

// Preparare array dei totali
$totali = [];
while ($row = $result_totali->fetch_assoc()) {
    $totali[$row['azione']] = $row['totale_contatore'];
}

// Get borgo name if one is selected
$borgo_name = "";
if ($selected_borgo > 0) {
    $name_query = "SELECT ragione_sociale FROM borghi_scheda WHERE id = " . $selected_borgo;
    $result_name = $conn->query($name_query);
    if ($result_name && $row = $result_name->fetch_assoc()) {
        $borgo_name = $row['ragione_sociale'];
    }
}
?>

<h2 class="mb-2">Statistiche <?php echo $selected_borgo > 0 ? "di " . htmlspecialchars($borgo_name) : "Totali"; ?></h2>

<!-- Filtro Borgo -->
<div class="mb-4">
    <form method="GET" class="form-inline">
        <input type="hidden" name="page" value="statistiche-borghi">
        <div class="form-group">
            <select name="borgo" class="form-control mr-2" onchange="this.form.submit()">
                <option value="0">Tutti i Borghi</option>
                <?php
                if ($result_borghi) {
                    while ($borgo = $result_borghi->fetch_assoc()) {
                        $selected = $selected_borgo == $borgo['id'] ? 'selected' : '';
                        echo "<option value='" . $borgo['id'] . "' " . $selected . ">" . 
                             htmlspecialchars($borgo['ragione_sociale']) . "</option>";
                    }
                }
                ?>
            </select>
        </div>
    </form>
</div>

<div class="content-card">
    <!-- Cards con totali -->
    <div class="row mt-2 mb-5">
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-header">Visualizzazioni Totali</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo isset($totali['visualizzazioni_pagina']) ? $totali['visualizzazioni_pagina'] : 0; ?></h5>
                    <p class="card-text">Visualizzazioni totali delle schede</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-header">Click Telefono</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo isset($totali['click_telefono']) ? $totali['click_telefono'] : 0; ?></h5>
                    <p class="card-text">Totale click sui numeri di telefono</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-header">Click Email</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo isset($totali['click_email']) ? $totali['click_email'] : 0; ?></h5>
                    <p class="card-text">Totale click sulle email</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-header">Click WhatsApp</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo isset($totali['click_whatsapp']) ? $totali['click_whatsapp'] : 0; ?></h5>
                    <p class="card-text">Totale click su WhatsApp</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-dark mb-3">
                <div class="card-header">Visualizzazioni Mappa</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo isset($totali['visualizzazioni_mappa']) ? $totali['visualizzazioni_mappa'] : 0; ?></h5>
                    <p class="card-text">Totale visualizzazioni della mappa</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
?>

<style>
    /* Stile del container principale */
    .dashboard-container {
        display: flex;
        min-height: 100vh;
        position: relative;
    }

    /* Barra laterale */
    .sidebar {
        width: 250px;
        background-color: #1c1c1c;
        color: white;
        display: flex;
        flex-direction: column;
        padding: 30px 20px;
        position: fixed;
        height: 100vh;
        overflow-y: auto;
    }

    /* Stile del contenuto principale */
    .main-content {
        flex-grow: 1;
        padding: 40px;
        background-color: #f3f3f3;
        margin-left: 250px; /* Same as sidebar width */
        min-height: 100vh;
    }
</style> 