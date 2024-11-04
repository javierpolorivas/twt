<?php
session_start();

// Verificar sesión de usuario
if (!isset($_SESSION["userId"])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario actual y del receptor
$currentUserId = $_SESSION["userId"];
$receiverId = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

if (!$receiverId || !$message) {
    die("Datos inválidos.");
}

// Configuración de conexión a la base de datos
$databaseConfig = [
    'host' => 'localhost:3306',
    'user' => 'root',
    'pass' => 'root',
    'name' => 'social_network'
];

// Establecer conexión
$conn = new mysqli($databaseConfig['host'], $databaseConfig['user'], $databaseConfig['pass'], $databaseConfig['name']);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Insertar el mensaje en la base de datos
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $currentUserId, $receiverId, $message);

if ($stmt->execute()) {
    // Redirigir de vuelta al perfil del usuario
    header("Location: perfil.php?id=" . $receiverId);
} else {
    echo "Error al enviar el mensaje: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
