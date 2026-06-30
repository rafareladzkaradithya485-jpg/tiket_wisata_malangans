<?php
/**
 * AI Predictive Analytics Engine
 * Memprediksi tren penjualan, revenue, dan rekomendasi
 */

require 'config.php';

class AnalyticsAI {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    /**
     * Generate analytics untuk hari ini
     */
    public function generate_daily_analytics($date = null) {
        $date = $date ?: date('Y-m-d');
        
        // Get today's data
        $query = "SELECT 
                    COUNT(DISTINCT id_tiket) as total_tiket,
                    SUM(total_harga) as total_revenue,
                    wisata,
                    COUNT(DISTINCT user_id) as visitor_count
                  FROM tiket
                  WHERE DATE(tgl_beli) = ?
                  GROUP BY wisata
                  ORDER BY total_revenue DESC
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $result = $stmt->get_result();

        $total_tiket = 0;
        $total_revenue = 0;
        $wisata_populer = '';
        $visitor_count = 0;

        if ($row = $result->fetch_assoc()) {
            $total_tiket = $row['total_tiket'];
            $total_revenue = $row['total_revenue'] ?? 0;
            $wisata_populer = $row['wisata'];
            $visitor_count = $row['visitor_count'];
        }

        // Predict next day revenue using simple moving average
        $predicted_revenue = $this->predict_revenue($date);
        $trend = $this->analyze_trend($date);

        // Save to analytics table
        $query2 = "INSERT INTO analytics (tanggal, total_tiket_terjual, total_revenue, wisata_terpopuler, visitor_count, predicted_revenue_next_day, trend)
                   VALUES (?, ?, ?, ?, ?, ?, ?)
                   ON DUPLICATE KEY UPDATE
                   total_tiket_terjual = ?, total_revenue = ?, wisata_terpopuler = ?, visitor_count = ?, predicted_revenue_next_day = ?, trend = ?";
        
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->bind_param(
            'siiiisisiiiis',
            $date, $total_tiket, $total_revenue, $wisata_populer, $visitor_count, $predicted_revenue, $trend,
            $total_tiket, $total_revenue, $wisata_populer, $visitor_count, $predicted_revenue, $trend
        );
        $stmt2->execute();

        return [
            'tanggal' => $date,
            'total_tiket' => $total_tiket,
            'total_revenue' => $total_revenue,
            'wisata_populer' => $wisata_populer,
            'pengunjung' => $visitor_count,
            'prediksi_revenue_besok' => $predicted_revenue,
            'trend' => $trend
        ];
    }

    /**
     * Predict revenue untuk hari berikutnya (Moving Average)
     */
    private function predict_revenue($date) {
        $query = "SELECT AVG(total_revenue) as avg_revenue
                  FROM analytics
                  WHERE tanggal < ?
                  ORDER BY tanggal DESC
                  LIMIT 7";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        return round($result['avg_revenue'] ?? 0);
    }

    /**
     * Analisis trend (naik/turun/stabil)
     */
    private function analyze_trend($date) {
        $query = "SELECT 
                    (SELECT total_revenue FROM analytics WHERE tanggal < ? ORDER BY tanggal DESC LIMIT 1) as today,
                    (SELECT total_revenue FROM analytics WHERE tanggal < ? ORDER BY tanggal DESC LIMIT 1 OFFSET 1) as yesterday";
        
        $stmt = $this->conn->prepare($query);
        $yesterday = date('Y-m-d', strtotime($date . ' -1 day'));
        $stmt->bind_param('ss', $date, $yesterday);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        $today = $result['today'] ?? 0;
        $yesterday = $result['yesterday'] ?? 0;

        if ($today > $yesterday) {
            return 'naik';
        } elseif ($today < $yesterday) {
            return 'turun';
        }
        return 'stabil';
    }

    /**
     * Get revenue forecast untuk 7 hari ke depan
     */
    public function get_revenue_forecast($days = 7) {
        $query = "SELECT tanggal, total_revenue, predicted_revenue_next_day
                  FROM analytics
                  ORDER BY tanggal DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $days);
        $stmt->execute();
        $result = $stmt->get_result();

