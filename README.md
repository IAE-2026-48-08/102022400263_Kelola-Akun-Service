# 🚀 Kelola Akun Service (Fintech Service 1)

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![GraphQL](https://img.shields.io/badge/GraphQL-Lighthouse-E10098?style=flat-square&logo=graphql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

> **Integrasi Aplikasi Enterprise (IAE) - Tugas 2**
>
> Layanan mikro (*microservice*) independen yang bertanggung jawab penuh atas siklus hidup data akun nasabah, pengecekan status validasi, dan otorisasi kelayakan akun dalam ekosistem *E-Banking/Fintech*.

Proyek ini dibangun menggunakan **Laravel 11 (PHP 8.2)** dan mengadopsi arsitektur komunikasi hibrida: menyediakan jalur komunikasi standar sistem-ke-sistem melalui **REST API (Standard Integration Contract)**, serta jalur data taktis berbasis klien melalui **GraphQL**. Seluruh lingkungan pengembangan telah diisolasi secara penuh menggunakan **Docker**.

---

## ✨ Fitur Utama

* 🛡️ **Strict Header Security:** Seluruh lapisan komunikasi API (REST & GraphQL) diamankan secara terpusat menggunakan *Custom Middleware* berbasis validasi `X-IAE-KEY`.
* 📦 **Standardized Wrapper:** Menerapkan IAE-T2 *Integration Contract* yang membungkus setiap *response* REST dengan objek `status`, `message`, `data`, dan `meta` secara konsisten.
* 📖 **Modern OpenAPI 3.0:** Dokumentasi interaktif via *Swagger UI*, dirender secara otomatis menggunakan sintaks modern *PHP Attributes* (bebas dari isu *PHPDoc whitespace*).
* ⚡ **Anti Over-Fetching:** Implementasi *Lighthouse GraphQL* dengan *Custom Resolver* yang memungkinkan klien meminta *field* spesifik (misal: hanya `nama` dan `saldo`) tanpa membebani *payload* jaringan.
* 🐳 **Zero-Config Deployment:** Berjalan secara instan (*Plug and Play*) melalui konfigurasi kontainer tunggal (Apache + PHP 8.2).

---

## 🛠️ Tech Stack & Ekosistem

| Kategori | Teknologi Utama | Library Tambahan |
| :--- | :--- | :--- |
| **Inti Framework** | Laravel 11 (PHP 8.2) | - |
| **Arsitektur REST** | Laravel API Routing | `darkaonline/l5-swagger` (v6.1.2) |
| **Arsitektur GraphQL** | GraphQL API | `nuwave/lighthouse`, `mll-lab/laravel-graphql-playground` |
| **Virtualisasi** | Docker Desktop | Image: `php:8.2-apache` |

---

## 📋 Prerequisites

### Opsi A — Menggunakan Docker (Direkomendasikan)
Tidak memerlukan instalasi PHP atau Composer di mesin lokal.
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (pastikan Engine sudah berjalan)

### Opsi B — Tanpa Docker (Local)
- PHP >= 8.2 (dengan ekstensi `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`)
- Composer >= 2.x
- SQLite (sudah tersedia di PHP secara default)

---

## ⚙️ Environment Variables

Salin file `.env.example` menjadi `.env` sebelum menjalankan aplikasi:

```bash
cp .env.example .env
```

Variabel kunci yang perlu diperhatikan:

| Variable | Default | Keterangan |
| :--- | :--- | :--- |
| `APP_KEY` | *(generate via artisan)* | Generate dengan `php artisan key:generate` |
| `APP_URL` | `http://localhost` | Base URL aplikasi |
| `DB_CONNECTION` | `sqlite` | Proyek ini menggunakan SQLite secara default |
| `L5_SWAGGER_CONST_HOST` | `http://127.0.0.1:8000` | Base URL untuk Swagger UI |

> ⚠️ **Jangan commit file `.env` ke repositori.** File ini sudah terdaftar di `.gitignore`.

---

## 🐳 Instalasi & Menjalankan Aplikasi

### Opsi A — Docker

```bash
# 1. Kloning repositori
git clone https://github.com/IAE-2026-48-08/102022400263_Kelola-Akun-Service.git
cd 102022400263_Kelola-Akun-Service

# 2. Rakit dan jalankan kontainer di latar belakang
docker-compose up -d --build
```

💡 *Indikator Sukses:* Aplikasi langsung dapat diakses pada **`http://localhost:8000`**.

### Opsi B — Local (Tanpa Docker)

```bash
# 1. Kloning repositori
git clone https://github.com/IAE-2026-48-08/102022400263_Kelola-Akun-Service.git
cd 102022400263_Kelola-Akun-Service

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Jalankan server lokal
php artisan serve
```

💡 *Indikator Sukses:* Aplikasi berjalan pada **`http://127.0.0.1:8000`**.

---

## 🔐 Autentikasi Global

Sistem menolak semua *request* anonim. Anda **wajib** menyisipkan *Header* berikut pada *HTTP Client* (Postman, Frontend, atau Service Lain) untuk mengakses seluruh *endpoint* REST maupun GraphQL:

```json
{
  "X-IAE-KEY": "102022400263"
}
```
*(Catatan: Menggunakan Nomor Induk Mahasiswa / NIM sebagai kunci akses)*.

---

## 🌐 Dokumentasi & Rute REST API

Antarmuka interaktif **Swagger UI** tersedia secara lokal untuk melakukan pengujian operasional secara langsung.

* 📍 **Akses UI:** [`http://localhost:8000/api/documentation`](http://localhost:8000/api/documentation)

### Daftar Endpoint Utama (Prefix: `/api/v1`)

| Metode | Endpoint | Deskripsi |
| :---: | :--- | :--- |
| `GET` | `/accounts` | Mengambil daftar agregat seluruh akun nasabah. |
| `GET` | `/accounts/{id}` | Memuat detail informasi dan nominal saldo pada entitas spesifik. |
| `GET` | `/accounts/{id}/validation-status` | Mengecek status persetujuan atau validasi sebuah akun. |
| `POST`| `/accounts/{id}/validate` | Memproses dan mengubah kelayakan akun menjadi *verified*. |

---

## 🔮 Pengujian GraphQL API

Disediakan antarmuka **GraphQL Playground** terintegrasi untuk menyimulasikan *Client-Driven Request* dan menghindari *Over-fetching*.

* 📍 **Akses UI:** [`http://localhost:8000/graphql-playground`](http://localhost:8000/graphql-playground)

> ⚠️ Jangan lupa memasukkan JSON kredensial `X-IAE-KEY` pada tab **HTTP HEADERS** di pojok kiri bawah Playground.

**Skenario Uji: Mengambil Data Saldo Taktis**
```graphql
query AmbilSaldoTaktis {
  accounts {
    nama
    saldo
  }
}
```

**Ekspektasi Respons Sukses (Anti Over-fetching):**
```json
{
  "data": {
    "accounts": [
      {
        "nama": "Nugraha Ade",
        "saldo": 5000000
      },
      {
        "nama": "Magnus Midtbo",
        "saldo": 7500000
      }
    ]
  }
}
```

---

## 👨‍💻 Dikembangkan Oleh

**Nugraha Ade Mulyana**  
Integrasi Aplikasi Enterprise — Tugas 2
