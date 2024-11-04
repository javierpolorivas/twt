<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Página de Inicio de Sesión</title>
    <style>
        body {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-lg rounded-lg p-10 w-96 text-center">
        <h1 class="text-2xl font-bold mb-6">Iniciar Sesión</h1>
        <form action="scripts/login.php" method="POST">
            <div class="mb-4">
                <input type="text" name="username" placeholder="Nombre de usuario" required class="w-full py-2 px-3 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <input type="password" name="password" placeholder="Contraseña" required class="w-full py-2 px-3 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition duration-300">Iniciar Sesión</button>
        </form>

        <p class="mt-4 text-gray-600">¿No tienes una cuenta? <a href="scripts/registro.php" class="text-blue-500 hover:underline">Regístrate</a></p>
    </div>
</body>
</html>