        $forecast = [];
        while ($row = $result->fetch_assoc()) {
            $forecast[] = $row;
        }

        return array_reverse($forecast);
    }

    /**
     * Rekomendasi wisata berdasarkan penjualan
     */
    public function get_top_wisata($limit = 5) {
        $query = "SELECT 
                    wisata,
                    COUNT(*) as total_terjual,
                    SUM(total_harga) as total_revenue,
                    AVG(jumlah) as avg_kuantitas
                  FROM tiket
                  WHERE tgl_beli >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  GROUP BY wisata
                  ORDER BY total_terjual DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Customer segmentation (high-value, regular, casual)
     */
    public function customer_segmentation() {
        $query = "SELECT 
                    u.id,
                    u.nama,
                    COUNT(t.id_tiket) as total_pembelian,
                    SUM(t.total_harga) as total_spent,
                    AVG(t.total_harga) as avg_spent,
                    MAX(t.tgl_beli) as last_purchase,
                    CASE 
                        WHEN SUM(t.total_harga) > 1000000 THEN 'High-Value'
                        WHEN SUM(t.total_harga) > 500000 THEN 'Regular'
                        ELSE 'Casual'
                    END as segment
                  FROM users u
                  LEFT JOIN tiket t ON u.id = t.user_id
                  WHERE u.role = 'user'
                  GROUP BY u.id, u.nama
                  ORDER BY total_spent DESC";
        
        $result = $this->conn->query($query);
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Churn prediction (pelanggan yang mungkin tidak aktif)
     */
    public function churn_prediction() {
        $query = "SELECT 
                    u.id,
                    u.nama,
                    u.username,
                    MAX(t.tgl_beli) as last_purchase,
                    DATEDIFF(NOW(), MAX(t.tgl_beli)) as days_inactive,
                    CASE 
                        WHEN DATEDIFF(NOW(), MAX(t.tgl_beli)) > 90 THEN 'High Risk'
                        WHEN DATEDIFF(NOW(), MAX(t.tgl_beli)) > 30 THEN 'Medium Risk'
                        ELSE 'Active'
                    END as churn_risk
                  FROM users u
                  LEFT JOIN tiket t ON u.id = t.user_id
                  WHERE u.role = 'user'
                  GROUP BY u.id, u.nama, u.username
                  HAVING churn_risk != 'Active'
                  ORDER BY days_inactive DESC";
        
        $result = $this->conn->query($query);
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Seasonality analysis
     */
    public function seasonality_analysis() {
        $query = "SELECT 
                    MONTH(tgl_beli) as bulan,
                    COUNT(*) as total_tiket,
                    SUM(total_harga) as total_revenue,
                    AVG(total_harga) as avg_harga
                  FROM tiket
                  GROUP BY MONTH(tgl_beli)
                  ORDER BY bulan ASC";
        
        $result = $this->conn->query($query);
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Anomaly detection (penjualan tidak normal)
     */
    public function detect_anomalies() {
        // Calculate mean dan std dev
        $query = "SELECT 
                    AVG(total_revenue) as mean,
                    STDDEV(total_revenue) as stddev
                  FROM analytics";
        
        $result = $this->conn->query($query);
        $stats = $result->fetch_assoc();

        $mean = $stats['mean'] ?? 0;
        $stddev = $stats['stddev'] ?? 0;

        // Find anomalies (values outside 2 standard deviations)
        $query2 = "SELECT 
                      tanggal,
                      total_revenue,
                      ? as expected_range,
                      CASE 
                          WHEN total_revenue > ? + (? * 2) THEN 'Unusually High'
                          WHEN total_revenue < ? - (? * 2) THEN 'Unusually Low'
                          ELSE 'Normal'
                      END as anomaly_status
                    FROM analytics
                    WHERE total_revenue > ? + (? * 2) OR total_revenue < ? - (? * 2)
                    ORDER BY tanggal DESC";
        
        $stmt = $this->conn->prepare($query2);
        $stmt->bind_param('ddddddddd', 
            $mean, $mean, $stddev, $mean, $stddev,
            $mean, $stddev, $mean, $stddev
        );
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Generate AI recommendations
     */
    public function get_recommendations() {
        $recommendations = [];

        // Top selling wisata
        $top_wisata = $this->get_top_wisata(3);
        $recommendations['top_products'] = [
            'title' => 'Wisata Terpopuler',
            'data' => $top_wisata,
            'action' => 'Fokus marketing pada wisata ini'
        ];

        // Churn prediction
        $churn_users = $this->churn_prediction();
        $recommendations['retention_opportunity'] = [
            'title' => 'Pelanggan Berisiko Churn',
            'count' => count($churn_users),
            'action' => 'Hubungi pelanggan untuk reactivation campaign'
        ];

        // Seasonality
        $seasonality = $this->seasonality_analysis();
        $recommendations['seasonality'] = [
            'title' => 'Pola Musiman',
            'data' => $seasonality,
            'action' => 'Persiapkan inventory sesuai trend bulanan'
        ];

        // Anomalies
        $anomalies = $this->detect_anomalies();
        $recommendations['anomalies'] = [
            'title' => 'Transaksi Tidak Normal',
            'count' => count($anomalies),
            'action' => 'Investigasi penyebab fluktuasi penjualan'
        ];

        return $recommendations;
    }

    /**
     * Get dashboard statistics
     */
    public function get_dashboard_stats() {
        $query = "SELECT 
                    SUM(total_tiket_terjual) as total_tiket_all_time,
                    SUM(total_revenue) as total_revenue_all_time,
                    AVG(total_revenue) as avg_daily_revenue,
                    MAX(total_revenue) as peak_revenue,
                    (SELECT COUNT(DISTINCT user_id) FROM tiket) as total_customers,
                    (SELECT COUNT(*) FROM users WHERE role = 'user') as registered_users
                  FROM analytics";
        
        $result = $this->conn->query($query);
        
        return $result->fetch_assoc();
    }
}

// API Endpoints
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');

    $action = $_GET['action'] ?? '';
    $analytics = new AnalyticsAI($conn);

    // Generate daily analytics
    if ($action === 'generate') {
        $result = $analytics->generate_daily_analytics($_GET['date'] ?? null);
        echo json_encode($result);
    }

    // Get forecast
    if ($action === 'forecast') {
        $days = $_GET['days'] ?? 7;
        $result = $analytics->get_revenue_forecast($days);
        echo json_encode($result);
    }

    // Get top wisata
    if ($action === 'top_wisata') {
        $limit = $_GET['limit'] ?? 5;
        $result = $analytics->get_top_wisata($limit);
        echo json_encode($result);
    }

    // Customer segmentation
    if ($action === 'segmentation') {
        $result = $analytics->customer_segmentation();
        echo json_encode($result);
    }

    // Churn prediction
    if ($action === 'churn') {
        $result = $analytics->churn_prediction();
        echo json_encode($result);
    }

    // Seasonality
    if ($action === 'seasonality') {
        $result = $analytics->seasonality_analysis();
        echo json_encode($result);
    }

    // Anomalies
    if ($action === 'anomalies') {
        $result = $analytics->detect_anomalies();
        echo json_encode($result);
    }

    // Recommendations
    if ($action === 'recommendations') {
        $result = $analytics->get_recommendations();
        echo json_encode($result);
    }

    // Dashboard stats
    if ($action === 'stats') {
        $result = $analytics->get_dashboard_stats();
        echo json_encode($result);
    }
}

// Auto-run daily analytics generation (trigger dengan cron job)
if (php_sapi_name() === 'cli') {
    $analytics = new AnalyticsAI($conn);
    $result = $analytics->generate_daily_analytics();
    echo json_encode($result);
}
?>
