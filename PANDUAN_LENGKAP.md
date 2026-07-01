# 🎫 Panduan Lengkap Tiket Wisata Malang

## 📋 Daftar Isi
1. [Persiapan Awal](#persiapan-awal)
2. [Akses Aplikasi](#akses-aplikasi)
3. [Fitur Utama](#fitur-utama)
4. [Troubleshooting](#troubleshooting)
5. [Informasi Teknis](#informasi-teknis)

---

## 🚀 Persiapan Awal

### 1. Pastikan XAMPP Berjalan
```bash
# Mulai Apache dan MySQL
# Buka XAMPP Control Panel
# Klik "Start" untuk Apache dan MySQL
```

### 2. Verifikasi Database
```bash
# Buka phpMyAdmin
# URL: http://localhost/phpmyadmin
# Database: tiket_malang
# Seharusnya ada 8 tabel:
#   - users
#   - tiket
#   - payments
#   - refunds
#   - analytics
#   - wisata
#   - reviews
#   - fasilitas_wisata
```

### 3. Cek Status Sistem
```bash
# Buka http://localhost/wisata_malang/diagnose.php
# Pastikan semua status berwarna HIJAU ✓
```

---

## 🌐 Akses Aplikasi

### URL Utama
| Halaman | URL | Keterangan |
|---------|-----|-----------|
| Beranda | `http://localhost/wisata_malang/` | Halaman awal |
| Home | `http://localhost/wisata_malang/home.php` | Dashboard utama |
| Wisata | `http://localhost/wisata_malang/wisata.php` | Lihat semua wisata |
| Login | `http://localhost/wisata_malang/login.php` | Masuk akun |
| Register | `http://localhost/wisata_malang/register.php` | Daftar akun baru |
| Dashboard User | `http://localhost/wisata_malang/dashboard_user.php` | Dashboard pengguna |
| Dashboard Admin | `http://localhost/wisata_malang/dashboard_admin.php` | Dashboard admin |
| Diagnose | `http://localhost/wisata_malang/diagnose.php` | Cek status sistem |
| Test API | `http://localhost/wisata_malang/test_api.php` | Tool testing API |

### Akun Test
Anda bisa login dengan membuat akun baru atau gunakan:
```
Username: test_user
Password: password123
```

---

## ✨ Fitur Utama

### 1. 🏖️ Informasi Wisata
- **URL**: `/wisata.php`
- **Fitur**:
  - Lihat semua destinasi wisata
  - Search dan filter
  - Lihat rating dan review
  - Tambah review (jika sudah login)

**Wisata yang Tersedia**:
- **Jatimpark 1** - Rp 150.000/tiket (Rating: 4.5⭐)
- **Gunung Bromo** - Rp 120.000/tiket (Rating: 4.8⭐)

### 2. 💳 Pembayaran (Midtrans)
- **File**: `payment_gateway.php`
- **Fitur**:
  - Berbagai metode pembayaran
  - Integrasi dengan Midtrans (Sandbox)
  - Tracking pembayaran real-time
  - Notifikasi pembayaran

### 3. ↩️ Refund
- **File**: `refund_handler.php`
- **Fitur**:
  - Request refund mudah
  - Tracking status refund
  - Persetujuan admin
  - Pengembalian dana otomatis

### 4. 📱 Mobile API
- **File**: `api_mobile.php`
- **Fitur**:
  - REST API untuk aplikasi mobile
  - JSON response format
  - Bearer token authentication
  - Support untuk Android/iOS

### 5. 📊 Analytics & AI
- **File**: `analytics_ai.php`
- **Fitur**:
  - Prediksi revenue
  - Segmentasi customer
  - Deteksi anomali
  - Rekomendasi bisnis

---

## 🔧 API Endpoints

### Wisata API
```
GET  /wisata_info.php?action=all              # Semua wisata
GET  /wisata_info.php?action=detail&id=1      # Detail wisata
GET  /wisata_info.php?action=search&keyword=  # Search
GET  /wisata_info.php?action=filter&category= # Filter
POST /wisata_info.php?action=add_review       # Tambah review
```

### Payment API
```
GET  /payment_gateway.php?action=all          # Semua pembayaran
GET  /payment_gateway.php?action=history      # Riwayat
GET  /payment_gateway.php?action=check        # Check status
POST /payment_gateway.php?action=initiate     # Inisiasi pembayaran
```

### Analytics API
```
GET  /analytics_ai.php?action=stats           # Statistik
GET  /analytics_ai.php?action=forecast        # Prediksi revenue
GET  /analytics_ai.php?action=segmentation    # Segmentasi customer
GET  /analytics_ai.php?action=churn           # Prediksi churn
```

---

## 📞 Troubleshooting

### ❌ "Database Connection Failed"
**Solusi**:
1. Buka XAMPP Control Panel
2. Pastikan MySQL status "Running" (hijau)
3. Buka http://localhost/phpmyadmin
4. Jika error, import database: `db_init.sql`

### ❌ "File not found / 404"
**Solusi**:
1. Pastikan XAMPP Apache running
2. Cek path file di: `C:\xampp\htdocs\wisata_malang\`
3. Buka http://localhost/wisata_malang/diagnose.php untuk verifikasi

### ❌ "Session not working / Cannot login"
**Solusi**:
1. Buka diagnose.php: http://localhost/wisata_malang/diagnose.php
2. Cek "PHP Environment" section
3. Pastikan tidak ada error
4. Clear browser cookies dan coba lagi

### ❌ "API returns 500 error"
**Solusi**:
1. Buka test_api.php: http://localhost/wisata_malang/test_api.php
2. Coba test endpoint satu per satu
3. Lihat error message di browser console (F12)
4. Check error log XAMPP

### ❌ "Pembayaran tidak bekerja"
**Solusi**:
1. Pastikan Midtrans credentials ada di `.env` atau environment variables
2. Gunakan Sandbox environment (bukan Production)
3. Untuk test, gunakan kartu dummy: `4811 1111 1111 1114`

---

## 💻 Informasi Teknis

### Stack Technology
- **Backend**: PHP 8.2
- **Database**: MySQL 8.0 (MariaDB)
- **Server**: Apache (XAMPP)
- **Frontend**: HTML5, CSS3, JavaScript
- **Payment**: Midtrans Snap API
- **Mobile API**: REST + JSON

### Structure Folder
```
wisata_malang/
├── config.php              # Database config & helpers
├── index.php               # Homepage (lama)
├── home.php                # Homepage (baru)
├── login.php               # Login page
├── register.php            # Register page
├── logout.php              # Logout
├── dashboard_user.php      # User dashboard
├── dashboard_admin.php     # Admin dashboard
├── wisata.php              # Wisata list page
├── wisata_info.php         # Wisata API
├── payment_gateway.php     # Payment integration
├── refund_handler.php      # Refund system
├── api_mobile.php          # Mobile API
├── analytics_ai.php        # Analytics engine
├── diagnose.php            # System diagnostic
├── test_api.php            # API testing tool
├── db_init.sql             # Database schema
├── db_backup/              # Database backups
└── json/
    └── main.js             # JavaScript utilities
```

### Database Schema

**users**
```sql
id, nama, username (UNIQUE), password, role (user/admin), created_at
```

**tiket**
```sql
id_tiket, user_id (FK), wisata, jumlah, total_harga, tgl_beli, 
status, kode_barcode, payment_method, payment_id
```

**payments**
```sql
id_payment, id_tiket (FK), user_id (FK), jumlah, payment_method,
payment_status (pending/success/failed/expired), transaction_id, 
created_at, updated_at
```

**wisata** (8 columns extended)
```sql
id_wisata, nama_wisata, deskripsi, lokasi, harga_tiket, jam_buka, 
jam_tutup, kategori, rating, total_review, gambar_url, lat, lon,
no_hp_contact, email_contact, status_aktif, created_at, updated_at
```

### Konfigurasi Environment

Buat file `.env` untuk production (optional):
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=tiket_malang

MIDTRANS_SERVER_KEY=your_server_key_here
MIDTRANS_CLIENT_KEY=your_client_key_here
MIDTRANS_ENVIRONMENT=sandbox
```

---

## 🔐 Security Notes

1. **Password Hashing**: Menggunakan `password_hash()` dengan algoritma default (bcrypt)
2. **SQL Injection**: Input disanitasi dengan `input_bersih()`
3. **Session**: Session timeout default 24 jam
4. **Payment**: Midtrans handle keamanan transaksi
5. **HTTPS**: Gunakan HTTPS di production

---

## 📚 Reference Cepat

### Terminal Commands
```bash
# Start development server
cd C:\xampp\htdocs\wisata_malang

# View git history
git log --oneline -5

# Push ke GitHub
git push origin main

# Import database
mysql -u root tiket_malang < db_init.sql

# Backup database
mysqldump -u root tiket_malang > backup.sql
```

### PHP Utility Functions (config.php)
```php
input_bersih($str)        # Sanitasi input SQL
rupiah($number)           # Format currency
mysqli_query_safe()       # Safe query execution
```

### JavaScript in json/main.js
```javascript
// Global utility functions
formatCurrency(amount)
formatDate(date)
showAlert(message, type)  // type: success, error, warning
```

---

## 🎯 Next Steps

1. ✅ Test semua fitur di http://localhost/wisata_malang/
2. ✅ Coba login/register
3. ✅ Browse wisata dan tambah review
4. ✅ Test API di test_api.php
5. ✅ Setup production di hosting

---

## 📞 Support & Updates

- **Repository**: https://github.com/rafaradithya-maker/tiket_wisata_malangans
- **Last Updated**: 2026-01-19
- **Version**: 2.0 (dengan Payment, Refund, Mobile API, Analytics)
- **Status**: Fully Functional ✅

---

**Dibuat oleh**: Rafa Radithya  
**License**: MIT  
**Contact**: info@tiketwisatamalang.com
