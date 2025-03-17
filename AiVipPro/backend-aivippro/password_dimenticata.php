<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Dimenticata - zoi yoga</title>
    <style>
    body {
      font-family: 'Open Sans', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      background:linear-gradient(0deg, rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.8)), url("/area-corsi-online/images/sfondo.jpg");
      ackground-repeat: no-repeat;
      background-position-x: center;
      background-position-y: center;
      background-size: cover;
}

.container {
    width: 100%;
    max-width: 400px;
    text-align: center;
}

.password-reset-form {
  background-color: #fcf6f5;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

h1 {
    color: #990011;
}

form {
  background-color: #fcf6f5;
  border-radius: 8px;

  padding: 20px;
  text-align: center;
  max-width: 300px;
  width: 100%;
  opacity: .8;
    display: flex;
    flex-direction: column;
    margin-top: 20px;
}

label {
    margin-bottom: 5px;
}

input {
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    background-color: #990011;
    color: #fff;
    padding: 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #333;
}

a {
    color: #990011;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
    <div class="container">
        <div class="password-reset-form">
            <h1>Password Dimenticata</h1>
            <p>Inserisci il tuo indirizzo email e ti invieremo istruzioni per il recupero della password.</p>
            <form method="post" action="/area-corsi-online/php/process_reset_password.php">
                <label for="email">Indirizzo Email:</label>
                <input type="email" name="email" required>
                <button type="submit">Invia Istruzioni</button>
            </form>
            <p><a href="login.php">Torna al login</a></p>
        </div>
    </div>
</body>
</html>
