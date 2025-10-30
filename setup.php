<?php
// setup.php - Environment setup wizard
// Collect DB credentials and write config.php for the app.

// Basic guard: allow running when not logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

function write_config($server, $username, $password, $dbname) {
    $config = "<?php\n" .
        "define('DB_SERVER', '" . addslashes($server) . "');\n" .
        "define('DB_USERNAME', '" . addslashes($username) . "');\n" .
        "define('DB_PASSWORD', '" . addslashes($password) . "');\n" .
        "define('DB_NAME', '" . addslashes($dbname) . "');\n";
    return file_put_contents(__DIR__ . '/config.php', $config) !== false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $server = trim($_POST['server'] ?? 'localhost');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $dbname = trim($_POST['dbname'] ?? '');

    if (empty($username) || empty($dbname)) {
        $error = 'Please provide at least Username and Database Name.';
    } else {
        // Test connection
        $mysqli = @new mysqli($server, $username, $password, $dbname);
        if ($mysqli && !$mysqli->connect_error) {
            if (write_config($server, $username, $password, $dbname)) {
                $success = 'Configuration saved! You can now log in.';
            } else {
                $error = 'Failed to write config.php. Check file permissions.';
            }
            $mysqli->close();
        } else {
            $error = 'Connection failed: ' . ($mysqli ? $mysqli->connect_error : 'Unknown error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Wizard - Waste Management Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script>
        tailwind.config = { darkMode: 'class' };
    </script>
    <style> body { font-family: Inter, sans-serif; } </style>
}</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-xl bg-gray-800/60 border border-gray-700 rounded-xl p-6">
            <h1 class="text-2xl font-bold mb-2">Setup Wizard</h1>
            <p class="text-gray-400 mb-6">Enter your MySQL credentials to configure the app. This works whether deployed in your domain root or any subfolder on cPanel/XAMPP.</p>

            <?php if (!empty($error)): ?>
              <div class="mb-4 p-3 rounded bg-red-500/20 border border-red-500 text-red-300"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
              <div class="mb-4 p-3 rounded bg-green-500/20 border border-green-500 text-green-300">
                <?= htmlspecialchars($success) ?>
                <div class="mt-2 text-sm"><a class="underline" href="login.php">Go to Login</a></div>
              </div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-300 mb-1">Server</label>
                    <input type="text" name="server" value="<?= htmlspecialchars($_POST['server'] ?? 'localhost') ?>" class="w-full px-3 py-2 rounded bg-gray-900 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500" />
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-1">Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" class="w-full px-3 py-2 rounded bg-gray-900 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500" />
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-1">Password</label>
                    <input type="password" name="password" value="<?= htmlspecialchars($_POST['password'] ?? '') ?>" class="w-full px-3 py-2 rounded bg-gray-900 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500" />
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-1">Database Name</label>
                    <input type="text" name="dbname" value="<?= htmlspecialchars($_POST['dbname'] ?? '') ?>" class="w-full px-3 py-2 rounded bg-gray-900 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500" />
                </div>

                <button type="submit" class="mt-4 inline-flex items-center px-4 py-2 rounded bg-cyan-600 hover:bg-cyan-500 text-white">
                    <span class="material-symbols-outlined mr-2">settings</span>
                    Save & Test
                </button>
            </form>

            <div class="mt-6 text-gray-400 text-sm">
                <p>After saving, a <code>config.php</code> file is created beside <code>db.php</code>. Keep it out of version control.</p>
            </div>
        </div>
    </div>
</body>
</html>