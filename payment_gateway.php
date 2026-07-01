<?php
/**
 * Payment Gateway Integration (Midtrans/Bank API)
 * Support untuk Midtrans, Bank Transfer, E-Wallet
 */

session_start();
require 'config.php';

// Konfigurasi Midtrans
define('MIDTRANS_SERVER_KEY', getenv('MIDTRANS_SERVER_KEY') ?: 'your_server_key');
define('MIDTRANS_CLIENT_KEY', getenv('MIDTRANS_CLIENT_KEY') ?: 'your_client_key');
define('MIDTRANS_ENVIRONMENT', getenv('MIDTRANS_ENVIRONMENT') ?: 'sandbox');

class PaymentGateway {
    private $conn;
    private $serverKey;
    private $clientKey;
    private $environment;

    public function __construct($connection) {
        $this->conn = $connection;
        $this->serverKey = MIDTRANS_SERVER_KEY;
        $this->clientKey = MIDTRANS_CLIENT_KEY;
        $this->environment = MIDTRANS_ENVIRONMENT;
    }

    /**
     * Inisiasi transaksi pembayaran
     */
    public function initiate_payment($tiket_id, $user_id, $jumlah, $payment_method = 'midtrans') {
        // Validasi input
        if (!$tiket_id || !$user_id || !$jumlah) {
            return ['status' => false, 'message' => 'Data tidak lengkap'];
        }

        // Generate transaction ID
        $transaction_id = 'TRX-' . time() . '-' . rand(1000, 9999);

        // Insert ke tabel payments
        $query = "INSERT INTO payments (id_tiket, user_id, jumlah, payment_method, payment_status, transaction_id)
                  VALUES (?, ?, ?, ?, 'pending', ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iiiss', $tiket_id, $user_id, $jumlah, $payment_method, $transaction_id);

        if ($stmt->execute()) {
            $payment_id = $stmt->insert_id;

            // Jika menggunakan Midtrans
            if ($payment_method === 'midtrans') {
                return $this->create_midtrans_transaction($payment_id, $transaction_id, $jumlah, $user_id);
            }

            // Jika Bank Transfer Manual
            if ($payment_method === 'bank_transfer') {
                return $this->generate_bank_account($payment_id, $transaction_id, $jumlah);
            }

            return ['status' => true, 'message' => 'Pembayaran berhasil disiapkan', 'payment_id' => $payment_id];
        }

        return ['status' => false, 'message' => 'Gagal membuat pembayaran'];
    }

    /**
     * Buat transaksi Midtrans
     */
    private function create_midtrans_transaction($payment_id, $transaction_id, $jumlah, $user_id) {
        // Get user info
        $user_query = "SELECT nama, username FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($user_query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $user_result = $stmt->get_result()->fetch_assoc();

        $payload = [
            'transaction_details' => [
                'order_id' => $transaction_id,
                'gross_amount' => (int)$jumlah,
            ],
            'customer_details' => [
                'first_name' => $user_result['nama'] ?? 'Customer',
                'email' => $user_result['username'] . '@wisatamalang.com',
                'phone' => '08123456789'
            ],
            'enabled_payments' => [
                'credit_card',
                'bca',
                'bni',
                'mandiri',
                'echannel',
                'gopay',
                'ovo'
            ],
            'vt_web' => true
        ];

        // Send ke Midtrans
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://app.' . $this->environment . '.midtrans.com/snap/v1/transactions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($this->serverKey . ':')
            ]
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => false, 'message' => 'Gagal koneksi ke Midtrans'];
        }

        $result = json_decode($response, true);

        if (isset($result['token'])) {
            return [
                'status' => true,
                'message' => 'Silahkan lakukan pembayaran',
                'payment_id' => $payment_id,
                'token' => $result['token'],
                'redirect_url' => $result['redirect_url'] ?? null,
                'transaction_id' => $transaction_id
            ];
        }

        return ['status' => false, 'message' => 'Gagal membuat transaksi Midtrans'];
    }

    /**
     * Generate Virtual Account untuk Bank Transfer
     */
    private function generate_bank_account($payment_id, $transaction_id, $jumlah) {
        $va_number = '1234' . str_pad($payment_id, 10, '0', STR_PAD_LEFT);
        
        return [
            'status' => true,
            'message' => 'Silahkan transfer ke rekening virtual',
            'payment_id' => $payment_id,
            'payment_method' => 'bank_transfer',
            'va_number' => $va_number,
            'bank_name' => 'BCA',
            'jumlah' => $jumlah,
            'transaction_id' => $transaction_id,
            'expire_time' => date('Y-m-d H:i:s', strtotime('+24 hours'))
        ];
    }

    /**
     * Verifikasi pembayaran dari Midtrans webhook
     */
    public function verify_midtrans_notification($notification) {
        $transaction_id = $notification['transaction_id'];
        $status_code = $notification['status_code'];

        // Update payment status
        $payment_status = in_array($status_code, ['200', '201']) ? 'success' : 'failed';

        $query = "UPDATE payments SET payment_status = ? WHERE transaction_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ss', $payment_status, $transaction_id);

        if ($stmt->execute() && $payment_status === 'success') {
            // Update tiket status
            $query2 = "UPDATE tiket SET status = 'confirmed' 
                       WHERE id_tiket = (SELECT id_tiket FROM payments WHERE transaction_id = ?)";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bind_param('s', $transaction_id);
            $stmt2->execute();
        }

        return ['status' => true, 'message' => 'Verifikasi berhasil'];
    }

    /**
     * Check status pembayaran
     */
    public function check_payment_status($payment_id) {
        $query = "SELECT p.*, t.wisata 
                  FROM payments p
                  JOIN tiket t ON p.id_tiket = t.id_tiket
                  WHERE p.id_payment = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $payment_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return $result ?: null;
    }

    /**
     * Get payment history
     */
    public function get_payment_history($user_id, $limit = 10) {
        $query = "SELECT p.*, t.wisata 
                  FROM payments p
                  JOIN tiket t ON p.id_tiket = t.id_tiket
                  WHERE p.user_id = ?
                  ORDER BY p.created_at DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ii', $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

// API Endpoint untuk pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_GET['action'] ?? '';
    $payment = new PaymentGateway($conn);

    if ($action === 'initiate') {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = $payment->initiate_payment(
            $data['tiket_id'] ?? null,
            $_SESSION['user_id'] ?? null,
            $data['jumlah'] ?? null,
            $data['payment_method'] ?? 'midtrans'
        );
        echo json_encode($result);
    }

    if ($action === 'webhook') {
        $notification = json_decode(file_get_contents('php://input'), true);
        $result = $payment->verify_midtrans_notification($notification);
        echo json_encode($result);
    }
}

// API Endpoint untuk get status
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    
    $action = $_GET['action'] ?? '';
    $payment = new PaymentGateway($conn);

    if ($action === 'check' && isset($_GET['payment_id'])) {
        $result = $payment->check_payment_status($_GET['payment_id']);
        echo json_encode($result ?? ['status' => false, 'message' => 'Pembayaran tidak ditemukan']);
    }

    if ($action === 'history') {
        $result = $payment->get_payment_history($_SESSION['user_id'] ?? 0);
        echo json_encode($result);
    }
}
?>
