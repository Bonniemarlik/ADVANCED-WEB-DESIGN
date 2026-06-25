<?php
session_start();
require_once 'db.php';

// Redirect logged-in users directly to workspace panel
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$reg_error = "";
$reg_success = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Input validation checks
    if (!empty($username) && !empty($password) && !empty($role)) {
        
        // 1. Check if the username identifier already exists in the infrastructure
        $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($check_stmt, "s", $username);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $reg_error = "Identity mapping collision: Username is already registered.";
            mysqli_stmt_close($check_stmt);
        } else {
            mysqli_stmt_close($check_stmt);

            // 2. Hash security passphrase using standard system algorithm
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // 3. Inject new profile record matrix into users table (Fixed column name here)
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $role);

            if (mysqli_stmt_execute($stmt)) {
                $reg_success = "Account provisioned successfully! Proceeding to gate activation.";
                header("Refresh: 2; URL=login.php");
            } else {
                $reg_error = "System pipeline exception during database registration execution.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $reg_error = "Registration matrix inputs cannot be left blank.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TaskHive - Provision Terminal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0b1120] text-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-[#0f172a] border border-slate-800 p-8 rounded-lg shadow-2xl">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-cyan-400">⬢ TaskHive Registry</h1>
            <p class="text-xs text-slate-400 mt-1">Create a new account</p>
        </div>

        <?php if(!empty($reg_error)): ?>
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-3 rounded mb-4 text-xs">
                ❌ <?php echo $reg_error; ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($reg_success)): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-3 rounded mb-4 text-xs">
                 <?php echo $reg_success; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1"> Username</label>
                <input type="text" name="username" required class="w-full bg-[#0b1120] border border-slate-700 rounded px-3 py-2 text-sm focus:outline-none focus:border-cyan-400 text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1">Password</label>
                <input type="password" name="password" required class="w-full bg-[#0b1120] border border-slate-700 rounded px-3 py-2 text-sm focus:outline-none focus:border-cyan-400 text-white">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1">System Privilege Level Role</label>
                <select name="role" required class="w-full bg-[#0b1120] border border-slate-700 rounded px-3 py-2 text-sm focus:outline-none focus:border-cyan-400 text-slate-300">
                    <option value="Client" selected>Client (Read-Only Workspace)</option>
                    <option value="Manager">Manager (Read & Create Pipeline)</option>
                    <option value="Admin">Admin (Full Operational Clearance)</option>
                </select>
            </div>
            
            <button type="submit" name="register" class="w-full bg-cyan-500 text-slate-900 text-xs font-bold uppercase tracking-wider py-2.5 rounded hover:bg-cyan-400 transition mt-2">
                CREATE ACCOUNT
            </button>
            
            <p class="text-center text-[11px] text-slate-500 pt-2">
               Have an account? <a href="login.php" class="text-cyan-400 hover:underline">log in.</a>
            </p>
        </form>
    </div>

</body>
</html>