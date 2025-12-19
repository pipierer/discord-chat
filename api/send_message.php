<?php
session_start();
require "../core/db.php";

if (!isset($_SESSION['user_id'])) die("Non connecté");

$channel_id = $_POST['channel_id'] ?? null;
$content = trim($_POST['content'] ?? "");

if (!$channel_id || $content === "") die("Paramètres manquants");

// Vérifier que l'utilisateur a accès au salon
$stmt = $db->prepare("
    SELECT sm.* FROM server_members sm
    JOIN channels c ON c.server_id = sm.server_id
    WHERE sm.user_id=? AND c.id=?
");
$stmt->execute([$_SESSION['user_id'], $channel_id]);
if (!$stmt->fetch()) die("Accès refusé");

// Insérer le message
$stmt = $db->prepare("INSERT INTO messages (channel_id, user_id, content) VALUES (?,?,?)");
$stmt->execute([$channel_id, $_SESSION['user_id'], $content]);
