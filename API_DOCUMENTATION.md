# API Documentation - Tiket Wisata Malang

## 🎫 Fitur-Fitur Baru

### 1. **Sistem Pembayaran Bank (Payment Gateway)**
File: `payment_gateway.php`

#### Integrasi Midtrans
- Support untuk Midtrans Snap
- Multiple payment methods: Kartu Kredit, E-Wallet, Bank Transfer
- Webhook untuk verifikasi otomatis

#### Endpoints:
```
POST /payment_gateway.php?action=initiate
```
Request:
```json
{
    "tiket_id": 1,
    "jumlah": 100000,
    "payment_method": "midtrans"
}
```

Response:
```json
{
    "status": true,
    "message": "Silahkan lakukan pembayaran",
    "payment_id": 1,
    "token": "snap_token_xxx",
    "redirect_url": "https://app.midtrans.com/snap/v1/..."
}
```

#### Status Payment:
```
GET /payment_gateway.php?action=check&payment_id=1
```

#### Payment History:
```
GET /payment_gateway.php?action=history
```

---

### 2. **Fitur Refund/Pengembalian Dana**
File: `refund_handler.php`

#### Create Refund Request:
```
POST /refund_handler.php?action=create_request
```
Request:
```json
{
    "tiket_id": 1,
    "alasan": "Tidak jadi berangkat"
}
```

#### Admin Approve Refund:
```
POST /refund_handler.php?action=approve
```
Request:
```json
{
    "refund_id": 1
}
```

#### Admin Reject Refund:
```
POST /refund_handler.php?action=reject
```
Request:
```json
{
    "refund_id": 1,
    "reason": "Request tidak sesuai kebijakan"
}
```

#### Get Refund History:
```
GET /refund_handler.php?action=history
```

#### Admin Get All Requests:
```
GET /refund_handler.php?action=all_requests&status=pending
```
Status: `pending`, `approved`, `rejected`, `completed`

#### Get Statistics:
```
GET /refund_handler.php?action=stats
```

---

### 3. **API Mobile App (Android/iOS)**
File: `api_mobile.php`

#### Authentication

**Login:**
```
POST /api_mobile.php/api/auth
```
Request:
```json
{
    "action": "login",
    "username": "user@example.com",
    "password": "password123"
}
```

**Register:**
```
POST /api_mobile.php/api/auth
```
Request:
```json
{
    "action": "register",
    "nama": "John Doe",
    "username": "johndoe",
    "password": "password123"
}
```

#### Tiket Management

**Get All Tikets:**
```
GET /api_mobile.php/tiket
Headers: Authorization: Bearer <token>
```

**Get Tiket Detail:**
```
GET /api_mobile.php/tiket/detail?id=1
Headers: Authorization: Bearer <token>
```

**Create Tiket:**
```
POST /api_mobile.php/tiket
Headers: Authorization: Bearer <token>
```
Request:
```json
{
    "wisata": "Bromo",
    "jumlah": 2,
    "total_harga": 300000
}
```

#### Payment via Mobile

**Initiate Payment:**
```
POST /api_mobile.php/payment/initiate
Headers: Authorization: Bearer <token>
```
Request:
```json
{
    "tiket_id": 1,
    "jumlah": 100000,
    "payment_method": "midtrans"
}
```

**Get Payment History:**
```
GET /api_mobile.php/payment/history
Headers: Authorization: Bearer <token>
```

#### Refund via Mobile

**Create Refund:**
```
POST /api_mobile.php/refund/create
Headers: Authorization: Bearer <token>
```
Request:
```json
{
    "tiket_id": 1,
    "alasan": "Alasan pembatalan"
}
```

**Get Refund History:**
```
GET /api_mobile.php/refund/history
Headers: Authorization: Bearer <token>
```

#### Profile Management

**Get Profile:**
```
GET /api_mobile.php/profile
Headers: Authorization: Bearer <token>
```

**Update Profile:**
```
PUT /api_mobile.php/profile
Headers: Authorization: Bearer <token>
```
Request:
```json
{
    "nama": "New Name"
}
```

#### Search

**Search Wisata:**
```
GET /api_mobile.php/search?q=bromo
Headers: Authorization: Bearer <token>
```

---

### 4. **AI Predictive Analytics**
File: `analytics_ai.php`

#### Generate Daily Analytics:
```
GET /analytics_ai.php?action=generate&date=2026-06-30
```

