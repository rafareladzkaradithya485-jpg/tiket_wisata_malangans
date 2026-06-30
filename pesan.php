<?php
require_once 'config.php';

// Proteksi Halaman
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$wisata_dipilih = isset($_GET['wisata']) ? $_GET['wisata'] : "Destinasi Umum";
$harga_tiket = ($wisata_dipilih == "Jatim Park") ? 100000 : 75000;

if (isset($_POST['bayar'])) {
    $user_id = $_SESSION['id'];
    $wisata = $_POST['wisata'];
    $jumlah = $_POST['jumlah'];
    $total_harga = $jumlah * $harga_tiket;
    $tgl_beli = date('Y-m-d');
    $status = "lunas"; // Set lunas untuk simulasi sederhana
    $kode_barcode = "TIC-" . strtoupper(substr(md5(time()), 0, 8));

    $query = "INSERT INTO tiket (user_id, wisata, jumlah, total_harga, tgl_beli, status, kode_barcode) 
              VALUES ('$user_id', '$wisata', '$jumlah', '$total_harga', '$tgl_beli', '$status', '$kode_barcode')";

    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Pembayaran Berhasil! Tiket Anda telah terbit.');
                window.location.href = 'dashboard_user.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Tiket - Wisata_Malang</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style_stars.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #050b18; color: white; }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div id="stars-container" class="fixed inset-0 pointer-events-none">
        <?php for ($i = 0; $i < 30; $i++): ?>
            <div class="star absolute bg-white rounded-full opacity-40" 
                 style="left:<?=rand(0,100)?>%; width:2px; height:2px; top:<?=rand(0,100)?>%; --duration:<?=rand(10,25)?>s;"></div>
        <?php endfor; ?>
    </div>

    <div class="glass-card w-full max-w-lg p-8 md:p-10 rounded-[2.5rem] relative z-10 shadow-2xl">
        <div class="mb-8">
            <h2 class="text-3xl font-black tracking-tight text-white mb-2">Konfirmasi <span class="text-blue-500">Tiket</span></h2>
            <p class="text-gray-400 text-sm">Selesaikan pembayaran untuk mendapatkan akses masuk.</p>
        </div>

        <form action="" method="POST" class="space-y-6">
            <div class="bg-white/5 p-6 rounded-2xl border border-white/5 space-y-4">
                <div class="flex justify-between items-center border-b border-white/5 pb-3">
                    <span class="text-gray-500 text-xs font-bold uppercase tracking-widest">Destinasi</span>
                    <input type="hidden" name="wisata" value="<?= $wisata_dipilih; ?>">
                    <span class="font-bold text-white"><?= $wisata_dipilih; ?></span>
                </div>
                <div class="flex justify-between items-center border-b border-white/5 pb-3">
                    <span class="text-gray-500 text-xs font-bold uppercase tracking-widest">Harga Satuan</span>
                    <span class="font-bold text-blue-400"><?= rupiah($harga_tiket); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 text-xs font-bold uppercase tracking-widest">Jumlah Tiket</span>
                    <input type="number" name="jumlah" id="jumlah" min="1" value="1" 
                           class="bg-blue-600/20 border border-blue-500/30 text-white text-center w-20 py-1 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold"
                           oninput="updateTotal(this.value)">
                </div>
            </div>

            <div class="flex justify-between items-center px-2">
                <span class="text-gray-400 font-bold">Total Bayar:</span>
                <span id="total-display" class="text-2xl font-black text-white tracking-tighter">
                    <?= rupiah($harga_tiket); ?>
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-4">
                <a href="dashboard_user.php" class="text-center py-4 rounded-2xl font-bold text-xs text-gray-400 hover:text-white border border-white/10 transition-all uppercase tracking-widest">Batal</a>
                <button type="submit" name="bayar" class="bg-blue-600 hover:bg-blue-700 py-4 rounded-2xl font-bold text-xs text-white shadow-lg shadow-blue-600/30 transition-all transform hover:scale-[1.02] uppercase tracking-widest">Bayar Sekarang</button>
            </div>
        </form>
    </div>

    <script>
        const hargaSatuan = <?= $harga_tiket ?>;
        function updateTotal(jumlah) {
            const total = jumlah * hargaSatuan;
            document.getElementById('total-display').innerText = 'Rp ' + total.toLocaleString('id-ID');
        }
    </script>
</body>
</html>