<?php
session_start();

require_once("../scripts/connection.php");

// Verificar si el usuario ha iniciado sesión
if (isset($_SESSION['userId'])) {
    $user_id = $_SESSION['userId'];
    $user_id = mysqli_real_escape_string($connect, $user_id);

    // Obtener los usuarios a los que sigue el usuario actual
    $sql = "SELECT u.username, u.email, u.description
            FROM users u
            JOIN follows f ON u.id = f.userToFollowId
            WHERE f.users_id = $user_id;";

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
    <title>Siguiendo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .btn-back {
            margin-bottom: 20px;
            display: inline-block;
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 5px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">Siguiendo</h1>

        <!-- Botón de volver al perfil -->
        <a href="../main/welcome.php" class="btn btn-primary btn-back">Volver a la página principal</a>

        <?php if (isset($error_message)): ?>
            <div class='alert alert-warning' role='alert'><?php echo $error_message; ?></div>
        <?php else: ?>
            <div class="row">
                <?php
                if (!$query) {
                    echo "<div class='alert alert-danger' role='alert'>Error en la consulta: " . mysqli_error($connect) . "</div>";
                } else {
                    while ($row = mysqli_fetch_assoc($query)) {
                        echo "<div class='col-md-4'>";
                        echo "<div class='card mb-4 shadow-sm'>";
                        echo "<div class='card-body'>";
                        echo "<h5 class='card-title'>" . htmlspecialchars($row['username'] ?? '') . "</h5>";
                        echo "<h6 class='card-subtitle mb-2 text-muted'>" . htmlspecialchars($row['email'] ?? '') . "</h6>";
                        echo "<p class='card-text'>" . htmlspecialchars($row['description'] ?? '') . "</p>";
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                    }
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
