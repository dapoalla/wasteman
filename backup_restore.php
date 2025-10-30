<?php
// Backup & Restore utility (admin only)
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$message_type = 'info';

function export_database($conn) {
    $dbname = DB_NAME;
    $sql = "";
    $tables = [];
    $res = $conn->query("SHOW TABLES");
    while ($row = $res->fetch_array()) { $tables[] = $row[0]; }

    foreach ($tables as $table) {
        // Schema
        $create = $conn->query("SHOW CREATE TABLE `$table`")->fetch_array()[1];
        $sql .= "\nDROP TABLE IF EXISTS `$table`;\n$create;\n\n";

        // Data
        $result = $conn->query("SELECT * FROM `$table`");
        while ($row = $result->fetch_assoc()) {
            $cols = array_map(fn($c) => "`" . $conn->real_escape_string($c) . "`", array_keys($row));
            $vals = array_map(function($v) use ($conn) {
                if ($v === null) return 'NULL';
                return "'" . $conn->real_escape_string($v) . "'";
            }, array_values($row));
            $sql .= "INSERT INTO `$table` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n";
        }
        $sql .= "\n";
    }

    $dir = __DIR__ . '/backups';
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
    $file = $dir . '/backup_' . date('Ymd_His') . '.sql';
    file_put_contents($file, $sql);
    return $file;
}

function restore_database($conn, $sql_content) {
    $conn->multi_query($sql_content);
    // Flush remaining results
    while ($conn->more_results() && $conn->next_result()) { /* drain */ }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'backup') {
        $file = export_database($conn);
        $message = 'Backup created: ' . basename($file);
        $message_type = 'success';
    } elseif (isset($_POST['action']) && $_POST['action'] === 'restore' && isset($_FILES['sql_file'])) {
        if ($_FILES['sql_file']['error'] === UPLOAD_ERR_OK) {
            $sql = file_get_contents($_FILES['sql_file']['tmp_name']);
            restore_database($conn, $sql);
            $message = 'Restore completed.';
            $message_type = 'success';
        } else {
            $message = 'Upload error. Please try again.';
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Restore</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
<?php $title = 'Backup & Restore'; require_once __DIR__ . '/header.php'; ?>
<div class="container mx-auto px-4">
    <h2 class="text-2xl md:text-3xl font-bold text-white mb-6">Backup & Restore</h2>

    <?php if (!empty($message)): ?>
        <div class="mb-4 p-3 rounded <?php echo $message_type === 'success' ? 'bg-green-500/20 border border-green-500 text-green-300' : ($message_type === 'error' ? 'bg-red-500/20 border border-red-500 text-red-300' : 'bg-gray-700/30 border border-gray-700 text-gray-200'); ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-gray-800/50 p-6 rounded-xl border border-gray-700">
            <h3 class="text-xl font-semibold mb-2">Backup Database</h3>
            <p class="text-gray-400 mb-4">Exports all tables and data to an SQL file in <code>backups/</code>.</p>
            <form method="post">
                <input type="hidden" name="action" value="backup" />
                <button class="inline-flex items-center px-4 py-2 rounded bg-cyan-600 hover:bg-cyan-500 text-white">
                    <span class="material-symbols-outlined mr-2">download</span>
                    Create Backup
                </button>
            </form>
        </div>

        <div class="bg-gray-800/50 p-6 rounded-xl border border-gray-700">
            <h3 class="text-xl font-semibold mb-2">Restore Database</h3>
            <p class="text-gray-400 mb-4">Upload an SQL file to restore schema and data.</p>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="restore" />
                <input type="file" name="sql_file" accept=".sql" class="mb-3" />
                <button class="inline-flex items-center px-4 py-2 rounded bg-amber-600 hover:bg-amber-500 text-white">
                    <span class="material-symbols-outlined mr-2">upload_file</span>
                    Restore
                </button>
            </form>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/footer.php'; ?>
</body>
</html>