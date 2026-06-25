<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$login_error = "";

if (isset($_POST['authenticate'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Prepare statement to fetch the user record
        $stmt = mysqli_prepare($conn, "SELECT id, username, password_hash, role FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // Read the exact database password hash and role
            $db_password = $user['password_hash'];
            $user_role = isset($user['role']) ? $user['role'] : 'Client'; 

            // Verify the entered text password against the encrypted database hash
            if (password_verify($password, $db_password)) { 
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user_role; 
                
                header("Location: index.php");
                exit();
            } else {
                $login_error = "Invalid credential mapping (Incorrect Passphrase).";
            }
        } else {
            $login_error = "Identity record not found in authorization cluster.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $login_error = "Authentication inputs cannot be blank.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TaskHive - Auth Gateway</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0b1120] text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-[#0f172a] border border-slate-800 p-8 rounded-lg shadow-2xl">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-cyan-400">⬢ Welcome to TaskHive</h1>
            <p class="text-xs text-slate-400 mt-1">Log in to your account</p>
        </div>

        <?php if(!empty($login_error)): ?>
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-3 rounded mb-4 text-xs">
                ❌ <?php echo $login_error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1">Username</label>
                <input type="text" name="username" required class="w-full bg-[#0b1120] border border-slate-700 rounded px-3 py-2 text-sm focus:outline-none focus:border-cyan-400 text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1">Password</label>
                <input type="password" name="password" required class="w-full bg-[#0b1120] border border-slate-700 rounded px-3 py-2 text-sm focus:outline-none focus:border-cyan-400 text-white">
            </div>
            
            <button type="submit" name="authenticate" class="w-full bg-cyan-500 text-slate-900 text-xs font-bold uppercase tracking-wider py-2.5 rounded hover:bg-cyan-400 transition mt-2">
                LOGIN
            </button>

            <p class="text-center text-[11px] text-slate-500 pt-4">
                Don't have an account? <a href="register.php" class="text-cyan-400 hover:underline">Create one here</a>
            </p>
        </form>
    </div>

</body>
</html>