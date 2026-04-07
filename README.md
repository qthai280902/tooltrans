# tooltrans
Web Đọc Truyện Tự Động — Hướng dẫn cài đặt
Cấu trúc thư mục
web-doc-truyen/
├── index.php        ← Giao diện & JS
├── api.php          ← Backend AJAX dispatcher
├── tts.php          ← Helper gọi edge-tts
├── temp/            ← (tự tạo) File MP3 chunk tạm
├── output/          ← (tự tạo) File MP3 hoàn chỉnh
└── README.md

Yêu cầu cài đặt
1. Python + edge-tts
pip install edge-tts

Sau đó kiểm tra:
edge-tts --version


2. ffmpeg
Tải từ: https://ffmpeg.org/download.html (Windows build)
Giải nén vào C:\ffmpeg\
Thêm C:\ffmpeg\bin vào biến môi trường PATH
Kiểm tra: ffmpeg -version
3. XAMPP PHP — bật exec()
Mở C:\xampp\php\php.ini, tìm dòng:
disable_functions = ...exec...

Xóa exec khỏi danh sách đó, rồi restart Apache.
4. Đặt project vào XAMPP
C:\xampp\htdocs\web-doc-truyen\

Truy cập: http://localhost/web-doc-truyen/
Luồng hoạt động
Browser                     api.php                    tts.php / edge-tts
   │                           │                              │
   ├─ POST clean_html ────────►│                              │
   │◄─ {session_id, chunks} ───┤                              │
   │                           │                              │
   ├─ POST generate_chunk[0] ─►│                              │
   │                           ├─ ghi chunk_0.txt ───────────►│
   │                           │◄─ đọc file → MP3 ────────────┤
   │◄─ {audio_file} ───────────┤   (xóa .txt tạm)            │
   │  ... lặp n chunks ...     │                              │
   │                           │                              │
   ├─ POST merge_audio ───────►│                              │
   │                           ├─ ffmpeg concat (MP3 lẻ → 1 MP3)
   │                           ├─ xóa file temp/             │
   │◄─ {output_url} ───────────┤                              │
   │                           │                              │
   ├─ Hiển thị player + link tải về

Lưu ý quan trọng
Tiếng Việt: tts.php ghi text ra file .txt (UTF-8 BOM) thay vì truyền thẳng qua command line → tránh lỗi encoding trên Windows CMD.
Session isolation: mỗi request dùng timestamp session_id riêng, nhiều user cùng lúc không bị ghi đè file nhau.
Cleanup: sau khi ghép, tất cả file .mp3 chunk trong temp/ bị xóa.
