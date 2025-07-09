# Koupii LMS API

API backend untuk platform kursus bahasa Inggris berbasis Laravel.

---

## 🚀 Fitur Utama
- Laravel 12, siap untuk pengembangan API
- Autentikasi dengan Sanctum
- Dokumentasi API otomatis dengan Swagger (L5 Swagger)
- CORS sudah dikonfigurasi untuk frontend
- Struktur siap kolaborasi tim

---

## 🛠️ Setup Development

1. **Clone repository**
   ```sh
   git clone <repo-url>
   cd koupii-api
   ```

2. **Install dependency**
   ```sh
   composer install
   npm install
   ```

3. **Copy environment file**
   ```sh
   cp .env.example .env
   ```

4. **Generate app key**
   ```sh
   php artisan key:generate
   ```

5. **Jalankan migrasi database**
   ```sh
   php artisan migrate
   ```

6. **Jalankan server**
   ```sh
   php artisan serve
   ```

---

## 📖 Dokumentasi API (Swagger)
- **Swagger UI:**
  - Buka [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)
- **Regenerate docs jika ada perubahan endpoint:**
  ```sh
  php artisan l5-swagger:generate
  ```

---

## 🔍 Testing Endpoint
- Coba akses endpoint test:
  ```sh
  curl http://localhost:8000/api/test
  # atau buka di browser
  ```
  Response:
  ```json
  { "message": "API is working!" }
  ```

---

## 👥 Kolaborasi
- **Jangan commit file `.env` atau file di folder `storage/`**
- **Gunakan `.env.example` sebagai template environment**
- **Log dan file sensitif sudah di-.gitignore**
- **Tambahkan anotasi Swagger di setiap controller endpoint baru**
- **Jalankan `php artisan l5-swagger:generate` setelah menambah/mengubah endpoint**

---

## 📦 Struktur Penting
- `routes/api.php` — Semua endpoint API
yang terdokumentasi Swagger
- `app/Http/Controllers/` — Controller API
- `config/l5-swagger.php` — Konfigurasi Swagger
- `storage/logs/` — File log (tidak di-commit)

---

## 📝 Kontribusi
1. Fork & clone repo
2. Buat branch fitur/bugfix
3. Pull request ke main

---

## 📬 Kontak
- Email: aziz@magercoding.com

---

Happy coding & kolaborasi!
