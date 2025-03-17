<?php

require_once('../database.php');

// Imposta il gestore del database
$db = $conn;

// Inizializza le variabili dei messaggi di errore e dei valori di input
$register = $fnameErr = $lnameErr = $emailErr = $passErr = $cpassErr = '';
$set_firstName = $set_lastName = $set_email = '';

// Estrae i dati dalla richiesta POST
extract($_POST);

// Controlla se è stato inviato il modulo
if (isset($_POST['submit'])) {

    // Validazione dei campi di input con espressioni regolari
    $validName = "/^[a-zA-Z ]*$/";
    $validEmail = "/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/";
    $uppercasePassword = "/(?=.*?[A-Z])/";
    $lowercasePassword = "/(?=.*?[a-z])/";
    $digitPassword = "/(?=.*?[0-9])/";
    $spacesPassword = "/^$|\s+/";
    $symbolPassword = "/(?=.*?[#?!@$%^&*-])/";
    $minEightPassword = "/.{8,}/";

    // Validazione del nome
    if (empty($first_name)) {
        $fnameErr = "First Name is Required";
    } else if (!preg_match($validName, $first_name)) {
        $fnameErr = "Digits are not allowed";
    } else {
        $fnameErr = true;
    }

    // Validazione del cognome
    if (empty($last_name)) {
        $lnameErr = "Last Name is required";
    } else if (!preg_match($validName, $last_name)) {
        $lnameErr = "Digit are not allowed";
    } else {
        $lnameErr = true;
    }

    // Validazione dell'indirizzo email
    if (empty($email)) {
        $emailErr = "Email is Required";
    } else if (!preg_match($validEmail, $email)) {
        $emailErr = "Invalid Email Address";
    } else {
        $emailErr = true;
    }

    // Validazione della password
    if (empty($password)) {
        $passErr = "Password is Required";
    } elseif (!preg_match($uppercasePassword, $password) || !preg_match($lowercasePassword, $password) || !preg_match($digitPassword, $password) || !preg_match($symbolPassword, $password) || !preg_match($minEightPassword, $password) || preg_match($spacesPassword, $password)) {
        $passErr = "Password must meet certain criteria";
    } else {
        $passErr = true;
    }

    // Validazione della conferma password
    if ($cpassword != $password) {
        $cpassErr = "Confirm Password does not Match";
    } else {
        $cpassErr = true;
    }

    // Validazione del ruolo
    if (isset($role) && ($role == 1 || $role == 2)) {
        $role = (int)$role; // Assicurati che il ruolo sia un intero
    } else {
        // Imposta un valore predefinito se il ruolo non è stato selezionato correttamente
        $role = 2; // Ruolo predefinito: Utente
    }

    // Controlla se tutti i campi sono validi
    if ($fnameErr == 1 && $lnameErr == 1 && $emailErr == 1 && $passErr == 1 && $cpassErr == 1) {
        $firstName = legal_input($first_name);
        $lastName  = legal_input($last_name);
        $email     = legal_input($email);
        $password  = legal_input(md5($password));

        // Verifica l'unicità dell'email
        $checkEmail = unique_email($email);
        if ($checkEmail) {
            $register = $email . " is already exist";
        } else {
            // Inserisci i dati nel database
            $register = register($firstName, $lastName, $email, $password, $role);
        }
    } else {
        // Imposta i valori di input solo se i campi sono invalidi
        $set_firstName = $first_name;
        $set_lastName  = $last_name;
        $set_email     = $email;
    }
}

// Funzione per convertire i valori di input illegali in formato legale
function legal_input($value)
{
    $value = trim($value);
    $value = stripslashes($value);
    $value = htmlspecialchars($value);
    return $value;
}

// Funzione per verificare l'unicità dell'indirizzo email nel database
function unique_email($email)
{
    global $db;
    $sql = "SELECT email FROM users WHERE email = ?";
    $query = $db->prepare($sql);
    $query->bind_param('s', $email);
    $query->execute();
    $query->store_result();

    return $query->num_rows > 0;
}

// Funzione per registrare l'utente nel database
function register($firstName, $lastName, $email, $password, $role)
{
    global $db;
    $sql = "INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)";
    $query = $db->prepare($sql);
    $query->bind_param('ssssi', $firstName, $lastName, $email, $password, $role);
    $exec = $query->execute();

    if ($exec == true) {
        return "You are registered successfully";
    } else {
        return "Error: " . $sql . "<br>" . $db->error;
    }
}

?>
