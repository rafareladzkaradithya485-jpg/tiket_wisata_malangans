<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$total_tiket = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tiket"))['total'];
$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) as total FROM tiket WHERE status = 'lunas'"))['total'];
$user_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'user'"))['total'];

$query = "SELECT tiket.*, users.nama FROM tiket 
        JOIN users ON tiket.user_id = users.id 
        ORDER BY tgl_beli DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Wisata_Malang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style_stars.css">
    <style>
        .stat-card {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 15px;
            padding: 20px;
        }
        .table { color: #2c8eff; }
        .table-hover tbody tr:hover { background: rgba(255,255,255,0.05); color: white; }
    </style>
</head>
<body>

    <div id="stars-container">
        <?php for ($i = 0; $i < 40; $i++): $size = rand(1, 2); ?>
            <div class="star" style="left:<?=rand(0,100)?>%; width:<?=$size?>px; height:<?=$size?>px; --duration:<?=rand(15,30)?>s;"></div>
        <?php endfor; ?>
    </div>

    <!-- Top Navbar -->
    <nav class="navbar navbar-dark bg-dark bg-opacity-50 border-bottom border-secondary mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">ADMIN PANEL</a>
            <div class="d-flex align-items-center">
                <span class="text-secondary me-3 small">Halo, <?= $_SESSION['nama']; ?></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Statistik Section -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <small class="text-secondary d-block">Total Pendapatan</small>
                    <h3 class="text-success fw-bold"><?= rupiah($total_pendapatan ?? 0); ?></h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <small class="text-secondary d-block">Tiket Terjual</small>
                    <h3 class="text-accent fw-bold"><?= $total_tiket; ?> Tiket</h3>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <small class="text-secondary d-block">User Terdaftar</small>
                    <h3 class="text-white fw-bold"><?= $user_aktif; ?> Orang</h3>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Daftar Transaksi Tiket</h5>
                <button class="btn btn-sm btn-outline-info" onclick="window.location.reload()">Refresh Data</button>
            </div>
            
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="text-secondary">
                        <tr>
                            <th>Tanggal</th>
                            <th>Pembeli</th>
                            <th>Destinasi</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><small><?= date('d/m/Y H:i', strtotime($row['tgl_beli'])); ?></small></td>
                            <td class="fw-bold"><?= $row['nama']; ?></td>
                            <td><?= $row['wisata']; ?></td>
                            <td><?= $row['jumlah']; ?></td>
                            <td><span class="text-accent"><?= rupiah($row['total_harga']); ?></span></td>
                            <td>
                                <?php if($row['status'] == 'lunas'): ?>
                                    <span class="badge bg-success-subtle text-success px-3">Lunas</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning px-3">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="text-center py-5">
        <small class="text-secondary">&copy; 2026 Management System - Wisata Malang</small>
    </footer>

</body>
</html>