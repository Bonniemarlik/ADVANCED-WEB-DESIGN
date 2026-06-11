<?php
session_set_cookie_params(0);
session_start();
require_once 'db.php'; 

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        // Securely hash the password before saving it to the database
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Check if the username already exists
        $check_sql = "SELECT id FROM users WHERE username = '$username'";
        $check_result = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_result) > 0) {
            $message = "<div class='bg-red-500/10 text-red-500 p-3 rounded-lg text-sm'>⚠️ Username already taken!</div>";
        } else {
            // Insert the new user into the database
            $sql = "INSERT INTO users (username, password_hash) VALUES ('$username', '$password_hash')";
            if (mysqli_query($conn, $sql)) {
                $message = "<div class='bg-emerald-500/10 text-emerald-500 p-3 rounded-lg text-sm'>🎉 Account created! <a href='login.php' class='text-teal-400 underline font-bold'>Click here to Login</a></div>";
            } else {
                $message = "<div class='bg-red-500/10 text-red-500 p-3 rounded-lg text-sm'>❌ Error: " . mysqli_error($conn) . "</div>";
            }
        }
    } else {
        $message = "<div class='bg-red-500/10 text-red-500 p-3 rounded-lg text-sm'>⚠️ Please fill in all fields.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskHive - Register</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center">

    <div class="bg-slate-950 p-8 rounded-xl border border-slate-800 w-full max-w-md">
        <h2 class="text-2xl font-bold text-white mb-2 text-center">Create Account</h2>
        <p class="text-slate-400 text-sm text-center mb-6">Join TaskHive to manage your team sprint logs.</p>
        
        <?php echo $message; ?>

        <form action="register.php" method="POST" class="space-y-4 mt-4">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Choose Username</label>
                <input type="text" name="username" class="w-full p-2.5 rounded bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-teal-500" required>
            </div>
            
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Password</label>
                <input type="password" name="password" class="w-full p-2.5 rounded bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-teal-500" required>
            </div>

            <button type="submit" class="w-full bg-teal-500 hover:bg-teal-600 text-slate-950 font-bold py-2.5 px-4 rounded transition cursor-pointer mt-2">
                Register Account
            </button>
        </form>

        <p class="text-sm text-slate-400 text-center mt-6">
            Already have an account? <a href="login.php" class="text-teal-400 hover:underline">Sign In</a>
        </p>
    </div>

</body>
</html>