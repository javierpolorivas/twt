<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["userId"])) {
    header("Location: login.php"); // Redirigir al inicio de sesión si no ha iniciado sesión
    exit();
}

$servername = "localhost:3306";
$username = "root";  
$password = "root";  
$dbname = "social_network";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION["userId"]; // Obtener el userId de la sesión
    $content = $_POST['content'];

    // Preparar la declaración SQL
    $stmt = $conn->prepare("INSERT INTO publications (userId, text, createDate) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $userId, $content);

    // Ejecutar la consulta y manejar el resultado
    if ($stmt->execute()) {
        echo "<p class='text-green-500'>Tweet enviado exitosamente.</p>";
    } else {
        echo "<p class='text-red-500'>Error al enviar el tweet: " . htmlspecialchars($stmt->error) . "</p>";
    }

    $stmt->close(); // Cerrar la declaración
}

// Consultas para obtener tweets
$userId = $_SESSION["userId"]; // Obtener el ID del usuario actual

// Tweets de personas que sigo
$sqlFollowing = "SELECT p.*, u.username, u.id AS userId FROM follows f
                   INNER JOIN publications p ON f.userToFollowId = p.userId
                   INNER JOIN users u ON p.userId = u.id
                   WHERE f.users_id = ? ORDER BY p.createDate DESC";
$stmtFollowing = $conn->prepare($sqlFollowing);
$stmtFollowing->bind_param("i", $userId);
$stmtFollowing->execute();
$resultFollowing = $stmtFollowing->get_result();

// Tweets relevantes para ti
$sqlForYou = "SELECT p.*, u.username, u.id AS userId FROM publications p INNER JOIN users u ON p.userId = u.id ORDER BY p.createDate DESC";
$stmtForYou = $conn->prepare($sqlForYou);
$stmtForYou->execute();
$resultForYou = $stmtForYou->get_result();

// Tweets del usuario
$sqlUserTweets = "SELECT text, createDate FROM publications WHERE userId = ? ORDER BY createDate DESC";
$stmtUserTweets = $conn->prepare($sqlUserTweets);
$stmtUserTweets->bind_param("i", $userId);
$stmtUserTweets->execute();
$resultUserTweets = $stmtUserTweets->get_result();

// Tweets de todos los usuarios
$sqlAllTweets = "SELECT p.*, u.username, u.id AS userId FROM publications p INNER JOIN users u ON p.userId = u.id ORDER BY p.createDate DESC";
$stmtAllTweets = $conn->prepare($sqlAllTweets);
$stmtAllTweets->execute();
$resultAllTweets = $stmtAllTweets->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Bienvenida</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc; /* Color de fondo claro */
        }
        .container {
            max-width: 1200px; /* Ancho máximo para el contenedor */
            margin: auto; /* Centrar el contenedor */
        }
        .header {
            background-color: #3b82f6; /* Color de la cabecera */
            color: white; /* Color del texto en la cabecera */
            padding: 1rem; /* Espaciado de la cabecera */
            text-align: center; /* Alinear texto al centro */
            border-radius: 0.5rem; /* Bordes redondeados */
        }
        .card {
            background: white; /* Color de fondo blanco para tarjetas */
            border-radius: 0.5rem; /* Bordes redondeados */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Sombra suave */
            padding: 1.5rem; /* Espaciado interno de las tarjetas */
            margin-bottom: 1.5rem; /* Espaciado entre tarjetas */
        }
        .card h2 {
            font-size: 1.5rem; /* Tamaño del encabezado */
            color: #1e3a8a; /* Color del encabezado */
        }
        .btn {
            background-color: #3b82f6; /* Color del botón */
            color: white; /* Color del texto del botón */
            padding: 0.75rem 1.5rem; /* Espaciado interno del botón */
            border-radius: 0.5rem; /* Bordes redondeados del botón */
            border: none; /* Sin borde */
            cursor: pointer; /* Cambia el cursor al pasar sobre el botón */
        }
        .btn:hover {
            background-color: #2563eb; /* Color del botón al pasar el ratón */
        }
    </style>
</head>

