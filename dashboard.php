<?php
require_once 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skol Portal - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="w-full min-h-screen">

    <div id="app-container" class="h-screen w-full p-4 bg-gray-50">
        <div class="flex h-full max-w-7xl mx-auto">
            <nav id="sidebar" class="w-64 bg-white rounded-3xl flex flex-col p-4 shadow-lg">
                <div class="flex items-center gap-4 p-2 mb-6">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center bg-gradient-to-br from-purple-200 to-indigo-200">
                        <span class="material-symbols-outlined text-4xl text-purple-600">account_circle</span>
                    </div>
                    <div>
                        <p class="font-bold text-lg"><?php echo htmlspecialchars($user['name']); ?></p>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($user['role']); ?></p>
                    </div>
                </div>
                <ul id="nav-links" class="flex flex-col gap-2 flex-grow"></ul>
                <form id="logout-form">
                    <input type="hidden" name="action" value="logout">
                    <button type="button" id="logout-btn" class="w-full flex items-center justify-center gap-3 py-3 px-4 text-red-500 hover:bg-red-100 rounded-full transition-colors">
                        <span class="material-symbols-outlined">logout</span>
                        <span class="font-semibold">Logout</span>
                    </button>
                </form>
            </nav>
            <main id="main-content-area" class="flex-1 p-8 overflow-y-auto">
            </main>
        </div>
    </div>
    
    <div id="user-modal" class="modal-overlay">
        <div class="modal-panel">
            <h2 id="modal-title" class="text-2xl font-bold mb-6">Edit User</h2>
            <form>
                <div class="form-group">
                    <input type="text" id="modal-name" name="name" class="form-input" placeholder=" " required>
                    <label class="form-label">Full Name</label>
                </div>
                <div class="form-group mt-6">
                    <input type="email" id="modal-email" name="email" class="form-input" placeholder=" " required>
                    <label class="form-label">Email</label>
                </div>
                <div class="mt-6">
                    <label for="modal-role" class="text-sm text-gray-600">Role</label>
                    <select id="modal-role" name="role" class="w-full p-2 mt-1 rounded-md border border-[var(--md-sys-color-outline)] bg-transparent">
                        <option>Student</option>
                        <option>Faculty</option>
                    </select>
                </div>
                <div class="flex justify-end gap-4 mt-8">
                    <button type="button" class="px-6 py-2 rounded-full hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-gradient-to-r from-purple-500 to-indigo-500 text-white rounded-full btn-ripple">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const USER_ROLE = "<?php echo $user['role']; ?>";
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>