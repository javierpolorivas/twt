<?php
session_start();
require_once("../scripts/connection.php");

// Verificar si el usuario ha iniciado sesión
if (isset($_SESSION['userId'])) {
    $user_id = $_SESSION['userId'];
    $user_id = mysqli_real_escape_string($connect, $user_id);

    // Obtener los usuarios que siguen al usuario actual (seguidores)
    $sql = "SELECT u.username, u.email, u.description
            FROM users u
            JOIN follows f ON u.id = f.users_id
            WHERE f.userToFollowId = $user_id;";

    $query = mysqli_query($connect, $sql);
} else {
    $error_message = "No estás autenticado. Por favor inicia sesión.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguidores</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f4f8; /* Fondo claro */
        }
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-radius: 10px; /* Bordes redondeados */
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .card-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container mx-auto mt-10 p-5">
        <h1 class="text-center text-3xl font-bold text-gray-800 mb-6">Seguidores</h1>

        <!-- Botón de volver al perfil -->
        <a href="../main/welcome.php" class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded-md shadow-md hover:bg-blue-700 transition duration-200">Volver a la página principal</a>

        <?php if (isset($error_message)): ?>
            <div class="bg-yellow-200 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php
                if (!$query) {
                    echo "<div class='bg-red-200 border-l-4 border-red-500 text-red-700 p-4' role='alert'>Error en la consulta: " . mysqli_error($connect) . "</div>";
                } else {
                    while ($row = mysqli_fetch_assoc($query)) {
                        echo "<div class='card'>";
                        echo "<div class='card-content'>";
                        echo "<h5 class='text-xl font-semibold text-gray-800'>" . htmlspecialchars($row['username']) . "</h5>";
                        echo "<h6 class='text-gray-600'>" . htmlspecialchars($row['email']) . "</h6>";
                        echo "</div>";
                        echo "</div>";
                    }
                }
                ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Efecto de aparición de tarjetas
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.transitionDelay = `${index * 100}ms`;
            card.classList.add('opacity-0');
            setTimeout(() => {
                card.classList.remove('opacity-0');
                card.classList.add('opacity-100');
            }, index * 100);
        });
    </script>
</body>
</html>
