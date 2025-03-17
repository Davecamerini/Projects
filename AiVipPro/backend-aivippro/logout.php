<?php
// Inizia la sessione
session_start();

// Distrugge tutte le variabili di sessione
$_SESSION = array();

// Cancella il cookie di sessione, se presente
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie('utente_id_standard', '', time() - 3600, "/");
    setcookie('utente_id_admin', '', time() - 3600, "/");
    setcookie('uidAb', '', time() - 3600, "/");
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Distrugge la sessione
session_destroy();

// Reindirizza alla pagina di login dopo il logout
header("Location: login.php");
exit();
?>
