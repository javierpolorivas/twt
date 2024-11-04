<?php
session_start();
require_once("../scripts/connection.php"); // Asegúrate de tener un archivo de conexión a la BD

if (!isset($_SESSION["userId"])) {
    header("Location: login.php");
    exit();
}

$currentUserId = $_SESSION["userId"];
$userToFollowId = filter_input(INPUT_POST, 'userToFollowId', FILTER_VALIDATE_INT);

if (!$userToFollowId) {
    die("ID de usuario no válido.");
}

// Verificar si el usuario ya sigue a la persona
$stmtCheckFollow = $connect->prepare("SELECT * FROM follows WHERE users_id = ? AND userToFollowId = ?");
$stmtCheckFollow->bind_param("ii", $currentUserId, $userToFollowId);
$stmtCheckFollow->execute();
$isFollowing = $stmtCheckFollow->get_result()->num_rows > 0;
$stmtCheckFollow->close();

// Ejecutar la acción de seguir o dejar de seguir
if ($isFollowing) {
    // Dejar de seguir al usuario
    $stmtUnfollow = $connect->prepare("DELETE FROM follows WHERE users_id = ? AND userToFollowId = ?");
    $stmtUnfollow->bind_param("ii", $currentUserId, $userToFollowId);
    $stmtUnfollow->execute();
    $stmtUnfollow->close();
} else {
    // Seguir al usuario
    $stmtFollow = $connect->prepare("INSERT INTO follows (users_id, userToFollowId) VALUES (?, ?)");
    $stmtFollow->bind_param("ii", $currentUserId, $userToFollowId);
    $stmtFollow->execute();
    $stmtFollow->close();
}

// Redirigir de nuevo al perfil
header("Location: perfil.php?id=" . $userToFollowId);
exit();
