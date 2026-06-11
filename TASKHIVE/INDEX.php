<?php 
// 1. START SESSION & CHECK AUTHENTICATION
session_set_cookie_params(0);
session_start();

// If the user session variable is not set, redirect them straight to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. DATABASE CONNECTION & TASK LOGIC
require_once 'db.php'; 

$message = "";

// Process form data when the logged-in user submits a new task
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));

    if (!empty($title) && !empty($description)) {
    
        $sql = "INSERT INTO tasks (title, description) VALUES ('$title', '$description')";
        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert-success'>🚀 Task created and sync'd successfully!</div>";
        } else {
            $message = "<div class='alert-error'>❌ Database Error: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Fetch all active tasks for display
$query = "SELECT * FROM tasks ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskHive - Agile Task Workspace</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        .alert-success { color: #155724; background-color: #d4edda; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .alert-error { color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen font-sans">

    <div class="flex">
        <aside class="w-64 h-screen bg-slate-950 p-6 sticky top-0 hidden md:block flex flex-col justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-wider text-teal-400 mb-8">🐝 TaskHive</h2>
                <nav class="space-y-4">
                    <a href="#" class="block py-2 px-4 rounded bg-slate-800 text-white font-medium">📋 Dashboard</a>
                    <a href="#" class="block py-2 px-4 rounded text-slate-400 hover:bg-slate-900 hover:text-white transition">📊 Analytics</a>
                    <a href="#" class="block py-2 px-4 rounded text-slate-400 hover:bg-slate-900 hover:text-white transition">⚙️ Settings</a>
                </nav>
            </div>
            
            <div class="pt-4 border-t border-slate-800">
                <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Logged in as:</p>
                <p class="text-sm font-bold text-teal-400 mb-3">@<?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <a href="logout.php" class="block w-full text-center bg-red-500/10 hover:bg-red-500/20 text-red-400 font-medium py-2 px-4 rounded transition text-xs">
                    🚪 Logout Securely
                </a>
            </div>
        </aside>

        <main class="flex-1 p-8">
            <header class="mb-8 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-extrabold text-white">Project Workspace</h1>
                    <p class="text-slate-400 text-sm">Welcome back, <span class="text-teal-400 font-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></span>! Track team workflows.</p>
                </div>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="bg-slate-950 p-6 rounded-xl border border-slate-800 h-fit">
                    <h3 class="text-xl font-bold text-white mb-4">Create New Task</h3>
                    
                    <?php echo $message; ?>

                    <form id="taskForm" action="index.php" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Task Title</label>
                            <input type="text" name="title" id="taskTitle" class="w-full p-2.5 rounded bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-teal-500" placeholder="e.g., Fix Navigation Bug">
                            <span id="titleError" class="text-xs text-red-400 mt-1 hidden"></span>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Description</label>
                            <textarea name="description" id="taskDesc" rows="3" class="w-full p-2.5 rounded bg-slate-900 border border-slate-700 text-white focus:outline-none focus:border-teal-500" placeholder="Describe the scope of the work..."></textarea>
                            <span id="descError" class="text-xs text-red-400 mt-1 hidden"></span>
                        </div>

                        <button type="submit" class="w-full bg-teal-500 hover:bg-teal-600 text-slate-950 font-bold py-2.5 px-4 rounded transition cursor-pointer">
                            Add to Board
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-2">
                    <h3 class="text-xl font-bold text-white mb-4">Active Tasks Sprint Board</h3>
                    <div class="bg-slate-950 p-6 rounded-xl border border-slate-800 space-y-4">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <div class="p-4 bg-slate-900 rounded-lg border-l-4 border-teal-400 flex justify-between items-start gap-4">
                                    <div>
                                        <h4 class="font-bold text-white text-lg"><?php echo htmlspecialchars($row['title']); ?></h4>
                                        <p class="text-slate-400 text-sm mt-1"><?php echo htmlspecialchars($row['description']); ?></p>
                                    </div>
                                    <span class="text-xs bg-teal-500/10 text-teal-400 px-2.5 py-1 rounded-full font-mono uppercase tracking-wide">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-slate-500 text-center py-8">No tasks added to the board yet. Complete the form to seed the database.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById("taskForm").addEventListener("submit", function(event) {
            const titleInput = document.getElementById("taskTitle");
            const descInput = document.getElementById("taskDesc");
            const titleError = document.getElementById("titleError");
            const descError = document.getElementById("descError");

            let isValid = true;

            if (titleInput.value.trim() === "") {
                titleError.textContent = "⚠️ Validation Error: Task Title is required!";
                titleError.classList.remove("hidden");
                titleInput.classList.add("border-red-500");
                isValid = false;
            } else {
                titleError.classList.add("hidden");
                titleInput.classList.remove("border-red-500");
            }

            if (descInput.value.trim() === "") {
                descError.textContent = "⚠️ Validation Error: Task Description cannot be empty!";
                descError.classList.remove("hidden");
                descInput.classList.add("border-red-500");
                isValid = false;
            } else {
                descError.classList.add("hidden");
                descInput.classList.remove("border-red-500");
            }

            if (!isValid) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>