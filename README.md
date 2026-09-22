# Todo List — RESTful API Learning Project

Project pembelajaran full-stack sederhana untuk memahami konsep **RESTful API**
dan komunikasi antara **frontend web** dan **backend**.

## 1. Tujuan Project

Project ini menunjukkan alur komunikasi berikut:

```text
Frontend Web (HTML/CSS/JS)
     │
     │ HTTP Request (fetch)
     ▼
RESTful API (Vanilla PHP)
     │
     │ SQL (PDO, prepared statement)
     ▼
PostgreSQL
```

Frontend **tidak pernah** mengakses database secara langsung. Semua komunikasi
frontend dengan data hanya melalui HTTP + JSON ke backend.

Project ini sengaja dibuat sederhana (tanpa framework, tanpa ORM, tanpa
autentikasi) agar mudah dipahami pemula dan fokus pada konsep intinya.

## 2. Arsitektur

```text
todo-fullstack/
├── backend/     → Vanilla PHP RESTful API + PostgreSQL + Docker
└── frontend/    → HTML + CSS + Vanilla JavaScript
```

Backend dan frontend **sepenuhnya terpisah**. Backend bisa di-deploy ke Render,
sementara frontend bisa dibuka langsung sebagai file statis atau dihosting di
mana saja (GitHub Pages, Netlify, dll).

## 3. Struktur Folder

```text
todo-fullstack/
│
├── backend/
│   ├── public/
│   │   └── index.php          # Entry point API
│   ├── src/
│   │   ├── Database.php       # Koneksi PDO ke PostgreSQL
│   │   ├── Router.php         # Routing, validasi, CORS, response
│   │   └── TodoRepository.php # Query ke tabel todos
│   ├── database/
│   │   └── init.sql           # Skema tabel todos
│   ├── Dockerfile
│   ├── docker-compose.yml
│   ├── .env.example
│   └── .gitignore
│
└── frontend/
    ├── index.html
    ├── style.css
    ├── api.js                 # Semua fetch() ke backend
    └── app.js                 # State, rendering, event handling
```

## 4. Requirement

- Docker & Docker Compose
- Browser modern (untuk frontend)
- (Opsional) `curl` untuk testing API

## 5. Menjalankan Backend + Database secara Lokal (Docker)

```bash
cd backend
docker compose up --build
```

Perintah ini akan menjalankan dua container:

- `postgres` → database PostgreSQL, data tersimpan di volume `todo_postgres_data`
  sehingga tidak hilang saat container di-restart. Tabel `todos` otomatis
  dibuat dari `database/init.sql` saat container pertama kali start.
- `php-api` → REST API, dapat diakses di `http://localhost:8000`

Untuk menghentikan:

```bash
docker compose down
```

Untuk menghentikan sekaligus menghapus data database:

```bash
docker compose down -v
```

## 6. Daftar Endpoint

| Method | Endpoint          | Deskripsi                        |
|--------|-------------------|-----------------------------------|
| GET    | `/health`         | Cek apakah API berjalan          |
| GET    | `/api/todos`      | Mengambil semua todo             |
| GET    | `/api/todos/{id}` | Mengambil satu todo              |
| POST   | `/api/todos`      | Membuat todo baru                |
| PATCH  | `/api/todos/{id}` | Mengubah sebagian data todo      |
| DELETE | `/api/todos/{id}` | Menghapus todo                   |

### Model Todo

```json
{
  "id": 1,
  "title": "Belajar REST API",
  "completed": false,
  "created_at": "2026-09-22T10:00:00Z",
  "updated_at": "2026-09-22T10:00:00Z"
}
```

## 7. Contoh Request & Response

**GET /api/todos**

```bash
curl http://localhost:8000/api/todos
```

```json
{
  "data": [
    { "id": 1, "title": "Belajar REST API", "completed": false, "created_at": "...", "updated_at": "..." }
  ]
}
```

**POST /api/todos**

```bash
curl -X POST http://localhost:8000/api/todos \
  -H "Content-Type: application/json" \
  -d '{"title":"Belajar REST API"}'
```

Response: `201 Created`

**PATCH /api/todos/1**

```bash
curl -X PATCH http://localhost:8000/api/todos/1 \
  -H "Content-Type: application/json" \
  -d '{"completed":true}'
```

Response: `200 OK`

**DELETE /api/todos/1**

```bash
curl -X DELETE http://localhost:8000/api/todos/1
```

Response: `204 No Content`

**Todo tidak ditemukan**

```json
{
  "error": {
    "message": "Todo not found"
  }
}
```

Response: `404 Not Found`

### Ringkasan HTTP Status Code

| Status | Kapan digunakan                              |
|--------|-----------------------------------------------|
| 200    | Request berhasil (GET, PATCH)                 |
| 201    | Todo berhasil dibuat (POST)                    |
| 204    | Todo berhasil dihapus, tidak ada body response |
| 400    | Request tidak valid (JSON salah, field kosong) |
| 404    | Todo atau route tidak ditemukan                |
| 405    | Method tidak diizinkan untuk route tersebut    |
| 500    | Error tak terduga di server                    |

## 8. Menjalankan Frontend

Frontend adalah file statis, tidak perlu build tool apapun.

Cara termudah: buka `frontend/index.html` langsung di browser, atau jalankan
server statis sederhana:

