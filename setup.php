<?php
// setup.php - Environment setup wizard
// Step 1: Collect DB credentials and write config.php (option to overwrite DB and init schema)
// Step 2: Create initial admin account

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';
$step = isset($_POST['step']) ? intval($_POST['step']) : (isset($_GET['step']) ? intval($_GET['step']) : 1);

function write_config($server, $username, $password, $dbname) {
    $config = "<?php\n" .
        "define('DB_SERVER', '" . addslashes($server) . "');\n" .
        "define('DB_USERNAME', '" . addslashes($username) . "');\n" .
        "define('DB_PASSWORD', '" . addslashes($password) . "');\n" .
        "define('DB_NAME', '" . addslashes($dbname) . "');\n";
    return file_put_contents(__DIR__ . '/config.php', $config) !== false;
}

function initialize_schema(mysqli $mysqli, bool $drop_existing = false) {
    $mysqli->set_charset('utf8mb4');
    $queries = [];
    if ($drop_existing) {
        $queries[] = "DROP TABLE IF EXISTS payments";
        $queries[] = "DROP TABLE IF EXISTS private_customers";
        $queries[] = "DROP TABLE IF EXISTS commercial_customers";
        $queries[] = "DROP TABLE IF EXISTS users";
        $queries[] = "DROP TABLE IF EXISTS dropdown_options";
        $queries[] = "DROP TABLE IF EXISTS guides";
        $queries[] = "DROP TABLE IF EXISTS global_rates";
    }

    $queries[] = "CREATE TABLE IF NOT EXISTS users (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        username VARCHAR(100) NOT NULL UNIQUE,\n        password VARCHAR(255) NOT NULL,\n        role ENUM('admin','operator') NOT NULL DEFAULT 'admin',\n        subsidiary VARCHAR(50) NOT NULL DEFAULT 'ITECSOL',\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $queries[] = "CREATE TABLE IF NOT EXISTS private_customers (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        customer_name VARCHAR(255) NOT NULL,\n        property_code VARCHAR(100) DEFAULT NULL,\n        street_address TEXT,\n        phone_number VARCHAR(40) DEFAULT NULL,\n        phone_number_whatsapp VARCHAR(40) DEFAULT NULL,\n        bill_status ENUM('paid','unpaid') DEFAULT 'unpaid',\n        compliance_status VARCHAR(50) DEFAULT NULL,\n        total_due DECIMAL(12,2) DEFAULT 0,\n        outstanding_balance DECIMAL(12,2) DEFAULT 0,\n        current_due DECIMAL(12,2) DEFAULT 0,\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $queries[] = "CREATE TABLE IF NOT EXISTS commercial_customers (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        company_name VARCHAR(255) NOT NULL,\n        company_address TEXT,\n        amount_paid DECIMAL(12,2) DEFAULT 0,\n        balance DECIMAL(12,2) DEFAULT 0,\n        is_high_value TINYINT(1) DEFAULT 0,\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $queries[] = "CREATE TABLE IF NOT EXISTS payments (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        customer_id INT NOT NULL,\n        customer_type ENUM('private','commercial') NOT NULL,\n        amount_paid DECIMAL(12,2) NOT NULL,\n        payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,\n        months_covered VARCHAR(100) DEFAULT NULL,\n        entry_by_user_id INT NOT NULL,\n        INDEX(customer_id),\n        INDEX(entry_by_user_id)\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $queries[] = "CREATE TABLE IF NOT EXISTS dropdown_options (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        category VARCHAR(50) NOT NULL,\n        value VARCHAR(255) NOT NULL,\n        display_order INT DEFAULT 0\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $queries[] = "CREATE TABLE IF NOT EXISTS guides (\n        id INT PRIMARY KEY,\n        content TEXT NOT NULL\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $queries[] = "CREATE TABLE IF NOT EXISTS global_rates (\n        id INT AUTO_INCREMENT PRIMARY KEY,\n        rate_name VARCHAR(255) NOT NULL,\n        rate_value DECIMAL(12,2) NOT NULL,\n        customer_type ENUM('private','commercial') NOT NULL,\n        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    foreach ($queries as $sql) {
        if (!$mysqli->query($sql)) {
            throw new Exception('Schema init error: ' . $mysqli->error);
        }
    }

    // Seed defaults
    $mysqli->query("INSERT IGNORE INTO guides (id, content) VALUES (1, 'Welcome to the Waste Management Portal. Use the left navigation to get started.')");

    // Seed dropdowns (basic options)
    $seed = [
        ['bin_types','240L'], ['bin_types','120L'],
        ['customer_types','Residential'], ['customer_types','Commercial'],
        ['property_types','Detached'], ['property_types','Flat'],
        ['compliance_status','Compliant'], ['compliance_status','Non-Compliant']
    ];
    $stmt = $mysqli->prepare("INSERT INTO dropdown_options (category, value, display_order) VALUES (?, ?, 0)");
    if ($stmt) {
        foreach ($seed as [$cat,$val]) {
            $stmt->bind_param('ss', $cat, $val);
            $stmt->execute();
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        $server = trim($_POST['server'] ?? 'localhost');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $dbname = trim($_POST['dbname'] ?? '');
        $overwrite_db = isset($_POST['overwrite_db']);

        if (empty($username) || empty($dbname)) {
            $error = 'Please provide at least Username and Database Name.';
        } else {
            $mysqli = @new mysqli($server, $username, $password, $dbname);
            if ($mysqli && !$mysqli->connect_error) {
                try {
                    if (write_config($server, $username, $password, $dbname)) {
                        if ($overwrite_db) {
                            initialize_schema($mysqli, true);
                            $success = 'Configuration saved and database initialized.';
                        } else {
                            $success = 'Configuration saved!';
                        }
                        // Move to step 2
                        $step = 2;
                    } else {
                        $error = 'Failed to write config.php. Check file permissions.';
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
                $mysqli->close();
            } else {
                $error = 'Connection failed: ' . ($mysqli ? $mysqli->connect_error : 'Unknown error');
            }
        }
    } elseif ($step === 2) {
        // Create admin user
        require_once __DIR__ . '/config.php';
        $mysqli = @new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if ($mysqli && !$mysqli->connect_error) {
            $admin_user = trim($_POST['admin_username'] ?? '');
            $admin_pass = trim($_POST['admin_password'] ?? '');
            $subsidiary = trim($_POST['subsidiary'] ?? 'ITECSOL');

            if (empty($admin_user) || empty($admin_pass)) {
                $error = 'Please provide admin username and password.';
            } else {
                $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
                // Ensure users table exists
                $mysqli->query("CREATE TABLE IF NOT EXISTS users (\n                    id INT AUTO_INCREMENT PRIMARY KEY,\n                    username VARCHAR(100) NOT NULL UNIQUE,\n                    password VARCHAR(255) NOT NULL,\n                    role ENUM('admin','operator') NOT NULL DEFAULT 'admin',\n                    subsidiary VARCHAR(50) NOT NULL DEFAULT 'ITECSOL',\n                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP\n                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

                // Upsert admin
                $stmt = $mysqli->prepare("INSERT INTO users (username, password, role, subsidiary) VALUES (?, ?, 'admin', ?) ON DUPLICATE KEY UPDATE password = VALUES(password), role='admin', subsidiary=VALUES(subsidiary)");
                if ($stmt) {
                    $stmt->bind_param('sss', $admin_user, $hash, $subsidiary);
                    if ($stmt->execute()) {
                        $success = 'Admin account ready. You can now log in.';
                    } else {
                        $error = 'Failed to create admin: ' . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error = 'Failed to prepare statement: ' . $mysqli->error;
                }
            }
            $mysqli->close();
        } else {
            $error = 'Database connection failed using saved config.';
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
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-xl bg-gray-800/60 border border-gray-700 rounded-xl p-6">
            <h1 class="text-2xl font-bold mb-2">Setup Wizard</h1>
            <p class="text-gray-400 mb-6">Configure your database and create the initial admin account. Works in cPanel and XAMPP, including subfolders.</p>

            <?php if (!empty($error)): ?>
              <div class="mb-4 p-3 rounded bg-red-500/20 border border-red-500 text-red-300"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
              <div class="mb-4 p-3 rounded bg-green-500/20 border border-green-500 text-green-300"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
            <form method="post" class="space-y-4">
                <input type="hidden" name="step" value="1">
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

                <div class="flex items-center mt-2">
                    <input id="overwrite_db" name="overwrite_db" type="checkbox" class="w-4 h-4 text-cyan-600 bg-gray-900 border-gray-700 rounded">
                    <label for="overwrite_db" class="ml-2 text-sm text-gray-300">Overwrite existing tables and initialize schema</label>
                </div>

                <button type="submit" class="mt-4 inline-flex items-center px-4 py-2 rounded bg-cyan-600 hover:bg-cyan-500 text-white">
                    <span class="material-symbols-outlined mr-2">settings</span>
                    Save & Continue
                </button>
            </form>
            <?php elseif ($step === 2): ?>
            <form method="post" class="space-y-4">
                <input type="hidden" name="step" value="2">
                <div>
                    <label class="block text-sm text-gray-300 mb-1">Admin Username</label>
                    <input type="text" name="admin_username" value="<?= htmlspecialchars($_POST['admin_username'] ?? 'admin') ?>" class="w-full px-3 py-2 rounded bg-gray-900 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500" />
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-1">Admin Password</label>
                    <input type="password" name="admin_password" value="<?= htmlspecialchars($_POST['admin_password'] ?? '') ?>" class="w-full px-3 py-2 rounded bg-gray-900 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500" />
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-1">Subsidiary</label>
                    <select name="subsidiary" class="w-full px-3 py-2 rounded bg-gray-900 border border-gray-700 focus:outline-none">
                        <option value="ITECSOL" <?php if (($_POST['subsidiary'] ?? '') === 'ITECSOL') echo 'selected'; ?>>ITECSOL</option>
                        <option value="KONGI" <?php if (($_POST['subsidiary'] ?? '') === 'KONGI') echo 'selected'; ?>>KONGI</option>
                    </select>
                </div>

                <button type="submit" class="mt-4 inline-flex items-center px-4 py-2 rounded bg-amber-600 hover:bg-amber-500 text-white">
                    <span class="material-symbols-outlined mr-2">person_add</span>
                    Create Admin
                </button>

                <?php if (!empty($success)): ?>
                <div class="mt-4 text-sm"><a class="underline" href="login.php">Go to Login</a></div>
                <?php endif; ?>
            </form>
            <?php endif; ?>

            <div class="mt-6 text-gray-400 text-sm">
                <p>Setup writes a <code>config.php</code> next to <code>db.php</code> and optionally initializes tables used by the portal. You can re-run this wizard anytime to update credentials or recreate the schema.</p>
            </div>
        </div>
    </div>
</body>
</html>