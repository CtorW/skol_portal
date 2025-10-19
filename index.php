<?php
require_once 'includes/db.php';

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skol Portal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="w-full min-h-screen">

    <div id="landing-container" class="w-full h-screen relative flex items-center justify-center p-4">
        <div class="absolute inset-0 w-full h-full bg-cover bg-center z-0" style="background-image: url('assets/mainmian.png')"></div>
        <div class="absolute inset-0 bg-black/50 z-10"></div>
        
        <div class="relative z-20 w-full max-w-sm p-8 bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl">
            <img src="assets/logo_college.png" alt="PUP Logo" class="w-24 h-24 mx-auto mb-6">
            
            <div id="landing-panel">

                <div id="role-selection-panel">
                    <div class="flex flex-col gap-4">
                        <button id="role-student-btn" class="w-full text-left p-4 bg-gradient-to-r from-purple-500 to-indigo-500 text-white font-bold rounded-lg shadow-md hover:from-purple-600 hover:to-indigo-600 transition transform hover:scale-105">Student Portal</button>
                        <button id="role-faculty-btn" class="w-full text-left p-4 bg-gray-700 text-white font-bold rounded-lg shadow-md hover:bg-gray-800 transition transform hover:scale-105">Faculty Portal</button>
                    </div>
                </div>

                <div id="auth-choice-panel" class="hidden">
                    <h2 id="auth-choice-title" class="text-3xl font-bold text-center mb-6 text-gray-800"></h2>
                    <div class="flex flex-col gap-4">
                        <button id="show-login-btn" class="w-full p-4 bg-gradient-to-r from-purple-500 to-indigo-500 text-white font-bold rounded-lg shadow-md hover:from-purple-600 hover:to-indigo-600 transition">Log In</button>
                        <button id="show-signup-btn" class="w-full p-4 bg-gray-200 text-gray-800 font-bold rounded-lg shadow-inner hover:bg-gray-300 transition">Sign Up</button>
                    </div>
                    <button id="back-to-role-btn" class="w-full mt-6 text-sm text-gray-600 hover:underline">Back to Portal Selection</button>
                </div>

                <div id="auth-forms-panel" class="hidden">
                    <form id="login-form" class="hidden">
                        <h2 id="login-title" class="text-3xl font-bold text-center mb-2 text-gray-800"></h2>
                        <p class="text-center text-gray-500 mb-6">Welcome back!</p>
                        <div id="login-error" class="mt-4 text-center text-red-600 hidden p-2 bg-red-100 rounded-md"></div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-input" placeholder=" " required>
                            <label class="form-label">School Email</label>
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" class="form-input" placeholder=" " required>
                            <label class="form-label">Password</label>
                        </div>
                        <button type="submit" class="w-full mt-8 py-3 bg-gradient-to-r from-purple-500 to-indigo-500 text-white rounded-full font-semibold btn-ripple shadow-lg">Log In</button>
                    </form>

                    <form id="signup-form" class="hidden">
                        <h2 id="signup-title" class="text-3xl font-bold text-center mb-2 text-gray-800"></h2>
                        <p class="text-center text-gray-500 mb-6">Create your account</p>
                        <div id="signup-error" class="mt-4 text-center text-red-600 hidden p-2 bg-red-100 rounded-md"></div>
                        <div class="form-group">
                            <input type="text" name="name" class="form-input" placeholder=" " required>
                            <label class="form-label">Full Name</label>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" class="form-input" placeholder=" " required>
                            <label class="form-label">School Email</label>
                        </div>
                        <div class="form-group">
                            <input type="password" name="password" class="form-input" placeholder=" " required>
                            <label class="form-label">Password</label>
                        </div>
                        <button type="submit" class="w-full mt-8 py-3 bg-gradient-to-r from-purple-500 to-indigo-500 text-white rounded-full font-semibold btn-ripple shadow-lg">Create Account</button>
                    </form>

                    <button id="back-to-auth-choice-btn" class="w-full mt-4 text-sm text-gray-600 hover:underline">Back</button>
                </div>
            </div>
            <p class="text-xs text-gray-600 mt-8 text-center">By using this service, you agree to the <a href="#" class="text-indigo-600 underline">Terms of Use</a> and <a href="#" class="text-indigo-600 underline">Privacy Statement</a></p>
        </div>
    </div>
    
    <script src="assets/js/auth.js"></script>
</body>
</html>