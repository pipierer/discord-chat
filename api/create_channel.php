<?php
session_start();
require "../core/db.php";

if (!isset($_SESSION['user_id'])) die("Non connecté");

$server_id = $_POST['server_id'] ?? null;
$name = trim($_POST['name'] ?? '');
if (!$server_id || $name === '') die("Données manquantes");

// Vérifier que l'utilisateur est le propriétaire du serveur
$stmt = $db->prepare("SELECT * FROM server_members WHERE server_id=? AND user_id=? AND role='owner'");
$stmt->execute([$server_id, $_SESSION['user_id']]);
if (!$stmt->fetch()) die("Accès refusé : seulement le propriétaire peut créer un salon");

// Créer le salon
$stmt = $db->prepare("INSERT INTO channels (server_id, name) VALUES (?, ?)");
$stmt->execute([$server_id, $name]);

// Rediriger vers le serveur
header("Location: ../server.php?id=".$server_id);
exit;
