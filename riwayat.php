<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: dashboard_user.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'];

$query = "SELECT * FROM tiket WHERE user_id = '$user_id' ORDER BY tgl_beli DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Tiket - Wisata_Malang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style_stars.css">
    <style>
        .card-ticket {
            border-left: 5px solid #3b82f6;
            transition: 0.3s;
        }
        .card-ticket:hover {
            transform: scale(1.02);
        }
        .qr-box {
            background: white;
            padding: 10px;
            border-radius: 10px;
            display: inline-block;
        }
    </style>
</head>
<body>

    <div id="stars-container">
        <?php for ($i = 0; $i < 40; $i++): $size = rand(1, 2); ?>
            <div class="star" style="left:<?=rand(0,100)?>%; width:<?=$size?>px; height:<?=$size?>px; --duration:<?=rand(20,40)?>s;"></div>
        <?php endfor; ?>
    </div>

    <div class="container py-5">
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="glass-card">
                    <h5 class="fw-bold text-accent">Menu Utama</h5>
                    <hr class="border-secondary">
                    <a href="dashboard_user.php" class="d-block text-secondary text-decoration-none mb-3">🏠 Beranda</a>
                    <a href="riwayat.php" class="d-block text-white fw-bold text-decoration-none mb-3">📜 Riwayat Tiket</a>
                    <a href="logout.php" class="d-block text-danger text-decoration-none">🚪 Logout</a>
                </div>
            </div>

            <div class="col-md-9">
                <h2 class="fw-bold mb-4">Riwayat Pembelian</h2>

                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <div class="glass-card mb-3 card-ticket">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="fw-bold text-white mb-1"><?= $row['wisata']; ?></h4>
                                    <p class="text-secondary small mb-2">📅 <?= date('d M Y, H:i', strtotime($row['tgl_beli'])); ?></p>
                                    <p class="mb-0">
                                        Jumlah: <span class="text-white"><?= $row['jumlah']; ?> Tiket</span> | 
                                        Total: <span class="text-accent fw-bold"><?= rupiah($row['total_harga']); ?></span>
                                    </p>
                                </div>
                                
                                <div class="col-md-4 text-center text-md-end mt-3 mt-md-0">
                                    <?php if ($row['status'] == 'lunas'): ?>
                                        <span class="badge bg-success px-3 py-2 mb-2">LUNAS</span><br>
                                        <div class="qr-box mt-2">
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= $row['kode_barcode']; ?>" alt="QR Code">
                                        </div>
                                        <p class="small mt-2 text-uppercase fw-bold text-white mb-0"><?= $row['kode_barcode']; ?></p>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark px-3 py-2 mb-2">PENDING</span><br>
                                        <p class="small text-secondary mb-2">Silakan selesaikan pembayaran.</p>
                                        <a href="bayar.php?id=<?= $row['id_tiket']; ?>" class="btn btn-sm btn-primary">Bayar Sekarang</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="glass-card text-center py-5">
                        <p class="text-secondary">Belum ada riwayat pemesanan.</p>
                        <a href="dashboard_user.php" class="btn btn-outline-primary">Pesan Tiket Pertama Kamu</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>