```bash
cd frontend
python3 -m http.server 5500
```

Lalu buka `http://localhost:5500` di browser.

Pastikan backend (`http://localhost:8000`) sudah berjalan terlebih dahulu.

## 9. Mengganti API URL

Buka file `frontend/api.js`, lalu ubah baris berikut:

```javascript
const API_URL = "http://localhost:8000";
```

Setelah backend dideploy ke Render, ganti menjadi:

```javascript
const API_URL = "https://todo-api-xxxx.onrender.com";
```

Nilai `API_URL` **hanya didefinisikan di satu tempat** (`api.js`), tidak
tersebar di banyak file.

## 10. Environment Variables (Backend)

| Variable      | Deskripsi                                  |
|---------------|---------------------------------------------|
| `DB_HOST`     | Host database PostgreSQL                    |
| `DB_PORT`     | Port database (default `5432`)               |
| `DB_NAME`     | Nama database                                |
| `DB_USER`     | Username database                            |
| `DB_PASSWORD` | Password database                            |
| `PORT`        | Port yang digunakan server PHP (Render mengisi otomatis) |

Salin `backend/.env.example` menjadi `.env` untuk referensi lokal. File `.env`
tidak boleh di-commit ke Git (sudah ada di `.gitignore`).

> Catatan: `docker-compose.yml` saat ini mengisi environment variable langsung
> di dalamnya untuk mempermudah pembelajaran. Untuk penggunaan yang lebih rapi,
> nilai tersebut bisa dipindahkan ke file `.env` dan dirujuk dengan `env_file`.

## 11. Deployment Backend ke Render

1. Push folder `backend/` ke repository Git (GitHub/GitLab).
2. Di Render, buat **New Web Service** → pilih **Docker** sebagai environment.
3. Arahkan Render ke repository dan pastikan root directory-nya adalah `backend/`
   (jika backend berada di sub-folder repo).
4. Render akan otomatis mem-build image dari `Dockerfile`.
5. Tambahkan environment variables berikut di dashboard Render:
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
   - (`PORT` sudah disediakan otomatis oleh Render, tidak perlu diisi manual)
6. Sediakan database PostgreSQL (misalnya menggunakan **Render PostgreSQL**),
   lalu jalankan isi `backend/database/init.sql` ke database tersebut sekali
   saja (melalui `psql` atau dashboard Render).
7. Setelah deploy selesai, Render memberikan URL seperti:

   ```text
   https://todo-api-xxxx.onrender.com
   ```

8. Uji endpoint:

   ```bash
   curl https://todo-api-xxxx.onrender.com/health
   curl https://todo-api-xxxx.onrender.com/api/todos
   ```

9. Update `API_URL` di `frontend/api.js` dengan URL tersebut (lihat bagian 9).

Server PHP di dalam container mendengarkan `0.0.0.0:$PORT`, sehingga otomatis
kompatibel dengan port yang diberikan Render — tidak ada hardcode
`localhost:8000` untuk production.

## 12. Cara Menguji API (curl)

```bash
# Cek status API
curl http://localhost:8000/health

# Ambil semua todo
curl http://localhost:8000/api/todos

# Ambil satu todo
curl http://localhost:8000/api/todos/1

# Buat todo baru
curl -X POST http://localhost:8000/api/todos \
  -H "Content-Type: application/json" \
  -d '{"title":"Belajar REST API"}'

# Tandai todo selesai
curl -X PATCH http://localhost:8000/api/todos/1 \
  -H "Content-Type: application/json" \
  -d '{"completed":true}'

# Ubah judul todo
curl -X PATCH http://localhost:8000/api/todos/1 \
  -H "Content-Type: application/json" \
  -d '{"title":"Belajar RESTful API"}'

# Hapus todo
curl -X DELETE http://localhost:8000/api/todos/1
```

## 13. Penjelasan CORS

Karena frontend (misalnya `http://localhost:5500`) dan backend
(`http://localhost:8000`) berjalan di origin yang berbeda, browser akan
memblokir request lintas-origin kecuali server mengizinkannya secara
eksplisit melalui header CORS.

Backend mengirimkan header berikut pada setiap response:

```text
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type
```

Untuk request selain `GET`/`POST` sederhana (seperti `PATCH`, `DELETE`, atau
request dengan header `Content-Type: application/json`), browser terlebih
dahulu mengirim request **preflight** dengan method `OPTIONS` untuk
memastikan server mengizinkan request tersebut. Backend menangani ini dengan
langsung mengembalikan `204 No Content` beserta header CORS di atas, tanpa
memprosesnya sebagai route API.

`Access-Control-Allow-Origin: *` dipakai di sini agar mudah dipahami untuk
keperluan pembelajaran. Untuk aplikasi produksi sungguhan, nilai ini
sebaiknya dibatasi hanya ke origin frontend yang benar-benar dipercaya.

## 14. Batasan Project

Project ini sengaja **tidak** mencakup:

- Autentikasi / login / register / JWT
- Manajemen user, role, atau permission
- Redis, queue, WebSocket, microservices
- ORM atau framework backend/frontend
- Aplikasi mobile (Flutter, dll)

Fokus project ini murni pada konsep RESTful API dan komunikasi
frontend–backend–database.
