<?php
session_set_cookie_params(0);
session_start();
require_once 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    $sql = "SELECT id, username, password_hash FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskHive - Login</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center">

    <div class="bg-slate-950 p-8 rounded-xl border border-slate-800 w-full max-w-md">
        <h2 class="text-2xl font-bold text-white mb-6 text-center">Sign In to TaskHive</h2>
        
        <?php if ($error): ?>
            <div class="bg-red-500/10 text-red-500 p-3 rounded-lg mb-4 text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Username</label>
                <input type="text" name="username" class="w-full p-2.5 rounded bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-teal-500" required>
            </div>
            
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Password</label>
                <input type="password" name="password" class="w-full p-2.5 rounded bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-teal-500" required>
            </div>

            <button type="submit" class="w-full bg-teal-500 hover:bg-teal-600 text-slate-950 font-bold py-2.5 px-4 rounded transition cursor-pointer">
                Login
            </button>
        </form>
        <p class="text-sm text-slate-400 text-center mt-6">
    Don't have an account yet? <a href="register.php" class="text-teal-400 hover:underline">Create Account</a>
</p>
    </div>

</body>
</html>