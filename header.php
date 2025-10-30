<?php
// Initialize the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

$page = basename($_SERVER['PHP_SELF']);
$subsidiary_name = $_SESSION["subsidiary"];
$theme_color_class = $subsidiary_name == 'ITECSOL' ? 'text-cyan-400' : 'text-amber-400';
$bg_theme_color_class = $subsidiary_name == 'ITECSOL' ? 'bg-cyan-500' : 'bg-amber-500';
$ring_theme_color_class = $subsidiary_name == 'ITECSOL' ? 'ring-cyan-500' : 'ring-amber-500';

?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Waste Management Portal'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
    <style>
        /* Custom scrollbar for webkit browsers */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1a202c; }
        ::-webkit-scrollbar-thumb { background: #4a5568; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #718096; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; font-size: 22px; }
    </style>
</head>
<body class="bg-gray-900 text-gray-200 font-sans">
    <div id="app" class="flex h-screen">
        <aside id="sidebar" class="bg-gray-800 w-64 min-h-screen flex-shrink-0 transition-all duration-300 ease-in-out -translate-x-full md:translate-x-0">
            <div class="px-6 py-4 border-b border-gray-700">
                <h1 class="text-2xl font-bold <?php echo $theme_color_class; ?>"><?php echo $subsidiary_name; ?></h1>
                <p class="text-xs text-gray-400">Waste Management</p>
            </div>
            <nav class="mt-6">
                <a href="dashboard.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700/50 hover:text-white <?php if($page == 'dashboard.php') echo 'bg-gray-900/80 border-l-4 border-cyan-400'; ?>">
                    <span class="material-symbols-outlined mr-3">dashboard</span> Dashboard
                </a>
                <?php if ($subsidiary_name == 'ITECSOL'): ?>
                <a href="private.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700/50 hover:text-white <?php if($page == 'private.php') echo 'bg-gray-900/80 border-l-4 border-cyan-400'; ?>">
                    <span class="material-symbols-outlined mr-3">home</span> Private
                </a>
                <?php else: ?>
                <a href="commercial.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700/50 hover:text-white <?php if($page == 'commercial.php') echo 'bg-gray-900/80 border-l-4 border-amber-400'; ?>">
                    <span class="material-symbols-outlined mr-3">business_center</span> Commercial
                </a>
                <?php endif; ?>
                <a href="import.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700/50 hover:text-white <?php if($page == 'import.php') echo 'bg-gray-900/80 border-l-4 border-'.$theme_color_class; ?>">
                    <span class="material-symbols-outlined mr-3">upload</span> Data Import
                </a>
                <a href="payment_review.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700/50 hover:text-white <?php if($page == 'payment_review.php') echo 'bg-gray-900/80 border-l-4 border-'.$theme_color_class; ?>">
                    <span class="material-symbols-outlined mr-3">receipt_long</span> Payment Review
                </a>
                 <a href="guide.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700/50 hover:text-white <?php if($page == 'guide.php') echo 'bg-gray-900/80 border-l-4 border-'.$theme_color_class; ?>">
                    <span class="material-symbols-outlined mr-3">help_center</span> Guide
                </a>

                <?php if ($_SESSION['role'] == 'admin'): ?>
                <div class="px-6 mt-4 mb-2 text-xs uppercase text-gray-500">Admin</div>
                <a href="rates.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700/50 hover:text-white <?php if($page == 'rates.php') echo 'bg-gray-900/80 border-l-4 border-'.$theme_color_class; ?>">
                    <span class="material-symbols-outlined mr-3">price_change</span> Global Rates
                </a>
                <a href="backup_restore.php" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700/50 hover:text-white <?php if($page == 'backup_restore.php') echo 'bg-gray-900/80 border-l-4 border-'.$theme_color_class; ?>">
                    <span class="material-symbols-outlined mr-3">database</span> Backup & Restore
                </a>
                <?php endif; ?>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-gray-800/50 backdrop-blur-sm border-b border-gray-700/50 shadow-md flex-shrink-0">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <button id="menu-btn" class="md:hidden text-gray-300 hover:text-white focus:outline-none">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div class="flex items-center ml-auto">
                        <div class="text-right mr-4">
                            <p class="font-semibold text-sm text-white"><?php echo htmlspecialchars($_SESSION["username"]); ?></p>
                            <p class="text-xs text-gray-400 capitalize"><?php echo htmlspecialchars($_SESSION["role"]); ?></p>
                        </div>
                        <a href="logout.php" class="flex items-center p-2 rounded-full bg-gray-700 hover:bg-red-500/50 text-gray-300 hover:text-white" title="Logout">
                           <span class="material-symbols-outlined">logout</span>
                        </a>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-900 p-4 sm:p-6 lg:p-8">