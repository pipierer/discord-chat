<?php
session_start();
require "core/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Discord-like</title>
<style>
body { font-family:Arial,sans-serif; background:#1e1f22; color:#fff; }
a { color:#5865f2; text-decoration:none; }
h2,h3 { margin-top:20px; }
ul { list-style:none; padding-left:0; }
li { margin-bottom:5px; }
button { padding:5px 10px; border:none; border-radius:5px; cursor:pointer; background:#5865f2; color:#fff; }
</style>
</head>
<body>

<h1>Bienvenue <?= htmlspecialchars($_SESSION["username"]) ?> 🎉</h1>
<p>Tu es connecté.</p>

<h2>Mes serveurs</h2>
<ul>
<?php
$stmt = $db->prepare("
    SELECT s.* FROM servers s
    JOIN server_members sm ON s.id = sm.server_id
    WHERE sm.user_id = ?
");
$stmt->execute([$_SESSION["user_id"]]);
$servers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($servers as $s) {
    echo "<li><a href='server.php?id={$s['id']}'>" . htmlspecialchars($s['name']) . "</a></li>";
}
?>
</ul>

<h3>Créer un serveur</h3>
<form method="post" action="api/create_server.php">
    <input type="text" name="name" placeholder="Nom du serveur" required>
    <label>
        <input type="checkbox" name="is_public" value="1">
        Publique. Cochez la case pour que votre server soit publique sinon il sera privé !
    </label>
    <button>Créer</button>
</form>

<hr>

<h2>Serveurs publics</h2>
<ul>
<?php
$stmt = $db->prepare("SELECT * FROM servers WHERE is_public=1");
$stmt->execute();
$public_servers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($public_servers as $s) {
    // Ne pas afficher les serveurs dont l'utilisateur est déjà membre
    $stmt2 = $db->prepare("SELECT * FROM server_members WHERE server_id=? AND user_id=?");
    $stmt2->execute([$s['id'], $_SESSION['user_id']]);
    if ($stmt2->fetch()) continue;

    echo "<li>" . htmlspecialchars($s['name']) . " 
            <a href='api/join_server.php?id={$s['id']}'>Rejoindre</a>
          </li>";
}
?>
</ul>

<a href="logout.php">Se déconnecter</a>

</body>
</html>
