<?php
session_start();

// Verificar sesión de usuario
if (!isset($_SESSION["userId"])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario desde la URL
$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$currentUserId = $_SESSION["userId"]; // ID del usuario actual

if (!$userId) {
    die("ID de usuario no válido.");
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

// Obtener información del usuario, incluyendo la descripción
$stmt = $conn->prepare("SELECT username, email, description FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Usuario no encontrado.");
}
$user = $result->fetch_assoc();
$stmt->close();

// Verificar si el usuario actual ya sigue a este perfil
$stmtFollow = $conn->prepare("SELECT * FROM follows WHERE users_id = ? AND userToFollowId = ?");
$stmtFollow->bind_param("ii", $currentUserId, $userId);
$stmtFollow->execute();
$isFollowing = $stmtFollow->get_result()->num_rows > 0;
$stmtFollow->close();

// Consultar los tweets del usuario
$stmtTweets = $conn->prepare("SELECT text, createDate FROM publications WHERE userId = ? ORDER BY createDate DESC");
$stmtTweets->bind_param("i", $userId);
$stmtTweets->execute();
$resultTweets = $stmtTweets->get_result();
$stmtTweets->close();

// Consultar los mensajes entre el usuario actual y el perfil que está viendo
$stmtMessages = $conn->prepare("SELECT sender_id, message, create_date FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY create_date ASC");
$stmtMessages->bind_param("iiii", $currentUserId, $userId, $userId, $currentUserId);
$stmtMessages->execute();
$resultMessages = $stmtMessages->get_result();

$conn->close(); // Cerrar la conexión aquí, ya que ya no la necesitamos

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

        <!-- Mostrar la descripción del perfil -->
        <div class="mt-6 text-center">
            <h2 class="text-2xl font-bold text-blue-700 mb-2">Descripción</h2>
            <p class="text-gray-700">
                <?php
                echo $user['description'] ? htmlspecialchars($user['description']) : "Este usuario no ha añadido una descripción.";
                ?>
            </p>
        </div>

        <div class="text-center mt-4">
            <a href="../main/welcome.php" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 hover:bg-blue-500 focus:outline-none">Regresar a la página principal</a>
            <form action="follow_user.php" method="POST" class="inline-block ml-2">
                <input type="hidden" name="userToFollowId" value="<?php echo $userId; ?>">
                <button type="submit" class="bg-<?php echo $isFollowing ? 'red' : 'green'; ?>-600 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 hover:bg-<?php echo $isFollowing ? 'red' : 'green'; ?>-500 focus:outline-none">
                    <?php echo $isFollowing ? 'Dejar de seguir' : 'Seguir'; ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Sección de Tweets del Usuario -->
    <div class="card p-8 max-w-lg w-full mt-6">
        <h2 class="text-2xl font-bold text-blue-700 text-center mb-4">Tweets de <?php echo htmlspecialchars($user['username']); ?></h2>
        <div class="space-y-4">
            <?php
            if ($resultTweets->num_rows > 0) {
                while ($tweet = $resultTweets->fetch_assoc()) {
                    echo "<div class='p-4 border border-gray-200 rounded-lg bg-gray-50 hover:shadow-lg transition-shadow duration-200'>";
                    echo "<p>" . htmlspecialchars($tweet['text']) . "</p>";
                    echo "<small class='text-gray-500'>" . htmlspecialchars($tweet['createDate']) . "</small>";
                    echo "</div>";
                }
            } else {
                echo "<p class='text-center text-gray-500'>Este usuario no ha publicado ningún tweet.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Sección de Chat -->
    <div class="card p-8 max-w-lg w-full mt-6">
        <h2 class="text-2xl font-bold text-blue-700 text-center mb-4">Chat con <?php echo htmlspecialchars($user['username']); ?></h2>
        <div class="max-h-60 overflow-y-auto mb-4 border border-gray-200 p-4 bg-gray-50 rounded-lg">
            <?php
            if ($resultMessages && $resultMessages->num_rows > 0) {
                while ($message = $resultMessages->fetch_assoc()) {
                    $sender = ($message['sender_id'] == $currentUserId) ? "Yo" : htmlspecialchars($user['username']);
                    echo "<div class='mb-2'><strong>{$sender}:</strong> " . htmlspecialchars($message['message']) . " <small class='text-gray-500'>(" . htmlspecialchars($message['create_date']) . ")</small></div>";
                }
            } else {
                echo "<p class='text-center text-gray-500'>No hay mensajes en esta conversación.</p>";
            }
            ?>
        </div>
        <form action="send_message.php" method="POST" class="flex space-x-2">
            <input type="hidden" name="receiver_id" value="<?php echo $userId; ?>">
            <input type="text" name="message" placeholder="Escribe tu mensaje..." required class="flex-1 p-2 border border-gray-300 rounded-lg">
            <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 hover:bg-blue-500 focus:outline-none">Enviar</button>
        </form>
    </div>
</body>
</html>
