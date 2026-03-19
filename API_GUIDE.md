# API Guide – HRIS Integration Plugin (local_hris)
## Developed by Digitos Multimedia Synergy - 2026

Dokumentasi teknis lengkap REST API untuk integrasi sistem HRIS dengan Moodle.

> 📐 Untuk arsitektur sistem, sequence diagrams, security model, dan panduan integrasi, lihat **[README.md](README.md)**.

---

## 🚀 API Endpoints

### Ringkasan Fungsi

| Fungsi | Tipe | Parameter | Deskripsi |
|--------|------|-----------|-----------|
| `local_hris_get_active_courses` | Read | apikey | Daftar kursus yang aktif/visible |
| `local_hris_get_course_participants` | Read | apikey, courseid | Daftar peserta yang terdaftar |
| `local_hris_get_course_results` | Read | apikey, courseid, userid | Hasil belajar beserta skor |
| `local_hris_get_all_course_results` | Read | apikey, courseid | Hasil belajar + skor kuesioner |

---

### 1. Get Active Courses

**Function**: `local_hris_get_active_courses`

Mengembalikan daftar semua kursus yang visible/aktif di sistem.

**Response Fields**:

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `id` | int | Course ID |
| `shortname` | string | Nama pendek kursus |
| `fullname` | string | Nama lengkap kursus |
| `summary` | string | Deskripsi kursus (tanpa HTML) |
| `startdate` | int | Timestamp mulai kursus |
| `enddate` | int | Timestamp akhir kursus |
| `visible` | int | Flag visibilitas kursus |
| `category_id` | int | ID kategori kursus |
| `category_name` | string | Nama kategori kursus |
| `training_host` | string | Tipe penyelenggaraan pelatihan (`internal` atau `external`; default: `internal`) |

---

### 2. Get Course Participants

**Function**: `local_hris_get_course_participants`

Mengembalikan daftar peserta yang terdaftar di kursus.

**Parameters**:

| Parameter | Tipe | Wajib | Default | Deskripsi |
|-----------|------|-------|---------|-----------|
| `courseid` | int | Tidak | 0 | Course ID tertentu (0 = semua kursus) |

**Response Fields**:

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `user_id` | int | User ID |
| `email` | string | Alamat email pengguna |
| `firstname` | string | Nama depan |
| `lastname` | string | Nama belakang |
| `company_name` | string | Nama cabang/organisasi (dari profil field `branch`) |
| `course_id` | int | Course ID |
| `course_shortname` | string | Nama pendek kursus |
| `course_name` | string | Nama lengkap kursus |
| `training_host` | string | Tipe penyelenggaraan pelatihan (`internal` atau `external`; default: `internal`) |
| `role_name` | string | Peran pengguna di kursus (misal: `student`, `teacher`, `editingteacher`) |
| `enrollment_date` | int | Timestamp pendaftaran |

---

### 3. Get Course Results

**Function**: `local_hris_get_course_results`

Hasil belajar lengkap dengan skor pre-test dan post-test.

**Parameters**:

| Parameter | Tipe | Wajib | Default | Deskripsi |
|-----------|------|-------|---------|-----------|
| `courseid` | int | Tidak | 0 | Course ID tertentu (0 = semua kursus) |
| `userid` | int | Tidak | 0 | User ID tertentu (0 = semua pengguna) |

**Response Fields**:

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `user_id` | int | User ID |
| `email` | string | Alamat email pengguna |
| `firstname` | string | Nama depan |
| `lastname` | string | Nama belakang |
| `company_name` | string | Nama cabang/organisasi (dari custom field `branch`) |
| `course_id` | int | Course ID |
| `course_shortname` | string | Nama pendek kursus |
| `course_name` | string | Nama lengkap kursus |
| `training_host` | string | Tipe penyelenggaraan pelatihan (`internal` atau `external`; default: `internal`) |
| `role_name` | string | Peran pengguna di kursus (misal: `student`, `teacher`, `editingteacher`) |
| `final_grade` | float | Nilai akhir kursus |
| `pretest_score` | float | Skor pre-test (custom field `jenis_quiz` = 2) |
| `posttest_score` | float | Skor post-test (custom field `jenis_quiz` = 3) |
| `completion_date` | int | Timestamp penyelesaian kursus (0 jika belum selesai) |
| `is_completed` | int | Status penyelesaian (1 = selesai, 0 = belum) |

---

### 4. Get All Course Results (dengan Skor Kuesioner)

**Function**: `local_hris_get_all_course_results`

Hasil belajar agregat termasuk skor kuesioner per pengguna dan kursus.

**Parameters**:

| Parameter | Tipe | Wajib | Default | Deskripsi |
|-----------|------|-------|---------|-----------|
| `courseid` | int | Tidak | 0 | Course ID tertentu (0 = semua kursus) |

