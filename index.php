<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Waste Management Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-gray-900 text-gray-200 font-sans flex items-center justify-center min-h-screen">
    <div class="text-center p-8">
        <h1 class="text-5xl font-bold text-white mb-4">
            Waste Management Portal
        </h1>
        <p class="text-lg text-gray-400 mb-8">
            Efficiently manage customer data, billing, and payments for your waste management operations.
        </p>
        <div class="flex justify-center gap-4">
            <a href="login.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg text-lg">
                Portal Login
            </a>
            <a href="about.php" class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg text-lg">
                Learn More
            </a>
        </div>
    </div>
</body>
</html>
