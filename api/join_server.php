<?php
session_start();
require "../core/db.php";

if (!isset($_SESSION['user_id'])) die("Non connecté");

$server_id = $_GET['id'] ?? null;
if (!$server_id) die("Serveur manquant");

// Vérifier si l'utilisateur n'est pas déjà membre
$stmt = $db->prepare("SELECT * FROM server_members WHERE server_id=? AND user_id=?");
$stmt->execute([$server_id, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    // Ajouter l'utilisateur
    $stmt = $db->prepare("INSERT INTO server_members (server_id, user_id, role) VALUES (?, ?, ?)");
    $stmt->execute([$server_id, $_SESSION['user_id'], 'member']);
}

// Rediriger vers le serveur
header("Location: ../server.php?id=".$server_id);
exit;
