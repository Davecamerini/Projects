<?php
session_start();
require('database.php');

$email_address = $_SESSION['email'];

if (empty($email_address)) {
    header("location: login-form.php");
    exit();
}

$update_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user'])) {
        handleUserUpdate();
    } elseif (isset($_POST['disable_account'])) {
        handleAccountDisable();
    }
}

$logged_user = getUserByEmail($conn, $email_address);

if (!$logged_user) {
    header("location: login-form.php");
    exit();
}

function handleUserUpdate() {
    global $conn, $email_address, $update_message;

    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $new_password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['cpassword']);

    if ($new_password == $confirm_password) {
        $hashed_password = md5($new_password);

        $update_query = "UPDATE users SET first_name='$first_name', last_name='$last_name', password='$hashed_password' WHERE email='$email_address'";

        if (mysqli_query($conn, $update_query)) {
            $logged_user = getUserByEmail($conn, $email_address);
            $update_message = 'Aggiornamento riuscito';
        } else {
            $update_message = 'Errore durante l\'aggiornamento nel database';
        }
    } else {
        $update_message = 'La nuova password e la conferma non coincidono';
    }
}

function handleAccountDisable() {
    global $conn, $email_address, $update_message;

    $data_disattivazione = date("Y-m-d H:i:s");
    $logged_user = getUserByEmail($conn, $email_address);

    if ($logged_user) {
        $disabilita_account_query = "UPDATE users SET attivazione='0' WHERE email='$email_address'";

        if (mysqli_query($conn, $disabilita_account_query)) {
            $insert_disattivazione_query = "INSERT INTO disattivazioni (user_id, data_disattivazione) VALUES ('{$logged_user['id']}', '$data_disattivazione')";

            if (mysqli_query($conn, $insert_disattivazione_query)) {
                session_destroy();
                header("location: login-form.php?success=account_disabled");
                exit();
            } else {
                $update_message = 'Errore durante la disattivazione dell\'account';
            }
        } else {
            $update_message = 'Errore durante la disattivazione dell\'account';
        }
    } else {
        header("location: login-form.php");
        exit();
    }
}

function getUserByEmail($conn, $email) {
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        return $result->fetch_assoc();
    }

    return null;
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <title>Modifica Utente - Lucia Ilaria Seglie</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Aggiungi il tuo stile Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"         integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <script>
        function confirmDisableAccount() {
            return confirm('Sei sicuro di voler disabilitare l\'account?');
        }
    </script>

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

        .btn-save,
        .btn-disable {
            width: 100%;
        }
    </style>
</head>

<body>
    <?php include('menu.php'); ?>
    <div class="header">
        <h3 class="text-center">Modifica il tuo account</h3>
    </div>
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-6">
            <form method="post" onsubmit="return confirm('Sei sicuro di voler disabilitare l\'account?');" style="text-align-last: center;">
                <button style="width: 30%;" type="submit" class="btn btn-danger btn-disable" name="disable_account">Disabilita Account</button>
            </form>

                <!-- Modulo di modifica utente -->
                <div class="edit-user-form">
                    <form method="post">
                        <!-- Aggiungi il messaggio di successo o errore qui -->
                        <?php if (!empty($update_message)): ?>
                            <div class="alert <?php echo ($update_message === 'Aggiornamento riuscito' || $update_message === 'Disattivazione riuscita') ? 'alert-success' : 'alert-danger'; ?>" role="alert">
                                <?php echo $update_message; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Nome -->
                        <div class="form-group">
                            <label for="first_name">Nome:</label>
                            <input type="text" class="form-control" name="first_name"
                                value="<?php echo $logged_user['first_name']; ?>" required>
                        </div>

                        <!-- Cognome -->
                        <div class="form-group">
                            <label for="last_name">Cognome:</label>
                            <input type="text" class="form-control" name="last_name"
                                value="<?php echo $logged_user['last_name']; ?>" required>
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" name="email"
                                value="<?php echo $logged_user['email']; ?>" readonly>
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label for="password">Nuova Password:</label>
                            <input type="password" class="form-control" name="password">
                        </div>

                        <!-- Conferma Password -->
                        <div class="form-group">
                            <label for="cpassword">Conferma Nuova Password:</label>
                            <input type="password" class="form-control" name="cpassword">
                        </div>

                        <!-- Bottone Salva Modifiche -->
                        <button type="submit" class="btn btn-primary btn-save" name="update_user">Salva Modifiche</button>

                     </form>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>
    <!-- Aggiungi i tuoi script Bootstrap -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
