<?php

require('database.php');
$db = $conn; // assign your connection variable

// by default, error messages are empty
$login = $emailErr = $passErr = '';

extract($_POST); 

if (isset($_POST['submit'])) {

    // input fields are validated with regular expressions
    $validName = "/^[a-zA-Z ]*$/";
    $validEmail = "/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/";

    // Email Address Validation
    if (empty($email)) {
        $emailErr = "Email is required";
    } elseif (!preg_match($validEmail, $email)) {
        $emailErr = "Invalid email address";
    } else {
        $emailErr = true;
    }

    // Password validation
    if (empty($password)) {
        $passErr = "Password is required";
    } else {
        $passErr = true;
    }

    // check if all fields are valid or not
    echo $email;
    if ($emailErr === true && $passErr === true) {

        // legal input values
        $email = legal_input($email);
        $password = legal_input(md5($password));

        // call login function
        $login = login($email, $password);
    }
}

// convert illegal input value to legal value format
function legal_input($value)
{
    $value = trim($value);
    $value = stripslashes($value);
    $value = htmlspecialchars($value);
    return $value;
}
// Function to authenticate user login
function login($email, $password)
{
    global $db;

    // Check if the email is registered
    $emailCheckSql = "SELECT email, attivazione FROM users WHERE email=?";
    $emailCheckQuery = $db->prepare($emailCheckSql);
    $emailCheckQuery->bind_param('s', $email);
    $emailCheckQuery->execute();
    $emailCheckResult = $emailCheckQuery->get_result();

    if ($emailCheckResult) {
        if ($emailCheckResult->num_rows > 0) {
            $userRow = $emailCheckResult->fetch_assoc();
            $attivazioneStatus = $userRow['attivazione'];

            if ($attivazioneStatus == 1) {
                // Email is registered and account is activated

                // Check email and password
                $loginSql = "SELECT email, password, role FROM users WHERE email=? AND password=?";
                $loginQuery = $db->prepare($loginSql);
                $loginQuery->bind_param('ss', $email, $password);
                $loginQuery->execute();
                $loginResult = $loginQuery->get_result();

                if ($loginResult) {
                    if ($loginResult->num_rows > 0) {
                        // Login successful

                        // Get user role
                        $roleSql = "SELECT role FROM users WHERE email=?";
                        $roleQuery = $db->prepare($roleSql);
                        $roleQuery->bind_param('s', $email);
                        $roleQuery->execute();
                        $roleResult = $roleQuery->get_result();

                        if ($roleResult) {
                            $roleRow = $roleResult->fetch_assoc();
                            $role = $roleRow['role'];

                            // Start user session
                            session_start();
                            $_SESSION['email'] = $email;

                            // Redirect based on user role
                            if ($role == 1) {
                                header("location:/area-corsi-online/admin/dashboard.php");
                            } else {
                                header("location:/area-corsi-online/categoria.php");
                            }
                        } else {
                            return "Error fetching user role";
                        }
                    } else {
                        return "Incorrect password";
                    }
                } else {
                    return $db->error;
                }
            } else {
                return "Account not activated";
            }
        } else {
            return "Email not registered";
        }
    } else {
        return $db->error;
    }
}

?>
