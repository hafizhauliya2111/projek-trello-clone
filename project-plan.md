# Trello-Style Task Manager — Project Plan

## 1. Overview
Aplikasi manajemen tugas bergaya Trello (board → list → card) dengan solo development, dibangun tanpa framework untuk memperkuat fundamental.

**Stack:** PHP native, MySQL, HTML/CSS/Vanilla JS

## 2. Functional Requirements

### Core
- [ ] User bisa register, login, logout
- [ ] User hanya bisa melihat/mengelola board miliknya sendiri
- [ ] User bisa membuat, mengedit, menghapus **board**
- [ ] User bisa membuat, mengedit, menghapus, mengurutkan ulang **list** dalam board
- [ ] User bisa membuat, mengedit, menghapus, memindahkan **card** antar list
- [ ] Pemindahan/reorder card & list versi awal pakai tombol/dropdown (bukan drag & drop), upgrade ke HTML5 Drag & Drop API di sprint lanjutan

### Fitur Tambahan (MVP)
- [ ] **Label/warna card** — user bisa assign 1+ label warna ke card
- [ ] **Due date** — card bisa punya tanggal jatuh tempo, tampil visual beda warna kalau overdue/mendekati deadline
- [ ] **Search/filter card** — cari card berdasarkan judul, filter berdasarkan label/due date dalam satu board

## 3. Non-Functional Requirements
- Password di-hash (`password_hash`/`password_verify`), tidak pernah disimpan plaintext
- Semua query pakai **prepared statements** (PDO) — no raw string concat ke SQL
- Validasi input di server-side, tidak percaya validasi client-side saja
- Proteksi CSRF pada form (token tersembunyi)
- Escape output ke HTML (`htmlspecialchars`) untuk cegah XSS
- Autentikasi wajib di setiap halaman/endpoint yang mengakses data board

## 4. Entity Relationship Design (ERD)

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE boards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE lists (
  id INT AUTO_INCREMENT PRIMARY KEY,
  board_id INT NOT NULL,
  title VARCHAR(100) NOT NULL,
  position INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (board_id) REFERENCES boards(id) ON DELETE CASCADE
);

CREATE TABLE cards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  list_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  due_date DATE NULL,
  position INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (list_id) REFERENCES lists(id) ON DELETE CASCADE
);

CREATE TABLE labels (
  id INT AUTO_INCREMENT PRIMARY KEY,
  board_id INT NOT NULL,
  name VARCHAR(50) NOT NULL,
  color VARCHAR(7) NOT NULL, -- hex code, misal #FF5733
  FOREIGN KEY (board_id) REFERENCES boards(id) ON DELETE CASCADE
);

CREATE TABLE card_labels (
  card_id INT NOT NULL,
  label_id INT NOT NULL,
  PRIMARY KEY (card_id, label_id),
  FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE CASCADE,
  FOREIGN KEY (label_id) REFERENCES labels(id) ON DELETE CASCADE
);
```

**Catatan desain:**
- `position` di `lists` dan `cards` menyimpan urutan tampilan — inti logic reorder/pindah
- `labels` scoped per board (bukan global), jadi tiap board punya set label sendiri
- `card_labels` tabel pivot many-to-many (1 card bisa multi label)
- Search/filter tidak butuh tabel baru — cukup query `WHERE title LIKE ?` dan join ke `card_labels`/`due_date`

## 5. Arsitektur: Full API + Frontend Terpisah

Diputuskan (setelah Sprint 1 dimulai) untuk memisahkan backend dan frontend sepenuhnya, bukan menggabung PHP+HTML dalam satu file:

- **`public/`** — HANYA HTML/CSS/JS statis. Tidak ada logic PHP sama sekali di sini.
- **`api/`** — HANYA PHP yang menerima request dan mengembalikan **JSON**. Tidak pernah merender HTML.
- Komunikasi antara keduanya lewat `fetch()` di JavaScript (AJAX), bukan form submit biasa.
- CSRF token diambil frontend lewat endpoint `api/csrf.php` sebelum submit apa pun, disimpan di variabel JS, dikirim di body request.
- Validasi dilakukan di 2 tempat: JS (feedback cepat ke user) DAN PHP (validasi sesungguhnya, karena JS bisa dimatikan/dimanipulasi).
- Session login (`$_SESSION`) tetap dipakai seperti biasa — cookie session otomatis terkirim tiap `fetch()` ke domain yang sama.

## 6. Folder Structure

```
/trello-clone
  /config
    db.php                 // koneksi PDO
  /includes
    auth.php               // session guard, redirect kalau belum login
    functions.php          // helper: escape, csrf token, dll
  /public                   // FULL statis, tidak ada PHP
    /css
      style.css
    /js
      register.js
      login.js
      board.js              // load & render board/list/card
      card-actions.js       // create/edit/delete/move card
      search-filter.js
    index.html
    login.html
    register.html
    board.html
  /api                       // FULL backend, selalu balikin JSON
    csrf.php                 // kasih token CSRF ke frontend
    register.php
    login.php
    logout.php
    create_board.php
    create_list.php
    create_card.php
    move_card.php
    move_list.php
    delete_card.php
    search_card.php
    manage_label.php
  database.sql               // dump schema di atas
  README.md
```

## 7. Sprint Plan

| Sprint | Fokus | Definition of Done |
|---|---|---|
| 0 | Setup project, git, struktur folder, koneksi DB | Bisa konek ke MySQL, struktur folder siap, repo git ter-init |
| 1 | Auth (register, login, logout, session guard) | User bisa daftar & login, halaman terproteksi tidak bisa diakses tanpa login |
| 2 | CRUD Board | User bisa create/edit/delete board miliknya sendiri |
| 3 | CRUD List | List bisa dibuat, diedit, dihapus, urutan tersimpan |
| 4 | CRUD Card | Card bisa dibuat, diedit, dihapus dalam list |
| 5 | Pindah/reorder card & list (tombol/select) | Posisi card/list berubah di DB & tampilan tanpa reload (AJAX) |
| 6 | Label & Due Date | Card bisa diberi label warna & due date, tampil visual beda kalau overdue |
| 7 | Search & Filter | Bisa cari card by judul, filter by label/due date |
| 8 | Upgrade ke Drag & Drop (HTML5 API) | Drag card/list antar posisi, backend logic reuse dari Sprint 5 |
| 9 | Testing, hardening security, dokumentasi | Checklist keamanan lolos, README lengkap |

## 8. Security Checklist (dicek ulang di Sprint 9)
- [ ] Semua query pakai prepared statement
- [ ] Password di-hash
- [ ] CSRF token di semua form POST
- [ ] Validasi ownership (user hanya akses board/list/card miliknya)
- [ ] Output di-escape (`htmlspecialchars`)
- [ ] Session diregenerate setelah login (`session_regenerate_id`)