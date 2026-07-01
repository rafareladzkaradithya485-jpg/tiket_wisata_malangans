<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query_tiket = "SELECT * FROM tiket WHERE user_id = '$user_id' ORDER BY id_tiket DESC LIMIT 3";
$result_tiket = mysqli_query($conn, $query_tiket);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wisata_Malang</title>
    <!-- Menambahkan Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style_stars.css">
    <!-- Google Fonts untuk tampilan modern -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #050b18;
            color: white;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.5rem;
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            border-color: rgba(59, 130, 246, 0.5);
            transform: translateY(-5px);
        }
        .text-accent { color: #3b82f6; }
        .bg-primary { background-color: #3b82f6 !important; }
    </style>
</head>
<body class="min-h-screen">

    <div id="stars-container" class="fixed inset-0 pointer-events-none">
        <?php for ($i = 0; $i < 40; $i++): $size = rand(1, 2); ?>
            <div class="star absolute bg-white rounded-full opacity-40"
                style="left:<?=rand(0,100)?>%; width:<?=$size?>px; height:<?=$size?>px; top:<?=rand(0,100)?>%; --duration:<?=rand(20,40)?>s;"></div>
        <?php endfor; ?>
    </div>

    <!-- Navbar dengan Tailwind utility -->
    <nav class="navbar navbar-expand-lg navbar-dark border-bottom border-white/10 mb-12 relative z-10 bg-black/20 backdrop-blur-sm">
        <div class="container py-2">
            <a class="navbar-brand fw-extrabold tracking-tighter text-2xl" href="#">
                MALANG<span class="text-accent">ANS</span>
            </a>
            <div class="ms-auto text-white text-sm flex items-center gap-3">
                <span class="opacity-70">Halo,</span> 
                <span class="font-bold text-blue-400"><?= $_SESSION['nama']; ?></span>
                <div class="h-4 w-[1px] bg-white/20 mx-2"></div>
                <a href="logout.php" class="text-red-400 hover:text-red-300 font-bold no-underline transition-colors">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container relative z-10">
        <div class="row">
            <!-- Kolom Destinasi -->
            <div class="col-lg-7 mb-8">
                <h4 class="font-black text-2xl mb-8 flex items-center gap-3">
                    <span class="w-2 h-8 bg-blue-500 rounded-full"></span>
                    Pilih Destinasi Wisata
                </h4>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="glass-card h-100 p-6">
                            <div class="bg-blue-500/10 w-12 h-12 rounded-xl flex items-center justify-center mb-4 text-2xl">🎡</div>
                            <h5 class="font-bold text-xl mb-2">Jatim Park</h5>
                            <p class="text-sm text-gray-400 leading-relaxed">Wisata edukasi dan bermain keluarga paling populer di Kota Batu.</p>
                            <div class="flex justify-between items-center mt-8 pt-4 border-t border-white/5">
                                <span class="text-blue-400 font-black text-lg"><?= rupiah(100000); ?></span>
                                <a href="pesan.php?wisata=Jatim Park" class="btn btn-sm bg-primary border-0 rounded-pill px-4 py-2 font-bold text-white transition-all hover:scale-105 shadow-lg shadow-blue-500/20">Pesan</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="glass-card h-100 p-6">
                            <div class="bg-orange-500/10 w-12 h-12 rounded-xl flex items-center justify-center mb-4 text-2xl">🌋</div>
                            <h5 class="font-bold text-xl mb-2">Gunung Bromo</h5>
                            <p class="text-sm text-gray-400 leading-relaxed">Eksplorasi lautan pasir dan sunrise yang ikonik di Jawa Timur.</p>
                            <div class="flex justify-between items-center mt-8 pt-4 border-t border-white/5">
                                <span class="text-blue-400 font-black text-lg"><?= rupiah(75000); ?></span>
                                <a href="pesan.php?wisata=Bromo" class="btn btn-sm bg-primary border-0 rounded-pill px-4 py-2 font-bold text-white transition-all hover:scale-105 shadow-lg shadow-blue-500/20">Pesan</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Tiket Saya -->
            <div class="col-lg-5">
                <h4 class="font-black text-2xl mb-8 flex items-center gap-3">
                    <span class="w-2 h-8 bg-blue-500 rounded-full"></span>
                    Tiket Saya
                </h4>
                <div class="glass-card p-6 shadow-2xl">
                    <?php if (mysqli_num_rows($result_tiket) > 0): ?>
                        <div class="list-group list-group-flush bg-transparent">
                            <?php while ($row = mysqli_fetch_assoc($result_tiket)): ?>
                                <div class="list-group-item bg-transparent border-white/5 text-white px-0 py-4 hover:bg-white/5 transition-colors rounded-xl px-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h6 class="mb-1 font-bold text-lg"><?= $row['wisata']; ?></h6>
                                            <div class="flex items-center gap-2 text-xs text-gray-500 uppercase tracking-widest font-semibold">
                                                <span><?= $row['jumlah']; ?> Tiket</span>
                                                <span>•</span>
                                                <span><?= date('d M Y', strtotime($row['tgl_beli'])); ?></span>
                                            </div>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?= $row['status'] == 'lunas' ? 'bg-green-500/20 text-green-400 border border-green-500/20' : 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/20' ?>">
                                            <?= strtoupper($row['status']); ?>
                                        </span>
                                    </div>
                                    <?php if ($row['status'] == 'lunas'): ?>
                                        <div class="mt-4 bg-white p-3 rounded-2xl inline-block shadow-lg mx-auto d-block">
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?= $row['kode_barcode']; ?>" alt="QR" class="w-20 h-20 opacity-90">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <div class="text-center mt-6">
                            <a href="riwayat.php" class="text-blue-400 text-xs font-bold no-underline hover:text-white transition-colors uppercase tracking-widest">Lihat Semua Riwayat →</a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 flex flex-col items-center">
                            <div class="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center text-3xl mb-4">🎫</div>
                            <p class="text-gray-500 text-sm font-medium">Kamu belum memiliki tiket.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-12 mt-12 opacity-40">
        <small class="uppercase tracking-[0.3em] text-[10px] font-bold">&copy; 2026 Wisata Malang — Rafarel Adzka Radithya</small>
    </footer>

</body>
</html>