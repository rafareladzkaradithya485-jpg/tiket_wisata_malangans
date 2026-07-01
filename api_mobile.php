<?php
/**
 * RESTful API untuk Mobile App (Android/iOS)
 * Endpoint untuk akses semua fitur dari aplikasi mobile
 */

session_start();
require 'config.php';
require 'payment_gateway.php';
require 'refund_handler.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// API Authentication
function verify_api_token() {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';
    
    if (strpos($token, 'Bearer ') !== 0) {
        http_response_code(401);
        return null;
    }

    $token = substr($token, 7);
    
    // Validasi token (bisa disimpan di database)
    // Untuk sekarang, gunakan session-based atau JWT
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }

    return null;
}

// Request method dan action
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', trim($path, '/'));

// API route handler
class MobileAPI {
    private $conn;
    private $user_id;

    public function __construct($connection, $user_id) {
        $this->conn = $connection;
        $this->user_id = $user_id;
    }

    // AUTH ENDPOINTS
    public function login($username, $password) {
        $query = "SELECT id, nama, username, role FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            return ['status' => false, 'message' => 'Username tidak ditemukan'];
        }

        // Validasi password
        $query2 = "SELECT password FROM users WHERE id = ?";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bind_param('i', $user['id']);
        $stmt2->execute();
        $pwd_data = $stmt2->get_result()->fetch_assoc();

        if (!password_verify($password, $pwd_data['password'])) {
            return ['status' => false, 'message' => 'Password salah'];
        }

        // Generate API token (bisa menggunakan JWT)
        $token = bin2hex(random_bytes(32));