**Response Fields**:

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| `course_id` | int | Course ID |
| `course_name` | string | Nama lengkap kursus |
| `course_shortname` | string | Nama pendek kursus |
| `training_host` | string | Tipe penyelenggaraan pelatihan (`internal` atau `external`; default: `internal`) |
| `user_id` | int | User ID |
| `firstname` | string | Nama depan |
| `lastname` | string | Nama belakang |
| `email` | string | Alamat email pengguna |
| `company_name` | string | Nama cabang/organisasi (dari custom field `branch`) |
| `role_name` | string | Peran pengguna di kursus (misal: `student`, `teacher`, `editingteacher`) |
| `final_grade` | float | Nilai akhir kursus |
| `pretest_score` | float | Skor pre-test (custom field `jenis_quiz` = 2) |
| `posttest_score` | float | Skor post-test (custom field `jenis_quiz` = 3) |
| `completion_date` | int | Timestamp penyelesaian kursus (0 jika belum selesai) |
| `is_completed` | int | Status penyelesaian (1 = selesai, 0 = belum) |
| `questionnaire_available` | int | 1 jika skor kuesioner tersedia, 0 jika tidak |
| `score_materi` | float | Rata-rata skor pertanyaan 1–3 (Materi) |
| `score_trainer` | float | Rata-rata skor pertanyaan 4–6 (Trainer) |
| `score_fasilitas` | float | Rata-rata skor pertanyaan 7–9 (Fasilitas/Venue) |
| `score_total` | float | Rata-rata skor keseluruhan |

---

##  Query Logic Explanation

### Pre/Post Test Detection

Plugin mendeteksi quiz pre-test dan post-test menggunakan nilai custom field pada course modules:

**Konfigurasi Custom Field**:
- Nama field: `jenis_quiz`
- Diterapkan pada: Course modules (quiz instances)
- Nilai:
  - `2` = PreTest
  - `3` = PostTest
  - `1` = Normal

**Langkah Setup**:
1. Buat custom field pada course modules dengan shortname `jenis_quiz`
2. Untuk setiap quiz, set nilai custom field (2 untuk pre-test, 3 untuk post-test)
3. Skor diambil dari tabel `grade_grades` menggunakan custom field sebagai filter

**Metode Deteksi**:
```sql
-- Pre-test: Custom field value = 2
JOIN {customfield_data} cfd ON cfd.instanceid = cm.id AND cfd.value = '2'

-- Post-test: Custom field value = 3
JOIN {customfield_data} cfd ON cfd.instanceid = cm.id AND cfd.value = '3'
```

### Questionnaire Score Calculation

Skor kuesioner hanya tersedia di `local_hris_get_all_course_results`.

**Ringkasan Logika**:
- Mencari modul questionnaire yang visible di kursus.
- Menemukan Rate question pertama (`type_id = 8`).
- Jika respons tersedia:
  - Saat Rate question memiliki **tepat 9 pilihan**:
    - `score_materi` = rata-rata pilihan 1–3
    - `score_trainer` = rata-rata pilihan 4–6
    - `score_fasilitas` = rata-rata pilihan 7–9
    - `score_total` = rata-rata semua 9 pilihan
    - `questionnaire_available` = 1
  - Saat Rate question memiliki jumlah pilihan **berbeda dari 9**:
    - `score_total` = rata-rata semua pilihan
    - `questionnaire_available` = 1 jika `score_total` > 0, selainnya 0
    - `score_materi`, `score_trainer`, `score_fasilitas` = 0
- Jika tidak ada questionnaire, Rate question, atau respons: semua skor = 0 dan `questionnaire_available` = 0

---

## 🔧 API Usage

### Konfigurasi Endpoint

**Base URL**: `https://yourmoodle.com/webservice/rest/server.php`

**HTTP Method**: `POST`

**Content-Type**: `application/x-www-form-urlencoded`

### Required Parameters (Semua Fungsi)

| Parameter | Tipe | Deskripsi |
|-----------|------|-----------|
| `wstoken` | string | Web service token (dari Moodle) |
| `wsfunction` | string | Nama fungsi yang dipanggil |
| `moodlewsrestformat` | string | Format respons (`json` atau `xml`) |
| `apikey` | string | API key plugin (dari pengaturan) |

### Authentication

Semua API call memerlukan:
- `wstoken`: Web service token
- `apikey`: HRIS API key (dikonfigurasi di pengaturan plugin)

---

### Contoh Request (cURL)

#### Contoh 1: Get Active Courses
```bash
curl -X POST "https://yourmoodle.com/webservice/rest/server.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "wstoken=YOUR_WS_TOKEN" \
  -d "wsfunction=local_hris_get_active_courses" \
  -d "moodlewsrestformat=json" \
  -d "apikey=YOUR_API_KEY"
```

