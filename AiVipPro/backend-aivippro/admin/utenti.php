<?php
session_start();
$email_address = $_SESSION['email'];
if (empty($email_address)) {
    header("location:../login-form.php");
}

require('../database.php');
require('script.php');

// Recupera i dati degli utenti dal database
$query = "SELECT * FROM users";
$result = $conn->query($query);

// Verifica se ci sono utenti disponibili
$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
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

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
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
        <div style="text-align: -webkit-center;"> <button id="showFormBtn" class="btn btn-primary" style="width: 20%;">Inserisci un nuovo utente</button></div>
    </div>
    <div class="container">
    <div class="row justify-content-md-center">
        <div class="col-md-6">
            
            <!-- Modulo di registrazione -->
            <div class="registration-form">
                <h4 class="text-center">Crea un Nuovo Account</h4>
                <p class="text-success text-center"><?php echo $register; ?></p>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" id="form-display" style="display:none">
                <div class="legend-box mt-4" style="margin-bottom:25px">
                        <div class="legend-content">
                            <h4 class="mb-3">Legenda:</h4>
                            <div class="legend-item">
                                <strong>1.</strong>
                                <div>Compila tutti i dati relativi all'utente</div>
                            </div>
                            <div class="legend-item">
                                <strong>2.</strong>
                                <div> Premi il bottone "Registra utente"</div>
                            </div>
                        </div>
                    </div>
                    <!-- Nome -->
                    <div class="form-group">
                        <label for="email">Nome</label>
                        <input type="text" class="form-control" placeholder="Inserisci il Nome" name="first_name"
                            value="<?php echo $set_firstName; ?>">
                        <p class="err-msg"><?php if ($fnameErr != 1) { echo $fnameErr; } ?></p>
                    </div>

                    <!-- Cognome -->
                    <div class="form-group">
                        <label for="email">Cognome</label>
                        <input type="text" class="form-control" placeholder="Inserisci il Cognome" name="last_name"
                            value="<?php echo $set_lastName; ?>">
                        <p class="err-msg"><?php if ($lnameErr != 1) { echo $lnameErr; } ?></p>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="text" class="form-control" id="email" placeholder="Inserisci l'indirizzo email"
                            name="email" value="<?php echo $set_email; ?>">
                        <p class="err-msg"><?php if ($emailErr != 1) { echo $emailErr; } ?></p>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="pwd">Password:</label>
                        <input type="password" class="form-control" placeholder="Inserisci la password"
                            name="password">
                        <p class="err-msg"><?php if ($passErr != 1) { echo $passErr; } ?></p>
                    </div>

                    <!-- Conferma Password -->
                    <div class="form-group">
                        <label for="pwd">Conferma Password:</label>
                        <input type="password" class="form-control" placeholder="Inserisci conferma password"
                            name="cpassword">
                        <p class="err-msg"><?php if ($cpassErr != 1) { echo $cpassErr; } ?></p>
                    </div>

                    <div class="form-group" style=" margin-bottom: 20px;">
                        <label for="role">Ruolo:</label>
                        <select class="form-control" name="role">
                            <option value="1">Amministratore</option>
                            <option value="2">Utente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" name="submit">Registra utente</button>
                    </div>
                   
                </form>
            </div>
        </div>
    </div>
        <div class="row" id="tabella-utenti">
            <div class="col-12">
                <!-- DataTable per visualizzare i dati degli utenti -->
                <table id="usersTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Cognome</th>
                            <th>Email</th>
                            <th>Ruolo</th>
                            <th>Attivazione</th> <!-- Nuova colonna per l'attivazione -->
                            <!-- Aggiungi altre colonne se necessario -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($users as $user) {
                            echo "<tr>";
                            echo "<td>{$user['id']}</td>";
                            echo "<td>{$user['first_name']}</td>";
                            echo "<td>{$user['last_name']}</td>";
                            echo "<td>{$user['email']}</td>";

                            // Aggiungi la condizione per stampare "Admin" o "Cliente" in base al valore di 'role'
                            echo "<td>";
                            echo ($user['role'] == 1) ? "Admin" : "Cliente";
                            echo "</td>";

                            // Aggiungi la colonna di attivazione con colori
                            echo "<td class='text-center'>";
                            echo "<button class='btn btn-toggle btn-" . (($user['attivazione'] == 1) ? "success" : "danger") . "' data-id='{$user['id']}' data-status='{$user['attivazione']}'>"
                                . ($user['attivazione'] == 1 ? "Attivato" : "Disattivato") . "</button>";
                            echo "</td>";

                            // Aggiungi altre colonne se necessario
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
    <script>
        // Inizializza il DataTable
        $(document).ready(function () {
            $('#usersTable').DataTable({
                "order": [[0, 'desc']] // Order by the first column (ID) in descending order
            });

            // Gestione del toggle per l'attivazione
            $('.btn-toggle').click(function () {
                var userId = $(this).data('id');
                var currentStatus = $(this).data('status');

                // Effettua la chiamata AJAX per aggiornare lo stato di attivazione nel database
                $.ajax({
                    type: 'POST',
                    url: 'toggle_activation.php',
                    data: { userId: userId, currentStatus: currentStatus },
                    success: function (response) {
                        // Aggiorna la colonna di attivazione nella tabella
                        var newStatus = response === '1' ? 'Attivato' : 'Disattivato';
                        $('.btn-toggle[data-id="' + userId + '"]').data('status', response).text(newStatus);
                        $('.btn-toggle[data-id="' + userId + '"]').toggleClass('btn-success btn-danger');
                    },
                    error: function () {
                        alert('Errore durante l\'aggiornamento dello stato di attivazione.');
                    }
                });
            });

            function toggleFormVisibility() {
                var form = document.getElementById("form-display");
                var tabella = document.getElementById("tabella-utenti");
                var showFormBtn = document.getElementById("showFormBtn");

                // Cambia la visibilità del form
                if (form.style.display === "none" || form.style.display === "") {
                    form.style.display = "block";
                    tabella.style.display = "none";
                    showFormBtn.textContent = "Visualizza Tabella";
                } else {
                    tabella.style.display = "block";
                    form.style.display = "none";
                    showFormBtn.textContent = "Inserisci un nuovo utente";
                }
            }

            // Aggiungi un listener per l'evento click sul bottone
            document.getElementById("showFormBtn").addEventListener("click", toggleFormVisibility);

        });
    </script>
</body>

</html>
