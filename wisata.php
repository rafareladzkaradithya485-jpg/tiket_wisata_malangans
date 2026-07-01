<?php
/**
 * Tampilan Halaman Informasi Wisata
 */

session_start();

require 'config.php';
require 'wisata_info.php';

$wisata_manager = new WisataManager($conn);

// Get action
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Wisata - Tiket Malang</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .filter-section {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .filter-section input,
        .filter-section select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .filter-section button {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .filter-section button:hover {
            background: #764ba2;
        }

        .wisata-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .wisata-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .wisata-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .wisata-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            overflow: hidden;
        }

        .wisata-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .wisata-content {
            padding: 15px;
        }

        .wisata-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .wisata-location {
            font-size: 13px;
            color: #666;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .wisata-price {
            font-size: 16px;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .wisata-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 10px;
        }

        .stars {
            color: #ffc107;
        }

        .view-detail-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
        }

        .view-detail-btn:hover {
            background: #764ba2;
        }

        .detail-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .detail-header {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .detail-image {
            flex: 1;
            min-width: 300px;
            height: 400px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
        }

        .detail-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .detail-info {
            flex: 1;
            min-width: 300px;
        }

        .detail-info h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 15px;
        }

        .detail-info-item {
            display: flex;
            margin-bottom: 15px;
            gap: 15px;
        }

        .detail-info-label {
            font-weight: bold;
            color: #667eea;
            min-width: 120px;
        }

        .detail-info-value {
            color: #666;
        }

        .fasilitas-section {
            margin-top: 30px;
        }

        .fasilitas-section h3 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .fasilitas-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .fasilitas-item {
            background: #f5f5f5;
            padding: 12px 15px;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }

        .reviews-section {
            margin-top: 30px;
        }

        .reviews-section h3 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .review-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #ffc107;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .review-author {
            font-weight: bold;
            color: #333;
        }

        .review-date {
            font-size: 12px;
            color: #999;
        }

        .review-rating {
            color: #ffc107;
            margin-bottom: 8px;
        }

        .review-text {
            color: #666;
            line-height: 1.5;
        }

        .add-review-form {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .submit-btn {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }

        .submit-btn:hover {
            background: #764ba2;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background: #764ba2;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            margin-top: 8px;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 10px;
        }

        .empty-state h2 {
            color: #999;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🏖️ Informasi Wisata Malang</h1>
            <p>Jelajahi destinasi wisata terbaik di Jawa Timur</p>
        </header>

        <?php if ($action === 'list'): ?>
            <!-- List Wisata -->
            <div class="detail-section">
                <div class="filter-section">
                    <input type="text" id="searchInput" placeholder="Cari wisata..." />
                    <select id="categorySelect">
                        <option value="">Semua Kategori</option>
                        <?php 
                        $categories = $wisata_manager->get_categories();
                        foreach ($categories as $cat): 
                        ?>
                        <option value="<?php echo $cat['kategori']; ?>"><?php echo $cat['kategori']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button onclick="searchWisata()">Cari</button>
                    <button onclick="showTopWisata()">Rating Tertinggi</button>
                    <button onclick="showTrendingWisata()">Trending</button>
                </div>
            </div>

            <?php
            $all_wisata = $wisata_manager->get_all_wisata();
            ?>

            <div class="wisata-grid">
                <?php if (empty($all_wisata)): ?>
                    <div class="empty-state" style="grid-column: 1/-1;">
                        <h2>Tidak ada wisata yang ditemukan</h2>
                        <p>Coba lagi dengan filter berbeda</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($all_wisata as $w): ?>
                    <div class="wisata-card" onclick="window.location.href='?action=detail&id=<?php echo $w['id_wisata']; ?>'">
                        <div class="wisata-image">
                            <?php if ($w['gambar_url']): ?>
                                <img src="<?php echo $w['gambar_url']; ?>" alt="<?php echo $w['nama_wisata']; ?>">
                            <?php else: ?>
                                📸 <?php echo $w['nama_wisata']; ?>
                            <?php endif; ?>
                        </div>
                        <div class="wisata-content">
                            <div class="wisata-name"><?php echo $w['nama_wisata']; ?></div>
                            <div class="wisata-location">📍 <?php echo substr($w['lokasi'], 0, 30); ?>...</div>
                            <div class="wisata-price">Rp <?php echo number_format($w['harga_tiket'], 0, ',', '.'); ?></div>
                            <div class="wisata-rating">
                                <span class="stars">★★★★★</span>
                                <span><?php echo number_format($w['rating'], 1); ?> (<?php echo $w['total_review']; ?> review)</span>
                            </div>
                            <button class="view-detail-btn">Lihat Detail</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php elseif ($action === 'detail' && $id): ?>
            <!-- Detail Wisata -->
            <a href="?" class="back-btn">← Kembali</a>

            <?php
            $wisata = $wisata_manager->get_wisata_detail($id);
            
            if (!$wisata):
            ?>
            <div class="empty-state">
                <h2>Wisata tidak ditemukan</h2>
                <p><a href="?" class="back-btn">Kembali ke Daftar Wisata</a></p>
            </div>
            <?php else: ?>

            <div class="detail-section">
                <div class="detail-header">
                    <div class="detail-image">
                        <?php if ($wisata['gambar_url']): ?>
                            <img src="<?php echo $wisata['gambar_url']; ?>" alt="<?php echo $wisata['nama_wisata']; ?>">
                        <?php else: ?>
                            📸
                        <?php endif; ?>
                    </div>
                    <div class="detail-info">
                        <h2><?php echo $wisata['nama_wisata']; ?></h2>
                        
                        <div class="detail-info-item">
                            <span class="detail-info-label">Lokasi:</span>
                            <span class="detail-info-value">📍 <?php echo $wisata['lokasi']; ?></span>
                        </div>

                        <div class="detail-info-item">
                            <span class="detail-info-label">Harga Tiket:</span>
                            <span class="detail-info-value">Rp <?php echo number_format($wisata['harga_tiket'], 0, ',', '.'); ?></span>
                        </div>

                        <div class="detail-info-item">
                            <span class="detail-info-label">Jam Operasional:</span>
                            <span class="detail-info-value">
                                <?php echo ($wisata['jam_buka'] ?? '09:00'); ?> - <?php echo ($wisata['jam_tutup'] ?? '17:00'); ?>
                            </span>
                        </div>

                        <div class="detail-info-item">
                            <span class="detail-info-label">Kategori:</span>
                            <span class="detail-info-value"><?php echo $wisata['kategori']; ?></span>
                        </div>

                        <div class="detail-info-item">
                            <span class="detail-info-label">Rating:</span>
                            <span class="detail-info-value">
                                ⭐ <?php echo number_format($wisata['rating'], 1); ?>/5 
                                (<?php echo $wisata['total_review'] ?? 0; ?> review)
                            </span>
                        </div>

                        <div class="detail-info-item">
                            <span class="detail-info-label">Kontak:</span>
                            <span class="detail-info-value">
                                📱 <?php echo $wisata['no_hp_contact'] ?? 'N/A'; ?> 
                                | 📧 <?php echo $wisata['email_contact'] ?? 'N/A'; ?>
                            </span>
                        </div>

                        <button class="submit-btn" onclick="alert('Fitur booking akan segera tersedia')">
                            🎫 Pesan Tiket Sekarang
                        </button>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #eee;">
                    <h3 style="color: #333; margin-bottom: 15px;">📝 Tentang Wisata Ini</h3>
                    <p style="color: #666; line-height: 1.8;">
                        <?php echo nl2br($wisata['deskripsi'] ?? 'Tidak ada deskripsi'); ?>
                    </p>
                </div>

                <!-- Fasilitas -->
                <?php if (!empty($wisata['fasilitas'])): ?>
                <div class="fasilitas-section">
                    <h3>🎪 Fasilitas Tersedia</h3>
                    <div class="fasilitas-list">
                        <?php foreach ($wisata['fasilitas'] as $f): ?>
                        <div class="fasilitas-item">
                            <strong><?php echo $f['nama_fasilitas']; ?></strong>
                            <p style="font-size: 13px; color: #999; margin-top: 5px;">
                                <?php echo $f['keterangan']; ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Reviews -->
                <div class="reviews-section">
                    <h3>💬 Ulasan Pengunjung</h3>

                    <?php if (!empty($wisata['reviews'])): ?>
                    <div>
                        <?php foreach ($wisata['reviews'] as $r): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <span class="review-author"><?php echo $r['nama']; ?></span>
                                <span class="review-date"><?php echo date('d M Y', strtotime($r['created_at'])); ?></span>
                            </div>
                            <div class="review-rating">
                                <?php echo str_repeat('⭐', $r['rating']); ?> <?php echo $r['rating']; ?>/5
                            </div>
                            <div class="review-text"><?php echo $r['ulasan']; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p style="color: #999;">Belum ada review untuk wisata ini</p>
                    <?php endif; ?>

                    <!-- Form Tambah Review -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="add-review-form">
                        <h4>Tambahkan Review Anda</h4>
                        <form onsubmit="submitReview(event, <?php echo $id; ?>)">
                            <div class="form-group">
                                <label>Rating</label>
                                <select name="rating" required>
                                    <option value="">Pilih Rating</option>
                                    <option value="5">⭐⭐⭐⭐⭐ Sangat Bagus</option>
                                    <option value="4">⭐⭐⭐⭐ Bagus</option>
                                    <option value="3">⭐⭐⭐ Cukup</option>
                                    <option value="2">⭐⭐ Kurang</option>
                                    <option value="1">⭐ Sangat Kurang</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Ulasan Anda</label>
                                <textarea name="ulasan" placeholder="Bagikan pengalaman Anda di wisata ini..." required></textarea>
                            </div>
                            <button type="submit" class="submit-btn">Kirim Review</button>
                        </form>
                    </div>
                    <?php else: ?>
                    <p style="color: #999; margin-top: 20px;">
                        <a href="login.php">Login</a> untuk menambahkan review
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <?php endif; ?>

        <?php endif; ?>
    </div>

    <script>
        function searchWisata() {
            const search = document.getElementById('searchInput').value;
            const category = document.getElementById('categorySelect').value;
            
            if (search) {
                window.location.href = `wisata_info.php?action=search&q=${encodeURIComponent(search)}`;
            } else if (category) {
                window.location.href = `wisata_info.php?action=filter_kategori&kategori=${encodeURIComponent(category)}`;
            }
        }

        function showTopWisata() {
            window.location.href = '?action=top';
        }

        function showTrendingWisata() {
            window.location.href = '?action=trending';
        }

        function submitReview(e, wisataId) {
            e.preventDefault();
            const form = e.target;
            const rating = form.rating.value;
            const ulasan = form.ulasan.value;

            fetch('wisata_info.php?action=add_review', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_wisata: wisataId,
                    rating: rating,
                    ulasan: ulasan
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status) {
                    alert('Review berhasil ditambahkan!');
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    </script>
</body>
</html>