#### Revenue Forecast (7 hari):
```
GET /analytics_ai.php?action=forecast&days=7
```

Response:
```json
[
    {
        "tanggal": "2026-06-30",
        "total_revenue": 5000000,
        "predicted_revenue_next_day": 5200000
    }
]
```

#### Top Wisata:
```
GET /analytics_ai.php?action=top_wisata&limit=5
```

#### Customer Segmentation:
```
GET /analytics_ai.php?action=segmentation
```

Response:
```json
[
    {
        "id": 1,
        "nama": "Customer Name",
        "total_pembelian": 5,
        "total_spent": 1500000,
        "segment": "High-Value"
    }
]
```

#### Churn Prediction:
```
GET /analytics_ai.php?action=churn
```

#### Seasonality Analysis:
```
GET /analytics_ai.php?action=seasonality
```

#### Anomaly Detection:
```
GET /analytics_ai.php?action=anomalies
```

#### AI Recommendations:
```
GET /analytics_ai.php?action=recommendations
```

Response:
```json
{
    "top_products": {
        "title": "Wisata Terpopuler",
        "data": [...],
        "action": "..."
    },
    "retention_opportunity": {...},
    "seasonality": {...},
    "anomalies": {...}
}
```

#### Dashboard Stats:
```
GET /analytics_ai.php?action=stats
```

---

## 📋 Database Schema

### Tabel payments
```sql
- id_payment (INT PRIMARY KEY)
- id_tiket (INT FOREIGN KEY)
- user_id (INT FOREIGN KEY)
- jumlah (INT)
- payment_method (VARCHAR)
- payment_status (ENUM: pending, success, failed, expired)
- transaction_id (VARCHAR UNIQUE)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### Tabel refunds
```sql
- id_refund (INT PRIMARY KEY)
- id_payment (INT FOREIGN KEY)
- id_tiket (INT FOREIGN KEY)
- user_id (INT FOREIGN KEY)
- jumlah_refund (INT)
- alasan (TEXT)
- status (ENUM: pending, approved, rejected, completed)
- request_date (TIMESTAMP)
- approved_date (TIMESTAMP)
- completed_date (TIMESTAMP)
```

### Tabel analytics
```sql
- id_analytics (INT PRIMARY KEY)
- tanggal (DATE UNIQUE)
- total_tiket_terjual (INT)
- total_revenue (BIGINT)
- wisata_terpopuler (VARCHAR)
- visitor_count (INT)
- predicted_revenue_next_day (BIGINT)
- trend (VARCHAR: naik, turun, stabil)
- created_at (TIMESTAMP)
```

---

## 🔑 Environment Variables

```env
# Database
DB_HOST=localhost
DB_USER=root
DB_PASS=password
DB_NAME=db_wisata

# Midtrans Payment Gateway
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_ENVIRONMENT=sandbox
```

---

## 🚀 Setup Instructions

### 1. Database Setup
```sql
-- Jalankan db_init.sql di MySQL
mysql -u root -p db_wisata < db_init.sql
```

### 2. Midtrans Setup
1. Daftar di https://midtrans.com
2. Dapatkan Server Key dan Client Key
3. Set di `.env` atau environment variables

### 3. Integrasi Mobile App
```javascript
// Contoh untuk Flutter/React Native
const API_URL = 'https://your-domain.com/api_mobile.php';

// Login
const login = async (username, password) => {
    const response = await fetch(`${API_URL}/api/auth`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'login',
            username,
            password
        })
    });
    return response.json();
};

// Get Tikets
const getTikets = async (token) => {
    const response = await fetch(`${API_URL}/tiket`, {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    return response.json();
};
```

---

## 🔒 Security Notes

1. **API Authentication**: Gunakan JWT atau OAuth2 untuk production
2. **HTTPS Only**: Deploy hanya dengan HTTPS
3. **Rate Limiting**: Implementasikan rate limiting
4. **Input Validation**: Semua input sudah di-validate
5. **SQL Injection Prevention**: Gunakan prepared statements (sudah diimplementasikan)

---

## 📊 Cron Job untuk Analytics

Untuk menggenerate analytics setiap hari, setup cron job:

```bash
# Setiap hari jam 00:00
0 0 * * * /usr/bin/php /var/www/html/analytics_ai.php
```

---

## 📞 Support

Untuk pertanyaan lebih lanjut, hubungi tim development.
