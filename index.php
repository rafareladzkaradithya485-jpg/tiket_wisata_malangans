<?php
if (file_exists('config.php')) {
    require_once 'config.php';
} else {
    die("Error: File config.php tidak ditemukan!");
}

$error = "";
if (isset($_POST['register'])) {
    $nama     = input_bersih($_POST['nama']); 
    $username = input_bersih($_POST['username']);
    $password = $_POST['password'];
    
    $password_aman = password_hash($password, PASSWORD_DEFAULT);

    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    
    if (mysqli_num_rows($cek_user) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $query = "INSERT INTO users (nama, username, password, role) VALUES ('$nama', '$username', '$password_aman', 'user')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>
                    alert('Pendaftaran Berhasil! Silakan Login.');
                    window.location='login.php';
                </script>";
        } else {
            $error = "Terjadi kesalahan sistem saat mendaftar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wisata_Malang</title>
    
    <!-- Tailwind CSS & Google Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
</head>
<body class="bg-[#050b18] text-white overflow-x-hidden min-h-screen">

    <div id="stars-container" class="fixed inset-0 z-0">
        <?php for ($i = 0; $i < 60; $i++): $size = rand(1, 3); ?>
            <div class="star absolute bg-white rounded-full opacity-40" 
                style="left: <?= rand(0, 100) ?>%; width: <?= $size ?>px; height: <?= $size ?>px; top: <?= rand(0, 100) ?>%; --duration: <?= rand(10, 25) ?>s;">
            </div>
        <?php endfor; ?>
    </div>

    <nav class="relative z-10 p-6">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-extrabold tracking-tighter">MALANG <span class="text-blue-500">ANS</span></h1>
            <div class="space-x-6 text-sm font-semibold">
                <a href="login.php" class="hover:text-blue-400 transition">MASUK</a>
                <a href="#join" class="bg-blue-600 px-5 py-2 rounded-full hover:bg-blue-700 transition">DAFTAR</a>
            </div>
        </div>
    </nav>

    <div class="relative z-10 container mx-auto px-6 py-12 lg:py-24">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            
            <div>
                <span class="bg-blue-500/20 text-blue-400 px-4 py-1 rounded-full text-xs font-bold mb-4 inline-block italic">
                    Explore the Future of Travel
                </span>
                <h2 class="text-5xl lg:text-7xl font-black leading-tight mb-6">
                    Jelajahi Malang <br> dalam <span class="text-blue-500 text-glow">Satu Klik.</span>
                </h2>
                <p class="text-gray-400 text-lg mb-8 max-w-md">
                    Dapatkan akses instan ke destinasi terbaik dengan sistem tiket berbasis QR Code otomatis yang aman dan cepat.
                </p>
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <div class="flex -space-x-2">
                        <div class="w-8 h-8 rounded-full bg-blue-600 border-2 border-[#050b18]"></div>
                        <div class="w-8 h-8 rounded-full bg-blue-400 border-2 border-[#050b18]"></div>
                        <div class="w-8 h-8 rounded-full bg-blue-200 border-2 border-[#050b18]"></div>
                    </div>
                    <span>Bergabung dengan 1,000+ Penjelajah</span>
                </div>
            </div>

            <div id="join" class="glass-card p-8 md:p-10 rounded-[2rem] shadow-2xl relative">
                <h3 class="text-2xl font-bold mb-2">Buat Akun Baru</h3>
                <p class="text-gray-400 text-sm mb-6">Daftar sekarang untuk mulai memesan tiket.</p>

                <?php if ($error != ""): ?>
                    <div class="bg-red-500/10 border border-red-500 text-red-500 text-xs p-3 rounded-lg mb-4 text-center">
                        <?= $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1 ml-1">NAMA LENGKAP</label>
                        <input type="text" name="nama" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 transition text-sm" 
                            placeholder="Contoh: Rafarel Adzka">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1 ml-1">USERNAME</label>
                        <input type="text" name="username" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 transition text-sm" 
                            placeholder="Pilih username unik">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1 ml-1">PASSWORD</label>
                        <input type="password" name="password" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 transition text-sm" 
                            placeholder="••••••••">
                    </div>
                    <button type="submit" name="register"
                            class="w-full bg-blue-600 hover:bg-blue-700 py-4 rounded-xl font-bold text-sm tracking-widest transition transform hover:scale-[1.02] mt-4 shadow-lg shadow-blue-600/30">
                        DAFTAR SEKARANG
                    </button>
                </form>
                <p class="text-center text-xs text-gray-500 mt-6">
                    Sudah punya akun? <a href="login.php" class="text-blue-400 font-bold hover:underline">Masuk di sini</a>
                </p>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-white/5 mt-20 py-10">
        <div class="container mx-auto px-6 text-center text-gray-600 text-xs uppercase tracking-widest">
            &copy; 2026 Aremania Tech | <span class="text-gray-400">Rafarel Adzka Radithya</span>
        </div>
    </footer>

</body>
</html>