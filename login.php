<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: dashboard_admin.php");
    } else {
        header("Location: dashboard_user.php");
    }
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $username = input_bersih($_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $row['password'])) {
            // Set session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = $row['role'];

            // Redirect berdasarkan role
            if ($row['role'] == 'admin') {
                header("Location: dashboard_admin.php");
            } else {
                header("Location: dashboard_user.php");
            }
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Wisata_Malang</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS untuk Animasi Galaxy -->
    <link rel="stylesheet" href="style_stars.css">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .text-glow {
            text-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }
    </style>
</head>
<body class="bg-[#050b18] text-white overflow-hidden min-h-screen flex flex-col">

    <!-- Efek Bintang Bergerak -->
    <div id="stars-container" class="fixed inset-0 z-0 pointer-events-none">
        <?php for ($i = 0; $i < 50; $i++): $size = rand(1, 2); ?>
            <div class="star absolute bg-white rounded-full opacity-40" 
                style="left: <?= rand(0, 100) ?>%; width: <?= $size ?>px; height: <?= $size ?>px; top: <?= rand(0, 100) ?>%; --duration: <?= rand(10, 25) ?>s;">
            </div>
        <?php endfor; ?>
    </div>

    <!-- Navigasi Sederhana -->
    <nav class="relative z-10 p-6 flex justify-center">
        <div class="flex gap-8 text-xs font-bold tracking-widest border-b border-white/10 pb-2">
            <a href="login.php" class="text-blue-500 border-b-2 border-blue-500 pb-2">LOGIN</a>
            <a href="index.php#join" class="text-gray-500 hover:text-white transition">REGISTER</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 flex-grow flex items-center justify-center px-6">
        <div class="glass-card w-full max-w-md p-8 md:p-10 rounded-[2.5rem] shadow-2xl transition-all hover:border-blue-500/30">
            
            <div class="text-center mb-8">
                <h2 class="text-3xl font-black mb-2 tracking-tight">Selamat <span class="text-blue-500 text-glow">Datang</span></h2>
                <p class="text-gray-400 text-sm">Masuk untuk melanjutkan petualangan Anda</p>
            </div>

            <!-- Pesan Error -->
            <?php if ($error != ""): ?>
                <div class="bg-red-500/10 border border-red-500/50 text-red-500 text-xs p-4 rounded-xl mb-6 text-center animate-pulse">
                    <?= $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-5">
                <div>
                    <label class="block text-[10px] font-black text-gray-500 mb-2 ml-1 tracking-[0.2em]">USERNAME</label>
                    <input type="text" name="username" required
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 focus:outline-none focus:border-blue-500 focus:bg-white/10 transition-all text-sm placeholder:text-gray-600" 
                        placeholder="Username Anda">
                </div>
                
                <div>
                    <label class="block text-[10px] font-black text-gray-500 mb-2 ml-1 tracking-[0.2em]">PASSWORD</label>
                    <input type="password" name="password" required 
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 focus:outline-none focus:border-blue-500 focus:bg-white/10 transition-all text-sm placeholder:text-gray-600" 
                        placeholder="••••••••">
                </div>

                <button type="submit" name="login"
                        class="w-full bg-blue-600 hover:bg-blue-700 py-4 rounded-2xl font-bold text-sm tracking-widest transition-all transform hover:scale-[1.02] mt-4 shadow-lg shadow-blue-600/30">
                    MASUK KE AKUN
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-white/5 flex flex-col gap-3 items-center">
                <p class="text-xs text-gray-500">
                    Belum punya akun? <a href="index.php#join" class="text-blue-400 font-bold hover:underline">Daftar Sekarang</a>
                </p>
                <a href="index.php" class="text-[10px] text-gray-600 hover:text-white transition tracking-widest uppercase font-bold">
                    ← Kembali ke Beranda
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 py-8 text-center text-[10px] text-gray-700 tracking-[0.3em] uppercase font-medium">
        Aremania.2026 // Rafarel Adzka Radithya
    </footer>

</body>
</html>