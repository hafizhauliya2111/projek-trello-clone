# Trello-Style Task Manager

Aplikasi manajemen tugas bergaya Trello (board → list → card), dibangun dengan PHP native, MySQL, dan Vanilla JS.

## Requirement
- PHP >= 8.0
- MySQL / MariaDB
- Web server (built-in PHP server sudah cukup untuk development)

## Setup

1. **Import database**
   ```bash
   mysql -u root -p < database.sql
   ```

2. **Konfigurasi koneksi database**

   Edit `config/db.php`, sesuaikan `DB_USER` dan `DB_PASS` dengan MySQL lokal kamu.

3. **Jalankan development server**
   ```bash
   php -S localhost:8000 -t public
   ```
   Lalu buka `http://localhost:8000` di browser.

## Struktur Project

```
config/     -> koneksi database
includes/   -> helper & session guard (dishare ke semua halaman)
public/     -> semua halaman yang diakses browser (document root)
api/        -> endpoint AJAX (dipanggil dari public/js)
database.sql -> schema database
```

## Status Pengembangan

Lihat `project-plan.md` untuk requirement lengkap dan sprint plan.

- [x] Sprint 0 — Setup project & struktur folder
- [ ] Sprint 1 — Auth
- [ ] Sprint 2 — CRUD Board
- [ ] Sprint 3 — CRUD List
- [ ] Sprint 4 — CRUD Card
- [ ] Sprint 5 — Reorder/pindah card & list
- [ ] Sprint 6 — Label & Due Date
- [ ] Sprint 7 — Search & Filter
- [ ] Sprint 8 — Drag & Drop upgrade
- [ ] Sprint 9 — Testing & hardening
