````markdown
# 📖 tooltrans

> Web đọc truyện tự động bằng Text-to-Speech (edge-tts + PHP + ffmpeg), chạy local.

---

## 📂 Cấu trúc thư mục
```bash
web-doc-truyen/
├── index.php        # Giao diện & JavaScript
├── api.php          # Backend AJAX dispatcher
├── tts.php          # Gọi edge-tts
├── temp/            # File MP3 tạm (tự tạo)
├── output/          # File MP3 hoàn chỉnh
└── README.md
````

---

## ⚙️ Yêu cầu cài đặt

### 1. Python + edge-tts

```bash
pip install edge-tts
edge-tts --version
```

---

### 2. ffmpeg

* Tải: [https://ffmpeg.org/download.html](https://ffmpeg.org/download.html)
* Giải nén vào: `C:\ffmpeg\`
* Thêm vào PATH: `C:\ffmpeg\bin`

Kiểm tra:

```bash
ffmpeg -version
```

---

### 3. XAMPP (PHP) — bật exec()

Mở file:

```
C:\xampp\php\php.ini
```

Tìm dòng:

```
disable_functions = ...
```

➡️ Xóa `exec` khỏi danh sách
➡️ Restart Apache

---

### 4. Chạy project

Copy project vào:

```
C:\xampp\htdocs\web-doc-truyen\
```

Truy cập:

```
http://localhost/web-doc-truyen/
```

---

## 🔄 Luồng hoạt động

```text
Browser → api.php → tts.php → edge-tts

POST clean_html      → xử lý & chia text thành chunks
POST generate_chunk  → tạo file MP3 từng phần
POST merge_audio     → ghép các file MP3 thành 1 file

→ Trả về audio + link tải
```

---

## ⚠️ Lưu ý

* Tiếng Việt: sử dụng file `.txt` UTF-8 BOM để tránh lỗi encoding trên Windows
* Session riêng cho mỗi request → tránh ghi đè file
* Sau khi merge audio → tự động xóa file trong `temp/`

---

## 🚀 Tính năng

* Chuyển văn bản thành giọng nói (TTS)
* Hỗ trợ tiếng Việt
* Tự động chia nhỏ nội dung dài
* Ghép audio thành file hoàn chỉnh
* Chạy hoàn toàn local

---

## 👨‍💻 Tác giả

Thai Nguyen Quoc

```
```
