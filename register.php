<?php
session_start();
require "core/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "Tous les champs sont obligatoires";
    } elseif (strlen($username) < 3) {
        $error = "Pseudo trop court (min 3 caractères)";
    } elseif (strlen($password) < 4) {
        $error = "Mot de passe trop court (min 4 caractères)";
    } else {
        // Vérifier si le pseudo existe déjà
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $error = "Ce pseudo existe déjà";
        } else {
            // Créer le compte
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hash]);

            $_SESSION["user_id"] = $db->lastInsertId();
            $_SESSION["username"] = $username;

            header("Location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <style>
        body {
            background:#1e1f22;
            color:#fff;
            font-family: Arial;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }
        form {
            background:#2b2d31;
            padding:20px;
            border-radius:8px;
            width:300px;
        }
        input {
            width:100%;
            padding:10px;
            margin:8px 0;
            border:none;
            border-radius:5px;
        }
        button {
            width:100%;
            padding:10px;
            background:#5865f2;
            color:white;
            border:none;
            border-radius:5px;
            cursor:pointer;
        }
        .error {
            color:#ff6b6b;
            margin-bottom:10px;
        }
    </style>
</head>
<body>

<form method="post">
    <h2>Inscription</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <input type="text" name="username" placeholder="Pseudo">
    <input type="password" name="password" placeholder="Mot de passe">

    <button>S'inscrire</button>

    <p style="margin-top:10px;font-size:14px">
        Déjà un compte ? <a href="login.php" style="color:#5865f2">Connexion</a>
    </p>
</form>

</body>
</html>
