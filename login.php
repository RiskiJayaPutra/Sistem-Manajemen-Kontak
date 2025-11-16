<?php
require_once 'functions.php';

$error = '';
$success = '';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = bersihkanInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $users = getUsers();
        $user_found = null;
        
        foreach ($users as $user) {
            if ($user['username'] == $username) {
                $user_found = $user;
                break;
            }
        }
        
        if ($user_found && password_verify($password, $user_found['password'])) {
            $_SESSION['user_id'] = $user_found['id'];
            $_SESSION['username'] = $user_found['username'];
            $_SESSION['nama_tampilan'] = $user_found['nama_tampilan'];
            $_SESSION['login_time'] = time();
            
            header("Location: index.php");
            exit();
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Manajemen Kontak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes slideInDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .animate-slideInDown {
            animation: slideInDown 0.5s ease-out;
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.8s ease-in;
        }
        
        .animate-pulse-slow {
            animation: pulse 3s infinite;
        }
        
        .bg-primary {
            background: #1e40af;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body class="bg-blue-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md animate-fadeIn">
        <!-- Logo/Header -->
        <div class="text-center mb-8 animate-slideInDown">
            <div class="inline-block p-4 bg-white rounded-full shadow-2xl mb-4 animate-pulse-slow border-4 border-blue-700">
                <i class="fas fa-address-book text-5xl text-blue-700"></i>
            </div>
            <h1 class="text-4xl font-bold text-blue-900 mb-2">Sistem Manajemen Kontak</h1>
        </div>
        
        <!-- Login Form -->
        <div class="glass-effect rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                <i class="fas fa-sign-in-alt mr-2 text-blue-700"></i>Masuk ke Akun
            </h2>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded animate-slideInDown">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <p><?php echo $error; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded animate-slideInDown">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <p><?php echo $success; ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div class="transform transition-all duration-300 hover:scale-105">
                    <label class="block text-gray-700 font-semibold mb-2" for="username">
                        <i class="fas fa-user mr-2 text-blue-700"></i>Username
                    </label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           required
                           class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-700 transition-colors duration-300"
                           placeholder="Masukkan username Anda">
                </div>
                
                <div class="transform transition-all duration-300 hover:scale-105">
                    <label class="block text-gray-700 font-semibold mb-2" for="password">
                        <i class="fas fa-lock mr-2 text-blue-700"></i>Password
                    </label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-700 transition-colors duration-300"
                           placeholder="Masukkan password Anda">
                </div>
                
                <button type="submit" 
                        class="w-full bg-blue-700 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-800 transform transition-all duration-300 hover:scale-105 shadow-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk Sekarang
                </button>
            </form>
            
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-sm text-gray-600 text-center">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    <strong>Info Login:</strong> Username: <code class="bg-gray-200 px-2 py-1 rounded">RiskiJayaPutra</code> | 
                    Password: <code class="bg-gray-200 px-2 py-1 rounded">iki123</code>
                </p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-6 text-blue-900 space-y-2">
            <p class="text-sm">
                <i class="fas fa-shield-alt mr-2"></i>
                Sistem keamanan terlindungi dengan enkripsi
            </p>
            <p class="text-sm">
                <a href="dokumentasi.html" class="underline hover:text-blue-700 transition-colors">
                    <i class="fas fa-book mr-2"></i>Lihat Dokumentasi Lengkap
                </a>
            </p>
        </div>
    </div>
</body>
</html>
