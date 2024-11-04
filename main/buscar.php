<?php
// Incluir el archivo de conexión a la base de datos
require_once '../scripts/connection.php'; 

// Inicializar variables
$userNotFound = false;
$row = null;

// Verificar si se ha enviado el nombre de usuario
if (isset($_POST['username'])) {
    $username = $_POST['username'];
    
    // Proteger contra inyecciones SQL
    $username = mysqli_real_escape_string($connect, $username);

    // Realizar la consulta en la base de datos
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($connect, $sql);

    // Verificar si la consulta devolvió resultados
    if (!$result) {
        echo "Error en la consulta SQL: " . mysqli_error($connect) . "<br>";
        exit();
    }

    // Comprobar si se encontró el usuario
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
    } else {
        $userNotFound = true; // Usar variable para verificar si el usuario no fue encontrado
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Búsqueda de Usuario</title>
    <style>
        body {
            background-color: #e0f7fa; /* Color de fondo suave */
        }
        .container {
            max-width: 500px; /* Limitar ancho del contenedor */
        }
        .foto-perfil {
            width: 120px; /* Tamaño ajustado */
            height: 120px; /* Tamaño ajustado */
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #00796b; /* Borde verde más intenso */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .perfil-card {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding: 20px;
            text-align: center;
        }
        .perfil-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="container mx-auto mt-10 p-6 rounded-lg shadow-lg bg-white">
        <h1 class="text-center text-2xl font-bold text-gray-800 mb-6">Buscar Usuario</h1>

        <!-- Tarjeta de búsqueda -->
        <form method="post" action="" class="flex flex-col items-center space-y-4">
            <input type="text" id="username" name="username" required 
                   placeholder="Nombre de usuario" 
                   class="border border-gray-300 p-3 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-teal-500 transition duration-200">
            <button type="submit" class="bg-teal-600 text-white p-3 rounded-lg hover:bg-teal-700 transition duration-200 w-full">Buscar</button>
        </form>

        <!-- Mensaje de error o resultados -->
        <div class="mt-6">
            <?php if ($userNotFound): ?>
                <div class="bg-red-100 text-red-800 p-4 rounded mb-4" role="alert">No se encontró ningún usuario con ese nombre.</div>
            <?php elseif (isset($row)): ?>
                <div class="perfil-card mt-6">
                    <img src="../img/fotoPerfil.jpg" alt="Foto de perfil" class="foto-perfil mb-4">
                    <h2 class="font-bold text-xl"><?php echo htmlspecialchars($row['username']); ?></h2>
                    <p class="text-gray-700"><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                    <br></br>
                    <button>
                        <a href="../main/perfil.php?id=<?php echo $row['id']; ?>" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-all duration-300 hover:bg-blue-500 focus:outline-none">Ver Perfil</a>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <a href="../main/welcome.php" class="mt-4 inline-block bg-gray-300 text-gray-700 p-3 rounded-lg hover:bg-gray-400 transition duration-200">Volver</a>
    </div>
</body>
</html>
