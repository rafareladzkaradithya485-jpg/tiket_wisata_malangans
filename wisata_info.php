<?php
/**
 * Wisata Information Management
 * Menampilkan dan mengelola informasi wisata
 */

session_start();

require 'config.php';

class WisataManager {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    /**
     * Get semua wisata
     */
    public function get_all_wisata($limit = null) {
        $query = "SELECT * FROM wisata WHERE status_aktif = TRUE ORDER BY rating DESC";
        
        if ($limit) {
            $query .= " LIMIT " . intval($limit);
        }
        
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get detail wisata
     */
    public function get_wisata_detail($id_wisata) {
        $query = "SELECT w.*, 
                        (SELECT COUNT(*) FROM reviews WHERE id_wisata = w.id_wisata) as total_review,
                        (SELECT AVG(rating) FROM reviews WHERE id_wisata = w.id_wisata) as avg_rating
                  FROM wisata w
                  WHERE w.id_wisata = ? AND w.status_aktif = TRUE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id_wisata);
        $stmt->execute();
        $wisata = $stmt->get_result()->fetch_assoc();

        if (!$wisata) {
            return null;
        }

        // Get fasilitas
        $query2 = "SELECT * FROM fasilitas_wisata WHERE id_wisata = ? AND tersedia = TRUE";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bind_param('i', $id_wisata);
        $stmt2->execute();
        $wisata['fasilitas'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

        // Get reviews
        $query3 = "SELECT r.*, u.nama FROM reviews r
                   JOIN users u ON r.user_id = u.id
                   WHERE r.id_wisata = ?
                   ORDER BY r.created_at DESC
                   LIMIT 10";
        $stmt3 = $this->conn->prepare($query3);
        $stmt3->bind_param('i', $id_wisata);
        $stmt3->execute();
        $wisata['reviews'] = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);

        return $wisata;
    }

