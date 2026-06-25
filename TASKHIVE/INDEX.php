<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['username'];
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'Client'; 

$success_msg = "";
$error_msg = "";

// CREATE (Admins & Managers Only)
if (isset($_POST['create_task'])) {
    if ($user_role === 'Admin' || $user_role === 'Manager') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);

        if (!empty($title)) {
            $stmt = mysqli_prepare($conn, "INSERT INTO tasks (user_id, title, description) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iss", $user_id, $title, $description);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Task node created successfully!";
            } else {
                $error_msg = "Compilation failure during task database insertion.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "Task title validation error: cannot be empty.";
        }
    } else {
        $error_msg = "Security Exception: Clients are restricted from executing structural task setups.";
    }
}

// UPDATE STATE (All Roles Authorized)
if (isset($_POST['update_task'])) {
    $task_id = intval($_POST['task_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']); 

    if ($user_role === 'Admin' || $user_role === 'Manager') {
        $stmt = mysqli_prepare($conn, "UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sssi", $title, $description, $status, $task_id);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "sssii", $title, $description, $status, $task_id, $user_id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $success_msg = "Task node payload updated successfully.";
    } else {
        $error_msg = "Failed to update target task node system status.";
    }
    mysqli_stmt_close($stmt);
}

// PURGE/DELETE (Strictly Admins Only)
if (isset($_GET['delete_id'])) {
    if ($user_role === 'Admin') {
        $delete_id = intval($_GET['delete_id']);

        $stmt = mysqli_prepare($conn, "DELETE FROM tasks WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = "Task record structure safely purged from database.";
        } else {
            $error_msg = "An anomaly occurred while trying to drop task entity.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_msg = "Privilege Escalation Warning: Only Administrator configurations can delete pipeline nodes.";
    }
}

// READ OPERATION (Dynamic Role Binding)
if ($user_role === 'Admin' || $user_role === 'Manager') {
    $query = "SELECT tasks.id, tasks.title, tasks.description, tasks.status, tasks.created_at, users.username AS owner 
              FROM tasks JOIN users ON tasks.user_id = users.id ORDER BY tasks.created_at DESC";
    $tasks_result = mysqli_query($conn, $query);
} else {
    $stmt = mysqli_prepare($conn, "SELECT id, title, description, status, created_at, ? as owner FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, "si", $user_name, $user_id);
    mysqli_stmt_execute($stmt);
    $tasks_result = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TaskHive - Workspace Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0b1120] text-slate-100 min-h-screen p-8">

    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center border-b border-slate-800 pb-5 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-cyan-400">⬢ TaskHive Console</h1>
                <p class="text-xs text-slate-400 mt-1">User: <span class="text-white font-mono"><?php echo htmlspecialchars($user_name); ?></span> | Authorization: <span class="text-amber-400 font-bold"><?php echo $user_role; ?></span></p>
            </div>
            <a href="logout.php" class="text-xs bg-rose-600/20 text-rose-400 border border-rose-500/30 px-3 py-1.5 rounded hover:bg-rose-600/40 transition">Disconnect Session</a>
        </div>

        <?php if(!empty($success_msg)): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-3 rounded mb-6 text-sm"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if(!empty($error_msg)): ?>
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 p-3 rounded mb-6 text-sm"> <?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="bg-[#0f172a] border border-slate-800 p-6 rounded-lg h-fit">
                <?php if ($user_role === 'Admin' || $user_role === 'Manager'): ?>
                    <h2 class="text-sm font-bold text-cyan-400 uppercase tracking-wider mb-4">INPUT TASK:</h2>
                    <form action="index.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Task Title Label</label>
                            <input type="text" name="title" required class="w-full bg-[#0b1120] border border-slate-700 rounded px-3 py-2 text-sm focus:outline-none focus:border-cyan-400 text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1">Task Core Description / Blueprint</label>
                            <textarea name="description" rows="3" class="w-full bg-[#0b1120] border border-slate-700 rounded px-3 py-2 text-sm focus:outline-none focus:border-cyan-400 text-white"></textarea>
                        </div>
                        <button type="submit" name="create_task" class="w-full bg-cyan-500 text-slate-900 text-xs font-bold uppercase tracking-wider py-2.5 rounded hover:bg-cyan-400 transition">Commit to Database</button>
                    </form>
                    <p class="text-center text-[11px] text-slate-500 pt-4">
    New user? <a href="register.php" class="text-cyan-400 hover:underline">Create an account here</a>
</p>
                <?php else: ?>
                    <div class="text-slate-400 text-xs text-center space-y-2 py-4">
                        <p class="font-bold text-amber-500 font-mono">⚠️ VIEW SYSTEM RESTRICTED</p>
                        <p>Your workspace is locked to structural Client mode pipeline parameters. Task injection form disabled.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="md:grid-cols-1 md:col-span-2 space-y-4">
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Active Task Streams Panel</h2>
                
                <?php if (mysqli_num_rows($tasks_result) === 0): ?>
                    <p class="text-xs text-slate-500 italic bg-[#0f172a] border border-dashed border-slate-800 p-6 rounded text-center">No active workspace tasks cataloged in this operational sector.</p>
                <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($tasks_result)): ?>
                        <div class="bg-[#0f172a] border border-slate-800 p-5 rounded-lg flex justify-between items-start transition hover:border-slate-700">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-3 flex-wrap gap-y-1">
                                    <span class="text-[10px] px-2 py-0.5 rounded font-mono font-bold <?php echo $row['status'] == 'Completed' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'; ?>">
                                        ● <?php echo $row['status']; ?>
                                    </span>
                                    <h3 class="font-bold text-slate-200"><?php echo htmlspecialchars($row['title']); ?></h3>
                                    <span class="text-[10px] text-slate-500 font-mono">by: @<?php echo htmlspecialchars($row['owner']); ?></span>
                                </div>
                                <p class="text-xs text-slate-400 pr-4 pt-1"><?php echo htmlspecialchars($row['description']); ?></p>
                                <span class="block text-[10px] font-mono text-slate-500 pt-2">System Deployment Mark: <?php echo $row['created_at']; ?></span>
                            </div>
                            
                            <div class="flex space-x-2 shrink-0">
                                <?php if ($row['status'] !== 'Completed'): ?>
                                    <form action="index.php" method="POST" class="inline">
                                        <input type="hidden" name="task_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="title" value="<?php echo htmlspecialchars($row['title']); ?>">
                                        <input type="hidden" name="description" value="<?php echo htmlspecialchars($row['description']); ?>">
                                        <input type="hidden" name="status" value="Completed">
                                        <button type="submit" name="update_task" class="text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-1 rounded hover:bg-emerald-500/30 transition">✔ Resolve</button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($user_role === 'Admin'): ?>
                                    <a href="index.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Purge task node?');" class="text-[10px] bg-rose-500/10 text-rose-400 border border-rose-500/20 px-2 py-1 rounded hover:bg-rose-500/30 transition">✕ Delete</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>
<?php 

?>