<body>
    <header class="header">
        <h1 class="text-3xl font-bold">¡Bienvenido a Twitter!</h1>
    </header>

    <div class="container">
        <div class="flex flex-col lg:flex-row">
            <aside class="w-full lg:w-1/4 p-5">
                <ul class="space-y-4">
                    <li>
                        <a href="../main/mi_perfil.php?id=<?php echo htmlspecialchars($userId); ?>" class="block text-blue-600 hover:underline">Perfil</a>
                    </li>
                    <li>
                        <a href="../main/buscar.php" class="block text-blue-600 hover:underline">Buscar Usuario</a>
                    </li>
                    <li>
                        <a href="../main/siguiendo.php" class="block text-blue-600 hover:underline">Siguiendo</a>
                    </li>
                    <li>
                        <a href="../main/seguidores.php" class="block text-blue-600 hover:underline">Seguidores</a>
                    </li>
                    <li>
                        <a href="../index.php" class="block text-blue-600 hover:underline">Logout</a>
                    </li>
                </ul>
            </aside>

            <main class="w-full lg:w-3/4 p-5">
                <section class="card">
                    <h2 class="font-bold mb-4">Envía un Nuevo Tweet</h2>
                    <form id="tweetForm" action="welcome.php" method="POST">
                        <textarea name="content" id="content" rows="4" placeholder="Escribe tu tweet aquí..." required class="w-full border border-gray-300 rounded-lg p-3 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        <button type="submit" class="btn">Tweetear</button>
                    </form>
                </section>

                      
                    </div>
                </section>

                <section class="card">
                    <h2 class="font-bold mb-4">Tweets Recientes de Personas que Sigues</h2>
                    <div class="space-y-4">
                        <?php
                        if ($resultFollowing->num_rows > 0) {
                            while ($row = $resultFollowing->fetch_assoc()) {
                                echo "<div class='p-4 border border-gray-200 rounded-lg bg-gray-50 hover:shadow-lg transition-shadow duration-200'>";
                                echo "<p class='font-semibold'><a href='../main/perfil.php?id=" . htmlspecialchars($row['userId']) . "' class='text-blue-600 hover:underline'>" . htmlspecialchars($row['username']) . "</a>:</p>";
                                echo "<p>" . htmlspecialchars($row['text']) . "</p>";
                                echo "<small class='text-gray-500'>" . htmlspecialchars($row['createDate']) . "</small>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p>No hay tweets recientes de personas que sigues.</p>";
                        }
                        ?>
                    </div>
                </
                </section>

                <section class="card">
                    <h2 class="font-bold mb-4"> Todos los Tweets</h2>
                    <div class="space-y-4">
                    <?php
                    if ($resultAllTweets->num_rows > 0) {
                        while ($row = $resultAllTweets->fetch_assoc()) {
                            echo "<div class='p-4 border border-gray-200 rounded-lg bg-gray-50 hover:shadow-lg transition-shadow duration-200'>";
                            echo "<p class='font-semibold'><a href='../main/perfil.php?id=" . htmlspecialchars($row['userId']) . "' class='text-blue-600 hover:underline'>" . htmlspecialchars($row['username']) . "</a>:</p>";
                            echo "<p>" . htmlspecialchars($row['text']) . "</p>";
                            echo "<small class='text-gray-500'>" . htmlspecialchars($row['createDate']) . "</small>";

                            // Verificar si el usuario ya dio "Me gusta" a este tweet
                            $stmtLikeCheck = $conn->prepare("SELECT * FROM likes WHERE userId = ? AND tweetId = ?");
                            $stmtLikeCheck->bind_param("ii", $userId, $row['id']);
                            $stmtLikeCheck->execute();
                            $hasLiked = $stmtLikeCheck->get_result()->num_rows > 0;
                            $stmtLikeCheck->close();

                            echo "<div class='flex items-center mt-2'>";
                            echo "<form action='like_tweet.php' method='POST'>";
                            echo "<input type='hidden' name='tweetId' value='" . htmlspecialchars($row['id']) . "'>";
                            if ($hasLiked) {
                                echo "<button type='submit' class='text-red-500 hover:text-red-700'><i class='fas fa-thumbs-down'></i> Quitar Me Gusta</button>";
                            } else {
                                echo "<button type='submit' class='text-blue-500 hover:text-blue-700'><i class='fas fa-thumbs-up'></i> Me Gusta</button>";
                            }
                            echo "</form>";
                            echo "<span class='ml-2 text-gray-600'>" . htmlspecialchars($row['likes']) . " Me gusta(s)</span>";
                            echo "</div>";

                            echo "</div>";
                        }
                    } else {
                        echo "<p>No hay tweets de otros usuarios.</p>";
                    }
                    ?>




                    </div>
                </section>

             

               
            </main>
        </div>
    </div>
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>