    /**
     * Search wisata
     */
    public function search_wisata($keyword) {
        $keyword = '%' . $keyword . '%';
        $query = "SELECT * FROM wisata 
                  WHERE (nama_wisata LIKE ? OR deskripsi LIKE ? OR kategori LIKE ? OR lokasi LIKE ?)
                  AND status_aktif = TRUE
                  ORDER BY rating DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssss', $keyword, $keyword, $keyword, $keyword);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filter wisata berdasarkan kategori
     */
    public function filter_by_kategori($kategori) {
        $query = "SELECT * FROM wisata 
                  WHERE kategori = ? AND status_aktif = TRUE
                  ORDER BY rating DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $kategori);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get kategori wisata
     */
    public function get_categories() {
        $query = "SELECT DISTINCT kategori FROM wisata WHERE status_aktif = TRUE ORDER BY kategori ASC";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filter wisata berdasarkan harga
     */
    public function filter_by_price($min_price, $max_price) {
        $query = "SELECT * FROM wisata 
                  WHERE harga_tiket >= ? AND harga_tiket <= ? AND status_aktif = TRUE
                  ORDER BY harga_tiket ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $min_price, $max_price);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Filter wisata berdasarkan rating
     */
    public function filter_by_rating($min_rating) {
        $query = "SELECT * FROM wisata 
                  WHERE rating >= ? AND status_aktif = TRUE
                  ORDER BY rating DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('d', $min_rating);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Tambah review (user)
     */
    public function add_review($id_wisata, $user_id, $rating, $ulasan) {
        // Validasi
        if ($rating < 1 || $rating > 5) {
            return ['status' => false, 'message' => 'Rating harus antara 1-5'];
        }

        // Check jika user sudah pernah review wisata ini
        $query0 = "SELECT id_review FROM reviews WHERE id_wisata = ? AND user_id = ?";
        $stmt0 = $this->conn->prepare($query0);
        $stmt0->bind_param('ii', $id_wisata, $user_id);
        $stmt0->execute();
        
        if ($stmt0->get_result()->num_rows > 0) {
            return ['status' => false, 'message' => 'Anda sudah pernah review wisata ini'];
        }

        // Insert review
        $query = "INSERT INTO reviews (id_wisata, user_id, rating, ulasan) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iis', $id_wisata, $user_id, $rating, $ulasan);

        if ($stmt->execute()) {
            // Update rating di tabel wisata
            $this->update_wisata_rating($id_wisata);
            
            return [
                'status' => true,
                'message' => 'Review berhasil ditambahkan',
                'review_id' => $stmt->insert_id
            ];
        }

        return ['status' => false, 'message' => 'Gagal menambahkan review'];
    }

    /**
     * Update rating wisata
     */
    private function update_wisata_rating($id_wisata) {
        $query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_review FROM reviews WHERE id_wisata = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id_wisata);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $avg_rating = round($result['avg_rating'] ?? 0, 2);
        $total_review = $result['total_review'] ?? 0;

        $query2 = "UPDATE wisata SET rating = ?, total_review = ? WHERE id_wisata = ?";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bind_param('dii', $avg_rating, $total_review, $id_wisata);
        $stmt2->execute();
    }

    /**
     * Get top wisata (rating tertinggi)
     */
    public function get_top_wisata($limit = 5) {
        $query = "SELECT * FROM wisata 
                  WHERE status_aktif = TRUE
                  ORDER BY rating DESC, total_review DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get wisata dengan most reviews
     */
    public function get_trending_wisata($limit = 5) {
        $query = "SELECT * FROM wisata 
                  WHERE status_aktif = TRUE
                  ORDER BY total_review DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get wisata stats
     */
    public function get_wisata_stats() {
        $query = "SELECT 
                    COUNT(*) as total_wisata,
                    AVG(harga_tiket) as avg_harga,
                    MIN(harga_tiket) as min_harga,
                    MAX(harga_tiket) as max_harga,
                    AVG(rating) as avg_rating
                  FROM wisata WHERE status_aktif = TRUE";
        
        $result = $this->conn->query($query);
        return $result->fetch_assoc();
    }

    /**
     * Get nearby wisata (radius)
     */
    public function get_nearby_wisata($lat, $lon, $radius_km = 50) {
        $query = "SELECT *, 
                    (6371 * acos(cos(radians(?)) * cos(radians(lat)) * 
                    cos(radians(lon) - radians(?)) + 
                    sin(radians(?)) * sin(radians(lat)))) AS distance
                  FROM wisata
                  WHERE status_aktif = TRUE
                  HAVING distance < ?
                  ORDER BY distance ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('dddd', $lat, $lon, $lat, $radius_km);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Tambah wisata (admin)
     */
    public function add_wisata($data) {
        $query = "INSERT INTO wisata (nama_wisata, deskripsi, lokasi, harga_tiket, jam_buka, jam_tutup, kategori, gambar_url, lat, lon, no_hp_contact, email_contact)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'sssissssddss',
            $data['nama_wisata'],
            $data['deskripsi'],
            $data['lokasi'],
            $data['harga_tiket'],
            $data['jam_buka'],
            $data['jam_tutup'],
            $data['kategori'],
            $data['gambar_url'],
            $data['lat'],
            $data['lon'],
            $data['no_hp_contact'],
            $data['email_contact']
        );

        if ($stmt->execute()) {
            return [
                'status' => true,
                'message' => 'Wisata berhasil ditambahkan',
                'wisata_id' => $stmt->insert_id
            ];
        }

        return ['status' => false, 'message' => 'Gagal menambahkan wisata'];
    }

    /**
     * Update wisata (admin)
     */
    public function update_wisata($id_wisata, $data) {
        $query = "UPDATE wisata 
                  SET nama_wisata = ?, deskripsi = ?, lokasi = ?, harga_tiket = ?, 
                      jam_buka = ?, jam_tutup = ?, kategori = ?, gambar_url = ?,
                      lat = ?, lon = ?, no_hp_contact = ?, email_contact = ?
                  WHERE id_wisata = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            'sssisssddsssi',
            $data['nama_wisata'],
            $data['deskripsi'],
            $data['lokasi'],
            $data['harga_tiket'],
            $data['jam_buka'],
            $data['jam_tutup'],
            $data['kategori'],
            $data['gambar_url'],
            $data['lat'],
            $data['lon'],
            $data['no_hp_contact'],
            $data['email_contact'],
            $id_wisata
        );

        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Wisata berhasil diupdate'];
        }

        return ['status' => false, 'message' => 'Gagal update wisata'];
    }

    /**
     * Delete wisata (admin)
     */
    public function delete_wisata($id_wisata) {
        $query = "UPDATE wisata SET status_aktif = FALSE WHERE id_wisata = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id_wisata);

        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Wisata berhasil dihapus'];
        }

        return ['status' => false, 'message' => 'Gagal menghapus wisata'];
    }
}

// API Endpoints
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $wisata = new WisataManager($conn);