#### Contoh 2: Get Participants untuk Kursus Tertentu
```bash
curl -X POST "https://yourmoodle.com/webservice/rest/server.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "wstoken=YOUR_WS_TOKEN" \
  -d "wsfunction=local_hris_get_course_participants" \
  -d "moodlewsrestformat=json" \
  -d "apikey=YOUR_API_KEY" \
  -d "courseid=5"
```

#### Contoh 3: Get Results untuk Semua Pengguna di Semua Kursus
```bash
curl -X POST "https://yourmoodle.com/webservice/rest/server.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "wstoken=YOUR_WS_TOKEN" \
  -d "wsfunction=local_hris_get_course_results" \
  -d "moodlewsrestformat=json" \
  -d "apikey=YOUR_API_KEY" \
  -d "courseid=0" \
  -d "userid=0"
```

#### Contoh 4: Get Results untuk Pengguna Tertentu di Kursus Tertentu
```bash
curl -X POST "https://yourmoodle.com/webservice/rest/server.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "wstoken=YOUR_WS_TOKEN" \
  -d "wsfunction=local_hris_get_course_results" \
  -d "moodlewsrestformat=json" \
  -d "apikey=YOUR_API_KEY" \
  -d "courseid=5" \
  -d "userid=123"
```

#### Contoh 5: Get All Course Results (dengan Skor Kuesioner)
```bash
curl -X POST "https://yourmoodle.com/webservice/rest/server.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "wstoken=YOUR_WS_TOKEN" \
  -d "wsfunction=local_hris_get_all_course_results" \
  -d "moodlewsrestformat=json" \
  -d "apikey=YOUR_API_KEY" \
  -d "courseid=0"
```

---

### Contoh Response

#### Success Response – Get Active Courses
```json
[
  {
    "id": 2,
    "shortname": "course101",
    "fullname": "Introduction to Programming",
    "summary": "Learn basic programming concepts",
    "startdate": 1703980800,
    "enddate": 1706659200,
    "visible": 1,
    "category_id": 3,
    "category_name": "IT Training",
    "training_host": "internal"
  },
  {
    "id": 3,
    "shortname": "webdev101",
    "fullname": "Web Development Fundamentals",
    "summary": "Master HTML, CSS, and JavaScript",
    "startdate": 1704067200,
    "enddate": 1706745600,
    "visible": 1,
    "category_id": 3,
    "category_name": "IT Training",
    "training_host": "external"
  }
]
```

#### Success Response – Get Course Participants
```json
[
  {
    "user_id": 45,
    "email": "john.doe@company.com",
    "firstname": "John",
    "lastname": "Doe",
    "company_name": "Tech Corp",
    "course_id": 5,
    "course_shortname": "course101",
    "course_name": "Introduction to Programming",
    "training_host": "internal",
    "role_name": "student",
    "enrollment_date": 1704153600
  }
]
```

#### Success Response – Get Course Results
```json
[
  {
    "user_id": 45,
    "email": "john.doe@company.com",
    "firstname": "John",
    "lastname": "Doe",
    "company_name": "Tech Corp",
    "course_id": 5,
    "course_shortname": "course101",
    "course_name": "Introduction to Programming",
    "training_host": "internal",
    "role_name": "student",
    "final_grade": 85.5,
    "pretest_score": 65.0,
    "posttest_score": 90.0,
    "completion_date": 1706659200,
    "is_completed": 1
  }
]
```

#### Success Response – Get All Course Results
```json
[
  {
    "course_id": 5,
    "course_name": "Introduction to Programming",
    "course_shortname": "course101",
    "training_host": "internal",
    "user_id": 45,
    "firstname": "John",
    "lastname": "Doe",
    "email": "john.doe@company.com",
    "company_name": "Tech Corp",
    "role_name": "student",
    "final_grade": 85.5,
    "pretest_score": 65.0,
    "posttest_score": 90.0,
    "completion_date": 1706659200,
    "is_completed": 1,
    "questionnaire_available": 1,
    "score_materi": 4.33,
    "score_trainer": 4.67,
    "score_fasilitas": 4.00,
    "score_total": 4.33
  }
]
```

#### Error Response – Invalid API Key
```json
{
  "exception": "moodle_exception",
  "errorcode": "invalidapikey",
  "message": "Invalid API key"
}
```

#### Error Response – Invalid Web Service Token
```json
{
  "exception": "webservice_access_exception",
  "errorcode": "accessexception",
  "message": "Access control exception"
}
```

---

*Dokumentasi ini adalah bagian dari plugin [local_hris](README.md).*
