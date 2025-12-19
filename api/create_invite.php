<?php
session_start();
require "../core/db.php";

if (!isset($_SESSION["user_id"])) exit("Non connecté");

$server_id = (int)$_POST["server_id"];
$user_id = $_SESSION["user_id"];

// vérifier que c’est le owner
$stmt = $db->prepare("SELECT * FROM servers WHERE id=? AND owner_id=?");
$stmt->execute([$server_id, $user_id]);
if (!$stmt->fetch()) exit("Pas autorisé");

// générer code
$code = bin2hex(random_bytes(6));

$stmt = $db->prepare("INSERT INTO invites (server_id, code, created_by) VALUES (?, ?, ?)");
$stmt->execute([$server_id, $code, $user_id]);

echo "Lien d'invitation : http://localhost:8000/join.php?invite=$code";
