<?php
session_start();
require "../core/db.php";

// Définir la timezone en Europe/Paris
date_default_timezone_set('Europe/Paris');

if (!isset($_SESSION['user_id'])) die("Non connecté");

$channel_id = $_GET['channel_id'] ?? null;
if (!$channel_id) die("Salon manquant");

// Vérifier accès
$stmt = $db->prepare("
    SELECT sm.* FROM server_members sm
    JOIN channels c ON c.server_id = sm.server_id
    WHERE sm.user_id=? AND c.id=?
");
$stmt->execute([$_SESSION['user_id'], $channel_id]);
if (!$stmt->fetch()) die("Accès refusé");

// Récupérer messages
$stmt = $db->prepare("
    SELECT m.*, u.username 
    FROM messages m
    JOIN users u ON u.id = m.user_id
    WHERE m.channel_id=?
    ORDER BY m.id ASC
");
$stmt->execute([$channel_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Couleurs alternées
$color1 = "#2f3136";
$color2 = "#36393f";
$toggle = true;

foreach ($messages as $m) {
    $bg = $toggle ? $color1 : $color2;
    $toggle = !$toggle;

    // Convertir la date en Europe/Paris
    $dt = new DateTime($m['created_at'], new DateTimeZone('UTC'));
    $dt->setTimezone(new DateTimeZone('Europe/Paris'));
    $time = $dt->format('H:i');

    echo "<div style='background:$bg; padding:5px; margin-bottom:3px; border-radius:5px;'>
            <b>".htmlspecialchars($m['username'])."</b> 
            <span style='color:#999;font-size:12px;'>[$time]</span><br>
            ".htmlspecialchars($m['content'])."
          </div>";
}