        return [
            'status' => true,
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token
        ];
    }

    public function register($nama, $username, $password) {
        // Validasi
        if (strlen($password) < 6) {
            return ['status' => false, 'message' => 'Password minimal 6 karakter'];
        }

        // Check username
        $query = "SELECT id FROM users WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $username);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            return ['status' => false, 'message' => 'Username sudah terdaftar'];
        }

        // Hash password
        $hashed_pwd = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $query2 = "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, 'user')";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bind_param('sss', $nama, $username, $hashed_pwd);

        if ($stmt2->execute()) {
            return [
                'status' => true,
                'message' => 'Registrasi berhasil',
                'user_id' => $stmt2->insert_id
            ];
        }

        return ['status' => false, 'message' => 'Registrasi gagal'];
    }

    // TIKET ENDPOINTS
    public function get_tikets() {
        $query = "SELECT id_tiket, wisata, jumlah, total_harga, status, tgl_beli 
                  FROM tiket WHERE user_id = ? ORDER BY tgl_beli DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->user_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function get_tiket_detail($tiket_id) {
        $query = "SELECT t.*, u.nama, u.username 
                  FROM tiket t
                  JOIN users u ON t.user_id = u.id
                  WHERE t.id_tiket = ? AND t.user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $tiket_id, $this->user_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    public function create_tiket($wisata, $jumlah, $total_harga) {
        $barcode = 'BC-' . time() . '-' . rand(1000, 9999);

        $query = "INSERT INTO tiket (user_id, wisata, jumlah, total_harga, tgl_beli, kode_barcode, status)
                  VALUES (?, ?, ?, ?, NOW(), ?, 'pending')";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('isii', $this->user_id, $wisata, $jumlah, $total_harga);

        if ($stmt->execute()) {
            return [
                'status' => true,
                'message' => 'Tiket berhasil dibuat',
                'tiket_id' => $stmt->insert_id,
                'barcode' => $barcode
            ];
        }

        return ['status' => false, 'message' => 'Gagal membuat tiket'];
    }

    // PAYMENT ENDPOINTS
    public function initiate_payment($tiket_id, $jumlah, $payment_method = 'midtrans') {
        $payment = new PaymentGateway($this->conn);
        return $payment->initiate_payment($tiket_id, $this->user_id, $jumlah, $payment_method);
    }

    public function get_payment_history($limit = 10) {
        $payment = new PaymentGateway($this->conn);
        return $payment->get_payment_history($this->user_id, $limit);
    }

    // REFUND ENDPOINTS
    public function create_refund($tiket_id, $alasan) {
        $refund = new RefundManager($this->conn);
        return $refund->create_refund_request($tiket_id, $this->user_id, $alasan);
    }

    public function get_refund_history() {
        $refund = new RefundManager($this->conn);
        return $refund->get_user_refund_history($this->user_id);
    }

    // PROFILE ENDPOINTS
    public function get_profile() {
        $query = "SELECT id, nama, username, role, created_at FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->user_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    public function update_profile($nama) {
        $query = "UPDATE users SET nama = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('si', $nama, $this->user_id);

        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Profil berhasil diupdate'];
        }

        return ['status' => false, 'message' => 'Gagal update profil'];
    }

    // WISHLIST/FAVORITE ENDPOINTS
    public function save_favorite($wisata_name) {
        // Bisa disimpan di cookie atau local storage di mobile
        // Atau bisa membuat tabel favorites di database
        return ['status' => true, 'message' => 'Berhasil disimpan'];
    }

    // SEARCH & FILTER
    public function search_wisata($keyword) {
        $keyword = '%' . $keyword . '%';
        $query = "SELECT DISTINCT wisata FROM tiket WHERE wisata LIKE ? LIMIT 20";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $keyword);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// Route handler
try {
    $action = $parts[1] ?? '';
    $subaction = $parts[2] ?? '';

    // Public endpoints (no auth required)
    if ($action === 'api') {
        if ($method === 'POST' && $subaction === 'auth') {
            $data = json_decode(file_get_contents('php://input'), true);
            $authAction = $data['action'] ?? '';

            $api = new MobileAPI($conn, null);

            if ($authAction === 'login') {
                echo json_encode($api->login($data['username'] ?? '', $data['password'] ?? ''));
            } else if ($authAction === 'register') {
                echo json_encode($api->register(
                    $data['nama'] ?? '',
                    $data['username'] ?? '',
                    $data['password'] ?? ''
                ));
            }
        }
        return;
    }

    // Protected endpoints (auth required)
    $user_id = verify_api_token();
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['status' => false, 'message' => 'Unauthorized']);
        return;
    }

    $api = new MobileAPI($conn, $user_id);

    // Route to appropriate endpoint
    if ($action === 'tiket') {
        if ($method === 'GET') {
            if ($subaction === 'detail' && isset($_GET['id'])) {
                echo json_encode($api->get_tiket_detail($_GET['id']));
            } else {
                echo json_encode($api->get_tikets());
            }
        } else if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($api->create_tiket(
                $data['wisata'] ?? '',
                $data['jumlah'] ?? 1,
                $data['total_harga'] ?? 0
            ));
        }
    }

    if ($action === 'payment') {
        if ($method === 'GET' && $subaction === 'history') {
            echo json_encode($api->get_payment_history());
        } else if ($method === 'POST' && $subaction === 'initiate') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($api->initiate_payment(
                $data['tiket_id'] ?? null,
                $data['jumlah'] ?? 0,
                $data['payment_method'] ?? 'midtrans'
            ));
        }
    }

    if ($action === 'refund') {
        if ($method === 'GET' && $subaction === 'history') {
            echo json_encode($api->get_refund_history());
        } else if ($method === 'POST' && $subaction === 'create') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($api->create_refund(
                $data['tiket_id'] ?? null,
                $data['alasan'] ?? ''
            ));
        }
    }

    if ($action === 'profile') {
        if ($method === 'GET') {
            echo json_encode($api->get_profile());
        } else if ($method === 'PUT') {
            $data = json_decode(file_get_contents('php://input'), true);
            echo json_encode($api->update_profile($data['nama'] ?? ''));
        }
    }

    if ($action === 'search' && $method === 'GET') {
        echo json_encode($api->search_wisata($_GET['q'] ?? ''));
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
