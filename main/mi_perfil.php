<?php
session_start();

// Verificar sesión de usuario
if (!isset($_SESSION["userId"])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario desde la sesión
$userId = $_SESSION["userId"];

// Configuración de conexión a la base de datos
$databaseConfig = [
    'host' => 'localhost:3306',
    'user' => 'root',
    'pass' => 'root',
    'name' => 'social_network'
];

// Procesar el formulario al enviarlo
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_description"])) {
    $newDescription = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    // Conectar a la base de datos
    $conn = new mysqli($databaseConfig['host'], $databaseConfig['user'], $databaseConfig['pass'], $databaseConfig['name']);
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Actualizar la descripción del usuario
    $stmt = $conn->prepare("UPDATE users SET description = ? WHERE id = ?");
    $stmt->bind_param("si", $newDescription, $userId);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Refrescar la página para mostrar la descripción actualizada
    header("Location: mi_perfil.php");
    exit();
}

// Obtener la información del usuario incluyendo la descripción
$conn = new mysqli($databaseConfig['host'], $databaseConfig['user'], $databaseConfig['pass'], $databaseConfig['name']);
$stmt = $conn->prepare("SELECT username, email, description FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($user['username']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { background-color: #add8e6; }
        .card {
            background: #fff;
            border-radius: 30px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .card:hover { transform: scale(1.05); }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen space-y-8">
    <div class="card p-8 max-w-lg w-full">
        <h1 class="text-4xl font-bold text-blue-700 text-center mb-4">Perfil de <?php echo htmlspecialchars($user['username']); ?></h1>
        <div class="flex justify-center mb-4">
            <img src="../img/fotoPerfil.jpg" alt="Imagen de perfil" class="w-32 h-32 rounded-full border-4 border-blue-500 shadow-lg transform hover:scale-110 transition-transform duration-300">
        </div>
        <p class="text-lg text-center text-gray-800"><strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        
        <!-- Descripción del perfil -->
        <div class="mt-6">
            <h2 class="text-2xl font-bold text-center text-blue-700 mb-2">Descripción</h2>
            
            <?php if (isset($_POST['edit_description'])): ?>
                <!-- Formulario de edición de descripción -->
                <form action="mi_perfil.php" method="POST" class="text-center">
                    <textarea name="description" rows="4" class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500" placeholder="Escribe tu nueva descripción..."><?php echo htmlspecialchars($user['description'] ?? ''); ?></textarea>
                    <div class="mt-4">
                        <button type="submit" name="update_description" class="bg-green-500 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 hover:bg-green-400">Guardar</button>
                        <button type="submit" class="bg-gray-500 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 hover:bg-gray-400" name="cancel_edit">Cancelar</button>
                    </div>
                </form>
            <?php else: ?>
                <!-- Mostrar descripción -->
                <p class="text-lg text-center text-gray-700 mb-4">
                    <?php echo htmlspecialchars($user['description'] ?? "Aún no has añadido una descripción."); ?>
                </p>
                <div class="text-center">
                    <form action="mi_perfil.php" method="POST">
                        <button type="submit" name="edit_description" class="bg-yellow-500 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 hover:bg-yellow-400">Editar Descripción</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Botón para regresar -->
        <div class="text-center mt-4">
            <a href="../main/welcome.php" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 hover:bg-blue-500 focus:outline-none">Regresar a la página principal</a>
        </div>
    </div>
</body>
</html>
