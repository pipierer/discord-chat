<?php
session_start();
require "core/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$server_id = $_GET["id"] ?? null;
if (!$server_id) die("Serveur manquant");

// Vérifier que l'utilisateur est membre
$stmt = $db->prepare("
    SELECT * FROM server_members
    WHERE server_id = ? AND user_id = ?
");
$stmt->execute([$server_id, $_SESSION["user_id"]]);
$membership = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$membership) die("Accès refusé");

// Owner ?
$is_owner = ($membership["role"] === "owner");

// Infos serveur
$stmt = $db->prepare("SELECT * FROM servers WHERE id=?");
$stmt->execute([$server_id]);
$server = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$server) die("Serveur introuvable");

// Salons
$stmt = $db->prepare("SELECT * FROM channels WHERE server_id=?");
$stmt->execute([$server_id]);
$channels = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Salon actif
$channel_id = $_GET["channel"] ?? ($channels[0]["id"] ?? null);

// Invitation créée ?
$invite_link = $_GET["invite"] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($server["name"]) ?></title>
<style>
body { margin:0; font-family:Arial; background:#1e1f22; color:white; display:flex; height:100vh; }
.sidebar { width:220px; background:#2b2d31; padding:10px; overflow-y:auto; }
.chat { flex:1; display:flex; flex-direction:column; }
.messages { flex:1; padding:10px; overflow-y:auto; }
input, button { padding:5px; margin-top:3px; }
.channel { display:block; padding:5px; color:white; text-decoration:none; border-radius:4px; }
.channel:hover { background:#404249; }
.invite-box { background:#1e1f22; padding:5px; margin-top:5px; border-radius:5px; }
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h3><?= htmlspecialchars($server["name"]) ?></h3>

    <?php foreach ($channels as $c): ?>
        <a class="channel"
           href="server.php?id=<?= $server_id ?>&channel=<?= $c["id"] ?>">
           # <?= htmlspecialchars($c["name"]) ?>
        </a>
    <?php endforeach; ?>

    <?php if ($is_owner): ?>
        <hr>

        <!-- Créer salon -->
        <form method="post" action="api/create_channel.php">
            <input type="hidden" name="server_id" value="<?= $server_id ?>">
            <input type="text" name="name" placeholder="Nouveau salon" required>
            <button>Créer salon</button>
        </form>

        <hr>

        <!-- Créer invitation -->
        <form method="post" action="api/create_invite.php">
            <input type="hidden" name="server_id" value="<?= $server_id ?>">
            <button>Créer une invitation</button>
        </form>

        <?php if ($invite_link): ?>
            <div class="invite-box">
                <small>Lien d'invitation :</small><br>
                <input type="text" value="<?= htmlspecialchars($invite_link) ?>" readonly style="width:100%;">
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <hr>
    <a href="index.php" style="color:#aaa;">⬅ Retour</a>
</div>

<!-- CHAT -->
<div class="chat">
    <div class="messages" id="messages"></div>

    <?php if ($channel_id): ?>
        <form id="msgForm" style="display:flex;">
            <input type="text" id="msg" style="flex:1" placeholder="Message..." autocomplete="off">
            <button>Envoyer</button>
        </form>

        <label style="padding:5px;">
            <input type="checkbox" id="autoScroll" checked>
            Scroll automatique
        </label>
    <?php else: ?>
        <p style="padding:10px;">Aucun salon</p>
    <?php endif; ?>
</div>

<script>
const channelId = <?= $channel_id ? $channel_id : "null" ?>;
const messagesDiv = document.getElementById("messages");
const autoScroll = document.getElementById("autoScroll");

function loadMessages() {
    if (!channelId) return;
    fetch("api/fetch_messages.php?channel_id=" + channelId)
        .then(r => r.text())
        .then(html => {
            messagesDiv.innerHTML = html;
            if (autoScroll.checked) {
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }
        });
}

setInterval(loadMessages, 1000);
loadMessages();

document.getElementById("msgForm")?.addEventListener("submit", e => {
    e.preventDefault();
    const msg = document.getElementById("msg");
    fetch("api/send_message.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: "channel_id=" + channelId + "&content=" + encodeURIComponent(msg.value)
    }).then(() => {
        msg.value = "";
        loadMessages();
    });
});
</script>

</body>
</html>
