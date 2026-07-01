<?php
/**
 * Refund Management System
 * Mengelola proses pengembalian dana
 */

session_start();
require 'config.php';

class RefundManager {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    /**
     * Buat request refund
     */
    public function create_refund_request($tiket_id, $user_id, $alasan) {
        // Validasi
        if (!$tiket_id || !$user_id || !$alasan) {
            return ['status' => false, 'message' => 'Data tidak lengkap'];
        }

        // Get payment info
        $query = "SELECT p.id_payment, p.jumlah, p.payment_status 
                  FROM payments p
                  WHERE p.id_tiket = ? AND p.user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $tiket_id, $user_id);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();

        if (!$payment) {
            return ['status' => false, 'message' => 'Pembayaran tidak ditemukan'];
        }

        if ($payment['payment_status'] !== 'success') {
            return ['status' => false, 'message' => 'Hanya pembayaran yang berhasil bisa di-refund'];
        }

        // Create refund request
        $query2 = "INSERT INTO refunds (id_payment, id_tiket, user_id, jumlah_refund, alasan, status)
                   VALUES (?, ?, ?, ?, ?, 'pending')";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bind_param('iiiis', 
            $payment['id_payment'], 
            $tiket_id, 
            $user_id, 
            $payment['jumlah'],
            $alasan
        );

        if ($stmt2->execute()) {
            $refund_id = $stmt2->insert_id;
            return [
                'status' => true, 
                'message' => 'Request refund berhasil dibuat',
                'refund_id' => $refund_id,
                'jumlah' => $payment['jumlah']
            ];
        }

        return ['status' => false, 'message' => 'Gagal membuat request refund'];
    }

    /**
     * Approve refund (admin only)
     */
    public function approve_refund($refund_id, $approved_by = null) {
        $query = "UPDATE refunds 
                  SET status = 'approved', approved_date = NOW()
                  WHERE id_refund = ? AND status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $refund_id);

        if ($stmt->execute()) {
            // Process refund ke payment gateway
            $this->process_refund_payment($refund_id);
            return ['status' => true, 'message' => 'Refund berhasil disetujui'];
        }

        return ['status' => false, 'message' => 'Gagal approve refund'];
    }

    /**
     * Reject refund (admin only)
     */
    public function reject_refund($refund_id, $reason = '') {
        $query = "UPDATE refunds 
                  SET status = 'rejected', approved_date = NOW()
                  WHERE id_refund = ? AND status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $refund_id);

        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'Refund berhasil ditolak'];
        }

        return ['status' => false, 'message' => 'Gagal reject refund'];
    }

    /**
     * Process refund payment (integrasi dengan payment gateway)
     */
    private function process_refund_payment($refund_id) {
        // Get refund info
        $query = "SELECT r.*, p.transaction_id 
                  FROM refunds r
                  JOIN payments p ON r.id_payment = p.id_payment
                  WHERE r.id_refund = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $refund_id);
        $stmt->execute();
        $refund = $stmt->get_result()->fetch_assoc();

        if (!$refund) return;

        // Proses ke Midtrans jika ada transaction_id
        if ($refund['transaction_id']) {
            $this->refund_via_midtrans($refund);
        }

        // Update refund status to completed
        $query2 = "UPDATE refunds 
                   SET status = 'completed', completed_date = NOW()
                   WHERE id_refund = ?";
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bind_param('i', $refund_id);
        $stmt2->execute();
    }

    /**
     * Refund via Midtrans
     */
    private function refund_via_midtrans($refund) {
        $serverKey = getenv('MIDTRANS_SERVER_KEY') ?: '';
        $environment = getenv('MIDTRANS_ENVIRONMENT') ?: 'sandbox';

        $payload = [
            'refund_key' => 'refund-' . $refund['id_refund'] . '-' . time(),
            'amount' => (int)$refund['jumlah_refund']
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://app.' . $environment . '.midtrans.com/v2/' . $refund['transaction_id'] . '/refund',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode($serverKey . ':')
            ]
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Get refund request
     */
    public function get_refund_request($refund_id) {
        $query = "SELECT r.*, u.nama, u.username, t.wisata 
                  FROM refunds r
                  JOIN users u ON r.user_id = u.id
                  JOIN tiket t ON r.id_tiket = t.id_tiket
                  WHERE r.id_refund = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $refund_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get all refund requests (admin)
     */
    public function get_all_refund_requests($status = null, $limit = 20) {
        $query = "SELECT r.*, u.nama, u.username, t.wisata 
                  FROM refunds r
                  JOIN users u ON r.user_id = u.id
                  JOIN tiket t ON r.id_tiket = t.id_tiket";
        
        if ($status) {
            $query .= " WHERE r.status = ?";
        }
        
        $query .= " ORDER BY r.request_date DESC LIMIT ?";

        $stmt = $this->conn->prepare($query);
        
        if ($status) {
            $stmt->bind_param('si', $status, $limit);
        } else {
            $stmt->bind_param('i', $limit);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get user refund history
     */
    public function get_user_refund_history($user_id) {
        $query = "SELECT r.*, t.wisata 
                  FROM refunds r
                  JOIN tiket t ON r.id_tiket = t.id_tiket
                  WHERE r.user_id = ?
                  ORDER BY r.request_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get refund stats
     */
    public function get_refund_statistics() {
        $query = "SELECT 
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = 'completed' THEN jumlah_refund ELSE 0 END) as total_refunded
                  FROM refunds";
        
        $result = $this->conn->query($query);
        return $result->fetch_assoc();
    }
}

// API Endpoints
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_GET['action'] ?? '';
    $refund = new RefundManager($conn);

    if ($action === 'create_request') {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $refund->create_refund_request(
            $data['tiket_id'] ?? null,
            $_SESSION['user_id'] ?? null,
            $data['alasan'] ?? ''
        );
        echo json_encode($result);
    }

    if ($action === 'approve' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $refund->approve_refund($data['refund_id'] ?? null, $_SESSION['user_id'] ?? null);
        echo json_encode($result);
    }

    if ($action === 'reject' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $refund->reject_refund($data['refund_id'] ?? null, $data['reason'] ?? '');
        echo json_encode($result);
    }
}

// GET endpoints
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    
    $action = $_GET['action'] ?? '';
    $refund = new RefundManager($conn);

    if ($action === 'history') {
        $result = $refund->get_user_refund_history($_SESSION['user_id'] ?? 0);
        echo json_encode($result);
    }

    if ($action === 'stats' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $result = $refund->get_refund_statistics();
        echo json_encode($result);
    }

    if ($action === 'all_requests' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $status = $_GET['status'] ?? null;
        $result = $refund->get_all_refund_requests($status);
        echo json_encode($result);
    }
}
?>
