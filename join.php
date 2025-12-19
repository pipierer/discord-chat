<?php
session_start();
require "core/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["invite"])) exit("Lien invalide");

$code = $_GET["invite"];
$user_id = $_SESSION["user_id"];

$stmt = $db->prepare("SELECT * FROM invites WHERE code=?");
$stmt->execute([$code]);
$invite = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invite) exit("Invitation invalide");

// vérifier déjà membre
$stmt = $db->prepare("SELECT * FROM server_members WHERE server_id=? AND user_id=?");
$stmt->execute([$invite["server_id"], $user_id]);
if (!$stmt->fetch()) {
    $stmt = $db->prepare("INSERT INTO server_members (server_id, user_id, role) VALUES (?, ?, 'member')");
    $stmt->execute([$invite["server_id"], $user_id]);
}

header("Location: server.php?id=" . $invite["server_id"]);
exit;
