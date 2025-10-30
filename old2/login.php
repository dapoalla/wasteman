<?php
require_once "db.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id, username, password, role, subsidiary FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_username);
            $param_username = $username;

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $username, $hashed_password, $role, $subsidiary);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;
                            $_SESSION["subsidiary"] = $subsidiary;

                            // Redirect user to dashboard
                            header("location: dashboard.php");
                        } else {
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    $login_err = "Invalid username or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Waste Management Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
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
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24 }
    </style>
</head>
<body class="bg-gray-900 text-gray-200 font-sans flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 space-y-8 bg-gray-800 rounded-2xl shadow-lg">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-white">
                Waste Management Portal
            </h2>
            <p class="mt-2 text-center text-sm text-gray-400">
                Sign in to your account
            </p>
        </div>
        <?php 
        if(!empty($login_err)){
            echo '<div class="bg-red-500/20 text-red-300 p-3 rounded-lg text-center">' . $login_err . '</div>';
        }        
        ?>
        <form class="mt-8 space-y-6" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="remember" value="true">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="username" class="sr-only">Username</label>
                    <input id="username" name="username" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-700 bg-gray-900 placeholder-gray-500 text-white rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm <?php echo (!empty($username_err)) ? 'border-red-500' : ''; ?>" value="<?php echo $username; ?>" placeholder="Username">
                    <span class="text-red-500 text-xs italic"><?php echo $username_err; ?></span>
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-gray-700 bg-gray-900 placeholder-gray-500 text-white rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm <?php echo (!empty($password_err)) ? 'border-red-500' : ''; ?>" placeholder="Password">
                     <span class="text-red-500 text-xs italic"><?php echo $password_err; ?></span>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 focus:ring-offset-gray-900">
                    Sign in
                </button>
       
        </form>
    </div>
</body>
</html>
