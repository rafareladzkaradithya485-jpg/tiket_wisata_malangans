# Wisata Malang Deployment

Aplikasi PHP sederhana untuk tiket wisata Malang.

## Deploy ke Railway

### A. Dari GitHub Repository (direkomendasikan)

1. Pastikan repo sudah di-push ke GitHub:
   - `https://github.com/rafaradithya-maker/tiket_wisata_malangans`

2. Buka dashboard Railway di:
   - https://railway.app

3. Buat project baru dan pilih "Deploy from GitHub".

4. Pilih repository `tiket_wisata_malangans`.

5. Railway akan mendeteksi `railway.json` dan `Dockerfile`.
   - Jika diminta, gunakan builder `dockerfile`.
   - Start command: `apache2-foreground`

6. Tambahkan plugin MySQL di Railway:
   - Pilih "Add Plugin" → "MySQL"

7. Railway biasanya mengisi environment variables otomatis.
   Jika belum, tambahkan:
   - `DB_HOST`
   - `DB_USER`
   - `DB_PASS`
   - `DB_NAME`
   - `DB_PORT`

   Railway MySQL juga biasanya menyediakan:
   - `MYSQL_HOST`
   - `MYSQL_USER`
   - `MYSQL_PASSWORD`
   - `MYSQL_DATABASE`
   - `MYSQL_PORT`

8. Deploy dan tunggu build selesai.
   - Aplikasi akan berjalan di URL Railway yang diberikan.

### B. Dari CLI Railway

1. Pastikan Git sudah terinisialisasi dan sudah di-push.
2. Pasang Railway CLI di Windows:
   https://railway.app/docs/cli
3. Jalankan:
   ```bash
   railway login
   railway init
   railway add mysql
   railway up
   ```

4. Atur environment variables jika perlu.

## Deploy ke Render

Render dapat menggunakan `render.yaml` untuk menjalankan layanan web dan layanan MySQL secara bersama-sama.

1. Pastikan repo sudah di-push ke GitHub.
2. Buka dashboard Render di:
   - https://render.com
3. Buat "Web Service" baru dan pilih GitHub repository `tiket_wisata_malangans`.
4. Gunakan `render.yaml` di root repo sebagai Blueprint untuk konfigurasi.
5. Untuk opsi manual atau CI/CD, pasang Render CLI dengan cara direct download dari GitHub Releases:
   - https://github.com/render-oss/cli/releases
   - Download `cli_<version>_windows_amd64.zip` untuk Windows
   - Ekstrak dan jalankan `render.exe`

Jika Anda menggunakan Render CLI, langkah dasar deploy adalah:
```bash
render login
# pilih workspace
render blueprints validate render.yaml
render blueprints apply render.yaml
```

> Catatan: Render tidak menyediakan managed MySQL langsung, jadi `render.yaml` ini membuat layanan MySQL privat menggunakan image MySQL dan menghubungkannya ke layanan PHP.

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
