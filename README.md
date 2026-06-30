# Wisata Malang Deployment

Aplikasi PHP sederhana untuk tiket wisata Malang.

## Deploy ke Railway

1. Inisialisasi Git jika belum:
   ```bash
   git init
   git add .
   git commit -m "Railway deployment setup"
   ```

2. Pasang Railway CLI di Windows:
   https://railway.app/docs/cli

3. Buat project Railway:
   ```bash
   railway init
   ```

4. Sambungkan database MySQL Railway:
   ```bash
   railway add mysql
   ```

5. Atur environment variables di Railway (Project Settings / Variables):
   - DB_HOST
   - DB_USER
   - DB_PASS
   - DB_NAME
   - DB_PORT

   Railway MySQL biasanya juga menyimpan variabel `MYSQL_HOST`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_DATABASE`, `MYSQL_PORT`.

6. Deploy aplikasi:
   ```bash
   railway up
   ```

## Local Testing dengan Docker Compose

Untuk menjalankan aplikasi secara lokal menggunakan Docker Compose:

```bash
docker-compose up --build
```

Lalu buka:

```bash
http://localhost:8080
```

Database akan tersedia pada port `3307` untuk koneksi lokal jika perlu.

## Catatan

- `Dockerfile` sudah dibuat untuk menjalankan PHP + Apache di Railway.
- `docker-compose.yml` dibuat untuk testing lokal dengan MySQL.
- `config.php` sudah mendukung environment variables Railway dan Docker Compose.
- Pastikan Anda telah membuat tabel `users` dan `tiket` di database `db_wisata`.
