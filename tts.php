<?php
/**
 * tts.php — Helper gọi edge-tts qua file text tạm (UTF-8 BOM)
 *
 * LÝ DO DÙNG FILE TẠM:
 *   XAMPP Windows dùng CMD (CP437/CP1252) → tiếng Việt có dấu
 *   truyền qua escapeshellarg() bị hỏng hoàn toàn.
 *   Giải pháp: ghi text ra .txt (UTF-8 BOM) → edge-tts đọc file
 *   bằng tham số -f → xóa .txt tạm sau khi xong.
 */

const VOICE_MAP = [
    'nam-minh' => 'vi-VN-NamMinhNeural',
    'hoai-my'  => 'vi-VN-HoaiMyNeural',
];

// ====================================================================
// HÀM CHÍNH — được gọi từ api.php
// ====================================================================

/**
 * Tổng hợp giọng nói cho 1 đoạn text, lưu ra file MP3.
 *
 * @param  string $text       Văn bản UTF-8
 * @param  string $voiceKey   'nam-minh' | 'hoai-my'
 * @param  string $outputMp3  Đường dẫn tuyệt đối file MP3 đầu ra
 * @return array  ['success'=>bool, 'error'=>string|null]
 */
function generateTts(string $text, string $voiceKey, string $outputMp3): array
{
    $voiceName = VOICE_MAP[$voiceKey] ?? VOICE_MAP['nam-minh'];

    // 1. Chuẩn hóa text
    $text = tts_normalize($text);
    if (mb_strlen(trim($text)) === 0) {
        return ['success' => false, 'error' => 'Text sau chuẩn hóa bị trống.'];
    }

    // 2. Tìm edge-tts binary
    $bin = tts_findEdgeTts();
    if ($bin === null) {
        return [
            'success' => false,
            'error'   => 'Không tìm thấy edge-tts. '
                       . 'Chạy: pip install edge-tts '
                       . 'và đảm bảo thư mục Scripts Python có trong PATH.',
        ];
    }

    // 3. Ghi file .txt tạm với UTF-8 BOM
    $txtFile = preg_replace('/\.mp3$/i', '.txt', $outputMp3);
    if (file_put_contents($txtFile, "\xEF\xBB\xBF" . $text) === false) {
        return ['success' => false, 'error' => "Không ghi được file tạm: $txtFile"];
    }

    // 4. Xây và thực thi lệnh
    $cmd      = tts_buildCmd($bin, $voiceName, $txtFile, $outputMp3);
    $output   = [];
    $exitCode = 0;
    exec($cmd, $output, $exitCode);

    // 5. Xóa file .txt tạm ngay lập tức
    if (file_exists($txtFile)) {
        @unlink($txtFile);
    }

    // 6. Kiểm tra output hợp lệ
    if ($exitCode !== 0 || !file_exists($outputMp3) || filesize($outputMp3) < 1024) {
        $detail = implode(' | ', array_filter(array_slice($output, -6)));
        return [
            'success' => false,
            'error'   => "edge-tts lỗi (exit $exitCode). " . ($detail ?: 'Không có output.'),
        ];
    }

    return ['success' => true, 'error' => null];
}

// ====================================================================
// HELPERS NỘI BỘ (tiền tố tts_ tránh xung đột tên toàn cục)
// ====================================================================

function tts_normalize(string $t): string
{
    $t = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $t);
    $t = preg_replace('/\r\n|\r/', "\n", $t);
    $t = preg_replace('/\n{3,}/', "\n\n", $t);
    $t = str_replace('...', '…', $t);
    return trim($t);
}

/**
 * Xây lệnh shell hoàn chỉnh.
 * $bin có thể là: path thực sự  |  '__MODULE__' (python -m edge_tts)
 */
function tts_buildCmd(string $bin, string $voice, string $txtFile, string $mp3Out): string
{
    $v = escapeshellarg($voice);
    $f = escapeshellarg($txtFile);
    $o = escapeshellarg($mp3Out);

    if ($bin === '__MODULE__') {
        $py = tts_findPython() ?? 'python';
        return escapeshellarg($py) . " -m edge_tts --voice $v -f $f --write-media $o 2>&1";
    }

    return escapeshellarg($bin) . " --voice $v -f $f --write-media $o 2>&1";
}

/**
 * Tìm edge-tts theo thứ tự: PATH → Scripts Python Windows → python module
 */
function tts_findEdgeTts(): ?string
{
    $isWin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

    // --- 1. Thử PATH ---
    $out = [];
    exec($isWin ? 'where edge-tts 2>nul' : 'which edge-tts 2>/dev/null', $out, $code);
    if ($code === 0 && !empty($out[0])) {
        $p = trim(explode("\n", $out[0])[0]); // lấy dòng đầu (where trả nhiều dòng)
        if (file_exists($p)) return $p;
    }

    // --- 2. Đường dẫn cứng Windows ---
    if ($isWin) {
        $bases = array_filter([
            getenv('LOCALAPPDATA') . '\\Programs\\Python',
            'C:',
            getenv('APPDATA') . '\\Python',
        ]);
        $versions = ['313','312','311','310','39','38'];
        foreach ($bases as $base) {
            foreach ($versions as $ver) {
                $paths = [
                    "$base\\Python$ver\\Scripts\\edge-tts.exe",
                    "$base\\Python$ver\\Scripts\\edge-tts",
                ];
                foreach ($paths as $p) {
                    if (file_exists($p)) return $p;
                }
            }
        }
    }

    // --- 3. python -m edge_tts ---
    $py = tts_findPython();
    if ($py !== null) {
        $tmp = [];
        exec(escapeshellarg($py) . ' -m edge_tts --help 2>&1', $tmp, $ec);
        if ($ec === 0) return '__MODULE__';
    }

    return null;
}

function tts_findPython(): ?string
{
    $isWin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    foreach (['python', 'python3', 'py'] as $name) {
        $out = [];
        exec($isWin ? "where $name 2>nul" : "which $name 2>/dev/null", $out, $code);
        if ($code === 0 && !empty($out[0])) {
            $p = trim(explode("\n", $out[0])[0]);
            if (file_exists($p)) return $p;
        }
    }
    if ($isWin) {
        foreach (['313','312','311','310','39','38'] as $ver) {
            foreach ([
                "C:\\Python$ver\\python.exe",
                getenv('LOCALAPPDATA') . "\\Programs\\Python\\Python$ver\\python.exe",
            ] as $p) {
                if ($p && file_exists($p)) return $p;
            }
        }
    }
    return null;
}