# 📋 Log Prompting AI — Tugas 3 IAE
### Integrasi Aplikasi Enterprise | Account-Service (Laravel 12)

---

> **⚠️ CATATAN PENTING**
>
> Log ini merupakan **rangkuman prompting untuk Tugas 3** yang diambil dari sesi percakapan bercampur antara Tugas 2 dan Tugas 3.
>
> 🔗 **Link percakapan asli:** [https://gemini.google.com/app/eda68ffe0dbaf823?hl=id](https://gemini.google.com/app/eda68ffe0dbaf823?hl=id)
>
> 🟢 **Prompt pertama Tugas 3 dimulai dari kata kunci:**
> *"alooo gemini, ini aku lanjut mengerjakan tugas 3 integrasi aplikasi enterprise menggunakan laravel 12..."*
>
> Percakapan sebelum kalimat tersebut merupakan bagian dari Tugas 2 yang ada di room yang sama.

---

## 🗂️ Ringkasan Sesi

| Info | Detail |
|------|--------|
| **Mata Kuliah** | Integrasi Aplikasi Enterprise (IAE) |
| **Tugas** | Tugas 3 — Modul SSO, SOAP Audit, AMQP Publisher |
| **Microservice** | `account-service` (Laravel 12) |
| **AI yang Digunakan** | Google Gemini |
| **Tanggal Ekspor** | 13 Juni 2026 |
| **Total Modul Dikerjakan** | 3 Modul (SSO + SOAP + AMQP) |

---

## 📌 Modul 1 — Federated SSO dengan JWT

### Prompt 1 — Meminta Arsitektur & Middleware `VerifyFederatedSso`

**Konteks:** Memulai Tugas 3, skenario SSO terpusat di mana aplikasi tidak mengurus login sendiri, melainkan menerima JWT dari cloud eksternal dosen.

**Isi Prompt (Ringkasan):**
> Meminta pembuatan custom middleware `VerifyFederatedSso` dengan ketentuan ketat:
> 1. Ekstrak & dekode JWT **manual** tanpa library auth bawaan Laravel Sanctum (boleh pakai `firebase/php-jwt`)
> 2. Validasi klaim `role` dari payload JWT ke database lokal (tabel `roles`)
> 3. Proteksi endpoint kritis — kembalikan 401 jika token tidak valid/expired/role tidak sesuai
>
> Diminta: penjelasan arsitektur ringkas + kode middleware utuh + cara mendaftarkan di Laravel 12.

**Teknik Prompting yang Digunakan:**
- ✅ Memberikan konteks proyek secara spesifik (nama microservice, framework, relasi dengan tugas sebelumnya)
- ✅ Merinci ketentuan dengan numbered list yang jelas dan ketat
- ✅ Menyebutkan output yang diinginkan secara eksplisit ("jangan beri potongan kode setengah-setengah")

**Hasil yang Didapat:**
- Penjelasan arsitektur Identity Provider (IdP) vs Service Provider (SP)
- Kode `VerifyFederatedSso.php` lengkap menggunakan algoritma `HS256`
- Cara instalasi `firebase/php-jwt` via Composer
- Konfigurasi `.env` untuk `SSO_JWT_SECRET`
- Pendaftaran middleware di `bootstrap/app.php` (Laravel 12 style)
- Penerapan di `routes/api.php` dengan alias `sso.federated`

---

### Prompt 2 — Revisi Middleware Berdasarkan Screenshot Spesifikasi API Dosen

**Konteks:** Setelah mendapat middleware awal, ditemukan bahwa spesifikasi asli dari dosen berbeda — menggunakan algoritma asimetris RS256 dengan JWKS endpoint, bukan secret key statis.

**Isi Prompt (Ringkasan):**
> Melampirkan 4 screenshot dokumen referensi API dari dosen, lalu meminta pembuatan ulang middleware dengan ketentuan yang sesuai screenshot tersebut.

**Teknik Prompting yang Digunakan:**
- ✅ Melampirkan bukti visual (screenshot) sebagai referensi konkret
- ✅ Membiarkan AI menganalisis perbedaan dan menjelaskan perubahan yang diperlukan
- ✅ Prompt singkat tapi kontekstual ("buat ulang eksekusinya tapi pakai ketentuan... seperti pada screenshot")

**Hasil yang Didapat:**
- Perubahan fundamental: dari `HS256` (symmetric) → `RS256` (asymmetric)
- Middleware baru yang mengambil Public Key secara dinamis dari endpoint `GET /api/v1/auth/jwks`
- Implementasi **caching JWKS selama 1 jam** menggunakan `Cache::remember()` agar performa tetap optimal
- Penggunaan `JWK::parseKeySet()` dari library Firebase untuk parse JWKS

---

## 📌 Modul 2 — SOAP Audit ke Server Pusat Dosen

### Prompt 3 — Memahami Cara Parsing Response XML (ReceiptNumber)

**Konteks:** Sebelum menyatukan modul, perlu dipahami cara mengekstrak `ReceiptNumber` dari response XML SOAP yang dikirim balik oleh server dosen.

**Isi Prompt (Ringkasan):**
> Bertanya tentang cara mengambil nilai `ReceiptNumber` dari response XML SOAP server dosen, mengingat XML menggunakan namespace (`iae:`).

**Hasil yang Didapat:**
- Penjelasan penggunaan `simplexml_load_string()` untuk parsing XML
- Teknik `registerXPathNamespace()` untuk mengenali namespace `iae:`
- Penggunaan `xpath('//iae:ReceiptNumber')` untuk mengekstrak nilai secara tepat

---

### Prompt 4 — Menggabungkan Modul 1 (SSO) dan Modul 2 (SOAP) ke `validateAccount`

**Konteks:** SSO dan SOAP sudah dipahami secara terpisah, saatnya digabung menjadi satu alur utuh.

**Isi Prompt (Ringkasan):**
> Meminta kode utuh fungsi `validateAccount` yang menggabungkan:
> 1. Ambil JWT yang sudah lolos dari middleware
> 2. Rakit string XML manual untuk dikirim ke server SOAP dosen
> 3. Parse response XML untuk mengambil `ReceiptNumber`
> 4. Update status akun di DB lokal jadi `verified` sekaligus simpan `receipt_no`

**Teknik Prompting yang Digunakan:**
- ✅ Mendeskripsikan alur secara berurutan dan runtut
- ✅ Meminta "kode utuh" — tidak mau potongan kode
- ✅ Menentukan output akhir yang diharapkan (fungsi siap pakai)

**Hasil yang Didapat:**
- Fungsi `validateAccount` lengkap end-to-end dengan 9 langkah berkomenter
- Pengiriman SOAP menggunakan `Http::withToken()->withHeaders()->send()`
- Parsing `ReceiptNumber` dengan XPath
- Placeholder DB update siap dibuka saat database aktif

---

### Prompt 5 — Konfirmasi Struktur Kode (Ya/Tidak)

**Konteks:** Setelah menyusun ulang kode sendiri berdasarkan panduan AI, meminta validasi apakah struktur sudah benar.

**Isi Prompt (Ringkasan):**
> Mengirim seluruh isi file `AccountController.php` yang sudah ditulis sendiri, meminta konfirmasi "ya atau tidak saja" apakah struktur dan penggabungannya sudah benar.

**Teknik Prompting yang Digunakan:**
- ✅ Melampirkan kode aktual yang ditulis untuk diverifikasi
- ✅ Batasan jawaban ("ya atau tidak saja") agar respons lebih fokus

**Hasil yang Didapat:**
- Konfirmasi bahwa kode 100% benar dan struktur penggabungannya sempurna

---

### Prompt 6 — Error: ReceiptNumber Tidak Ditemukan

**Konteks:** Setelah diuji di Postman, muncul error `"ReceiptNumber tidak ditemukan pada response SOAP"`.

**Isi Prompt (Ringkasan):**
> Melampirkan screenshot error dari Postman dan bertanya apa penyebabnya.

**Hasil yang Didapat:**
- Analisis: kemungkinan format `TeamID` salah (`TEAM-04` vs `TEAM04`)
- Solusi awal: ganti `TEAM-04` → `TEAM04`

---

### Prompt 7 — Revisi: Menambah `X-IAE-KEY` Mahasiswa ke Header

**Konteks:** Setelah cek spesifikasi API di LMS, ditemukan bahwa server SOAP memerlukan `api_key` mahasiswa, bukan hanya token JWT warga.

**Isi Prompt (Ringkasan):**
> Menjelaskan temuan dari LMS bahwa server SOAP perlu header `X-IAE-KEY` dengan nilai `KEY-MHS-274`, lalu meminta revisi kode controller.

**Teknik Prompting yang Digunakan:**
- ✅ Proaktif menelaah dokumen sumber (LMS) sendiri, bukan hanya mengandalkan AI
- ✅ Menyertakan kode yang perlu direvisi secara lengkap

**Hasil yang Didapat:**
- Kode controller direvisi dengan penambahan header `'X-IAE-KEY' => 'KEY-MHS-274'`
- Konfirmasi format `TEAM04` (tanpa strip) sudah dikoreksi

---

### Prompt 8 — Error Berlanjut, Meminta Revisi Alur Total (M2M Auth)

**Konteks:** Error `ReceiptNumber tidak ditemukan` masih terjadi. Saya mempertanyakan apakah token warga + `X-IAE-KEY` memang perlu, atau ada konsep yang salah.

**Isi Prompt (Ringkasan):**
> Meminta AI tidak berspekulasi. Bertanya secara konseptual: apakah sebelum nembak SOAP harus ada request token dulu ke SSO? Dan apakah token warga serta header `X-IAE-KEY` masih relevan?

**Teknik Prompting yang Digunakan:**
- ✅ Meminta AI untuk tidak berspekulasi ("jangan spekulasi lagi deh")
- ✅ Mengajukan pertanyaan konseptual terlebih dahulu sebelum minta kode
- ✅ Melampirkan kode eksisting untuk konteks revisi

**Hasil yang Didapat:**
- Penjelasan konsep **Machine-to-Machine (M2M) Authentication**
- Klarifikasi alur yang benar:
  1. Token warga → hanya untuk lolos middleware lokal
  2. Aplikasi login sebagai "mesin" ke SSO dengan `api_key` → dapat M2M Token
  3. M2M Token digunakan sebagai Bearer saat kirim SOAP
- Kode controller direvisi total dengan alur M2M yang benar

---

## 📌 Modul 3 — AMQP Publisher via HTTP Bridge

### Prompt 9 — Klarifikasi Konsep HTTP Bridge RabbitMQ

**Konteks:** AI menyebut "HTTP Bridge" untuk RabbitMQ. Mahasiswa belum paham dan meminta penjelasan dengan analogi.

**Isi Prompt (Ringkasan):**
> Bertanya: "hahhh whattt http bridge apaan, jangan asal dulu, bentar2 coba jelasin pakai analogi"

**Teknik Prompting yang Digunakan:**
- ✅ Meminta penjelasan dengan **analogi** agar mudah dipahami
- ✅ Berani meminta klarifikasi sebelum langsung minta kode

**Hasil yang Didapat:**
- Analogi balap F1: HTTP Bridge = Race Engineer di pit wall, bukan langsung ngobrol ke semua mekanik via radio frekuensi khusus
- Konfirmasi bahwa endpoint `/api/v1/messages/publish` memang sudah disediakan dosen di referensi API
- Tidak perlu install `php-amqplib` karena dosen sudah sediakan HTTP Bridge

---

### Prompt 10 — Konfirmasi Pemahaman Konsep Sebelum Minta Kode

**Konteks:** Setelah mendapat analogi, mahasiswa mengkonfirmasi pemahamannya sendiri dengan kata-katanya.

**Isi Prompt (Ringkasan):**
> "oalaahhh jadi intinya kita tuh kaya buat json aja alias ibaratnya surat lah, nah nantinya surat ini tuh kita buat dan kirim pakai bahasa json, terus karena http bridge alias kita anggap kaya ini orang adalah asisten, dialah yang ngubah bahasa amqp tadi dan ngirim ke server pusat punya pak eky, gitu?"

**Teknik Prompting yang Digunakan:**
- ✅ Memverifikasi pemahaman sendiri dengan kata-kata sendiri sebelum lanjut
- ✅ Mereferensikan dokumen tugas ("di hal 4 di modul 3 katanya berhasil mengirimkan event berbentuk json") sebagai validasi tambahan

**Hasil yang Didapat:**
- Konfirmasi pemahaman 100% akurat
- AI memberikan kode fase 4 AMQP Publisher — menambahkan `$eventPayload` JSON dan menembakkan ke `/api/v1/messages/publish` menggunakan M2M Token yang sama

---

### Prompt 11 — Berhasil! Lalu Mempercantik Payload JSON RabbitMQ

**Konteks:** Setelah berhasil mendapat `broadcast_status: success` dan `receipt_no` di Postman, mahasiswa ingin mempercantik struktur JSON event agar lebih profesional seperti milik tim lain.

**Isi Prompt (Ringkasan):**
> "aku liat di board punya pak eky tuh tim lain isi json nya keren dan panjang banget... punya aku pendek banget cuma isi payload dasar doang AKWOKAOKWAKAWO"
>
> Meminta revisi `$eventPayload` dengan struktur:
> - `event_name`: `account.validated`
> - `service_name`: `Account-Service`
> - `api_version`: `v1`
> - `team_id`: `TEAM04`
> - `timestamp`
> - `data`: objek akun lengkap (id, nama, email, status\_validasi, receipt\_no)

**Teknik Prompting yang Digunakan:**
- ✅ Memberikan spesifikasi struktur JSON yang diinginkan secara detail
- ✅ Meminta kode utuh file controller agar bisa langsung di-replace di VS Code

**Hasil yang Didapat:**
- `AccountController.php` versi final dengan payload JSON terstruktur lengkap mengikuti standar enterprise event-driven architecture
- Fungsi `validateAccount` kini menjadi **orchestrator** yang menangani 5 fase sekaligus dalam satu klik

---

## 🏁 Hasil Akhir — Alur `validateAccount` Final

```
POST /api/v1/accounts/{id}/validate
        │
        ▼
[Middleware] VerifyFederatedSso
  → Ambil JWT dari header Authorization
  → Fetch Public Key (JWKS) dari SSO Dosen (cached 1 jam)
  → Dekode & verifikasi signature RS256
  → Validasi klaim `role` ke tabel `roles` DB lokal
        │
        ▼
[Controller] validateAccount()
  │
  ├── FASE 1: POST ke /api/v1/auth/token → Dapat M2M Token (login sebagai mesin)
  │
  ├── FASE 2: Rakit SOAP Envelope XML + Kirim ke /soap/v1/audit dengan M2M Token
  │
  ├── FASE 3: Parse XML response → Ekstrak ReceiptNumber via XPath
  │
  ├── FASE 4: Kirim event JSON ke /api/v1/messages/publish (RabbitMQ HTTP Bridge)
  │
  └── FASE 5: Return response sukses dengan receipt_no & broadcast_status: "success"
```

---

## 💡 Pola Prompting yang Efektif (Refleksi)

| Teknik | Contoh Penerapan |
|--------|-----------------|
| **Konteks spesifik** | Selalu menyebut nama microservice, framework, dan relasi tugas sebelumnya |
| **Ketentuan numbered list** | Merinci syarat middleware dengan poin bernomor |
| **Lampirkan bukti visual** | Screenshot Postman, screenshot dokumen LMS |
| **Minta analogi** | "jelasin pakai analogi" saat konsep baru terasa asing |
| **Konfirmasi pemahaman diri** | Mengulang pemahaman dengan kata sendiri sebelum minta kode |
| **Batasi format jawaban** | "ya atau tidak saja" saat hanya butuh validasi |
| **Tidak spekulasi** | "jangan spekulasi lagi deh" saat debugging error |
| **Minta kode utuh** | "buatin kode utuh... biar langsung aku ganti" agar tidak perlu tebak-tebakan posisi kode |

---

*Log ini dibuat sebagai bagian dari luaran Tugas 3 IAE — bukti akuntabilitas penggunaan AI dalam proses pengerjaan.*

> **📝 Catatan Pembuatan Log**
>
> Log prompting ini sendiri dibuat dengan bantuan AI (Claude dari Anthropic) berdasarkan ekspor percakapan mentah dari Google Gemini.