    // Get all wisata
    if ($action === 'all') {
        $limit = $_GET['limit'] ?? null;
        $result = $wisata->get_all_wisata($limit);
        echo json_encode($result);
    }

    // Get wisata detail
    if ($action === 'detail' && isset($_GET['id'])) {
        $result = $wisata->get_wisata_detail($_GET['id']);
        echo json_encode($result ?? ['status' => false, 'message' => 'Wisata tidak ditemukan']);
    }

    // Search wisata
    if ($action === 'search' && isset($_GET['q'])) {
        $result = $wisata->search_wisata($_GET['q']);
        echo json_encode($result);
    }

    // Filter by kategori
    if ($action === 'filter_kategori' && isset($_GET['kategori'])) {
        $result = $wisata->filter_by_kategori($_GET['kategori']);
        echo json_encode($result);
    }

    // Get categories
    if ($action === 'categories') {
        $result = $wisata->get_categories();
        echo json_encode($result);
    }

    // Filter by price
    if ($action === 'filter_price' && isset($_GET['min']) && isset($_GET['max'])) {
        $result = $wisata->filter_by_price($_GET['min'], $_GET['max']);
        echo json_encode($result);
    }

    // Filter by rating
    if ($action === 'filter_rating' && isset($_GET['min_rating'])) {
        $result = $wisata->filter_by_rating($_GET['min_rating']);
        echo json_encode($result);
    }

    // Top wisata
    if ($action === 'top') {
        $limit = $_GET['limit'] ?? 5;
        $result = $wisata->get_top_wisata($limit);
        echo json_encode($result);
    }

    // Trending wisata
    if ($action === 'trending') {
        $limit = $_GET['limit'] ?? 5;
        $result = $wisata->get_trending_wisata($limit);
        echo json_encode($result);
    }

    // Wisata stats
    if ($action === 'stats') {
        $result = $wisata->get_wisata_stats();
        echo json_encode($result);
    }

    // Nearby wisata
    if ($action === 'nearby' && isset($_GET['lat']) && isset($_GET['lon'])) {
        $radius = $_GET['radius'] ?? 50;
        $result = $wisata->get_nearby_wisata($_GET['lat'], $_GET['lon'], $radius);
        echo json_encode($result);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    $data = json_decode(file_get_contents('php://input'), true);
    $wisata = new WisataManager($conn);

    // Add review
    if ($action === 'add_review') {
        $result = $wisata->add_review(
            $data['id_wisata'] ?? null,
            $_SESSION['user_id'] ?? null,
            $data['rating'] ?? null,
            $data['ulasan'] ?? ''
        );
        echo json_encode($result);
    }

    // Add wisata (admin only)
    if ($action === 'add' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $result = $wisata->add_wisata($data);
        echo json_encode($result);
    }

    // Update wisata (admin only)
    if ($action === 'update' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $result = $wisata->update_wisata($data['id_wisata'] ?? null, $data);
        echo json_encode($result);
    }

    // Delete wisata (admin only)
    if ($action === 'delete' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $result = $wisata->delete_wisata($data['id_wisata'] ?? null);
        echo json_encode($result);
    }
}
?>
