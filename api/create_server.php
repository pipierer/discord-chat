<?php
session_start();
require "../core/db.php";

if (!isset($_SESSION["user_id"])) {
    exit("Non connecté");
}

// Récupérer le nom et le statut public
$name = trim($_POST["name"] ?? "");
$is_public = isset($_POST["is_public"]) ? 1 : 0;

if ($name === "") {
    exit("Nom du serveur obligatoire");
}

$owner_id = $_SESSION["user_id"];

// Créer le serveur
$stmt = $db->prepare("INSERT INTO servers (name, owner_id, is_public) VALUES (?, ?, ?)");
$stmt->execute([$name, $owner_id, $is_public]);

$server_id = $db->lastInsertId();

// Ajouter le propriétaire comme membre
$stmt2 = $db->prepare("INSERT INTO server_members (server_id, user_id, role) VALUES (?, ?, 'owner')");
$stmt2->execute([$server_id, $owner_id]);

// Redirection vers le serveur
header("Location: ../server.php?id=$server_id");
exit;
