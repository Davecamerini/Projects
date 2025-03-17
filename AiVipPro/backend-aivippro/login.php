<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

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

        form {
            background-color: #fcf6f5;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            padding: 20px;
            text-align: center;
            max-width: 300px;
            width: 100%;
            opacity: .98;
        }

        h1 {
            margin-bottom: 20px;
            color:#990011;

        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            background-color: #333;
            border-radius: 5px;
            color: #fff;
        }

        button {
            background-color: #990011;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #333;
        }
        #login{

        }
    </style>
</head>
<body id="login">
  <div class="div-overlay">
    <form method="post" action="/area-corsi-online/php/login.php">
        <h1>Login</h1>
        <input type="text" id="username" placeholder="Username" name="username">
        <input type="password" id="password" placeholder="Password" name="password">
        <button type="submit" name="login">Accedi</button>
        <a style="text-decoration:none;color:#333;padding:10px;font-size:15px;margin-top:10px;display: block;" href="/area-corsi-online/password_dimenticata.php">Hai dimenticato la password?</a>
    </form>
  </div>
</body>
</html>
