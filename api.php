<?php
/**
 * api.php — Backend AJAX dispatcher
 *
 * Actions:
 *   clean_html     — Làm sạch HTML, chia chunks, trả về text sạch
 *   generate_chunk — Gọi edge-tts cho 1 chunk
 *   merge_audio    — ffmpeg ghép MP3 cuối cùng
 */

header('Content-Type: application/json; charset=utf-8');

// Bắt mọi lỗi PHP thành JSON thay vì trang trắng / 500
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    apiError("PHP Error [$errno]: $errstr in $errfile:$errline");
});
set_exception_handler(function($e) {
    apiError('Exception: ' . $e->getMessage());
});

error_reporting(E_ALL);
ini_set('display_errors', 0);
set_time_limit(300);

define('BASE_DIR',   __DIR__);
define('TEMP_DIR',   BASE_DIR . DIRECTORY_SEPARATOR . 'temp');
define('OUTPUT_DIR', BASE_DIR . DIRECTORY_SEPARATOR . 'output');

// Tự tạo thư mục nếu chưa có
foreach ([TEMP_DIR, OUTPUT_DIR] as $dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            apiError("Không thể tạo thư mục: $dir — kiểm tra quyền ghi.");
        }
    }
}

require_once __DIR__ . '/tts.php';

// ====================================================================
// ĐỌC REQUEST
// ====================================================================
$raw     = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($payload['action'])) {
    apiError('Request JSON không hợp lệ hoặc thiếu trường action. Raw: ' . substr($raw, 0, 200));
}

$action = trim($payload['action']);

// ====================================================================
// ROUTER
// ====================================================================
switch ($action) {
    case 'clean_html':     handleCleanHtml($payload);    break;
    case 'generate_chunk': handleGenerateChunk($payload); break;
    case 'merge_audio':    handleMergeAudio($payload);    break;
    default: apiError("Action không hỗ trợ: $action");
}


// ====================================================================
// ACTION 1: clean_html
// ====================================================================
function handleCleanHtml(array $p): void
{
    $html  = $p['html']  ?? '';
    $voice = sanitizeVoice($p['voice'] ?? 'nam-minh');

    if (mb_strlen(trim($html)) < 5) {
        apiError('HTML đầu vào trống hoặc quá ngắn.');
    }

    // Làm sạch
    $cleanText = cleanHtml($html);

    if (mb_strlen(trim($cleanText)) < 5) {
        apiError('Không trích xuất được văn bản từ HTML.');
    }

    // Chia chunks
    $chunks    = splitChunks($cleanText, 2800);
    $sessionId = 'sess_' . time() . '_' . mt_rand(1000, 9999);

    apiSuccess([
        'session_id' => $sessionId,
        'clean_text' => $cleanText,          // ← text sạch trả về frontend
        'char_count' => mb_strlen($cleanText),
        'chunks'     => $chunks,
        'voice'      => $voice,
    ]);
}


// ====================================================================
// ACTION 2: generate_chunk
// ====================================================================
function handleGenerateChunk(array $p): void
{
    $sessionId  = sanitizeId($p['session_id']   ?? '');
    $chunkIndex = intval($p['chunk_index']       ?? 0);
    $text       = $p['text']                     ?? '';
    $voice      = sanitizeVoice($p['voice']      ?? 'nam-minh');

    if (empty($sessionId)) apiError('Thiếu session_id.');
    if (mb_strlen(trim($text)) === 0) apiError('Text chunk trống.');

    $mp3File = TEMP_DIR . DIRECTORY_SEPARATOR . "{$sessionId}_chunk_{$chunkIndex}.mp3";

    $result = generateTts($text, $voice, $mp3File);

    if (!$result['success']) {
        apiError("TTS chunk $chunkIndex thất bại: " . $result['error']);
    }

    apiSuccess([
        'chunk_index' => $chunkIndex,
        'audio_file'  => $mp3File,
    ]);
}


// ====================================================================
// ACTION 3: merge_audio
// ====================================================================
function handleMergeAudio(array $p): void
{
    $sessionId = sanitizeId($p['session_id'] ?? '');
    $files     = $p['files']                 ?? [];
    $voice     = sanitizeVoice($p['voice']   ?? 'nam-minh');

    if (empty($sessionId)) apiError('Thiếu session_id.');
    if (empty($files))     apiError('Danh sách file trống.');

    // Kiểm tra từng file tồn tại và nằm trong TEMP_DIR
    $tempReal = realpath(TEMP_DIR);
    foreach ($files as $f) {
        $real = realpath($f);
        if ($real === false || strpos($real, $tempReal) !== 0) {
            apiError("File nằm ngoài thư mục temp/ (bảo mật): $f");
        }
        if (!file_exists($real) || filesize($real) < 1024) {
            apiError("File audio tạm không hợp lệ: $f");
        }
    }

    // Tên và đường dẫn output
    $outName = "{$sessionId}_{$voice}_final.mp3";
    $outPath = OUTPUT_DIR . DIRECTORY_SEPARATOR . $outName;

    // Tạo filelist cho ffmpeg
    $listFile = TEMP_DIR . DIRECTORY_SEPARATOR . "{$sessionId}_list.txt";
    $lines = array_map(fn($f) => "file '" . str_replace("'", "\\'", realpath($f)) . "'", $files);
    file_put_contents($listFile, implode("\n", $lines));

    // Chạy ffmpeg
    $ffResult = runFfmpeg($listFile, $outPath);

    // Dọn dẹp file tạm
    foreach ($files as $f) {
        $real = realpath($f);
        if ($real) @unlink($real);
    }
    @unlink($listFile);

    if (!$ffResult['success']) {
        apiError('ffmpeg lỗi: ' . $ffResult['error']);
    }

    apiSuccess([
        'output_file' => $outName,
        'output_url'  => 'output/' . rawurlencode($outName),
    ]);
}


// ====================================================================
// XỬ LÝ VĂN BẢN
// ====================================================================

function cleanHtml(string $html): string
{
    // Xóa script/style hoàn toàn
    $html = preg_replace('#<script[^>]*>.*?</script>#si', '', $html);
    $html = preg_replace('#<style[^>]*>.*?</style>#si',  '', $html);
    // Xóa tag quảng cáo theo class/id phổ biến
    $html = preg_replace(
        '#<[^>]+(?:class|id)=["\'][^"\']*\b(?:ads?|adsense|banner|sponsor|popup|overlay)\b[^"\']*["\'][^>]*>.*?</[a-z][a-z0-9]*>#si',
        '', $html
    );
    // Chuyển tag xuống dòng → \n TRƯỚC strip_tags
    $html = preg_replace('#<(?:br\s*/?|/p|/div|/li|/h[1-6]|/blockquote|/tr)>#i', "\n", $html);
    // Strip tags
    $text = strip_tags($html);
    // Giải mã HTML entities
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Dọn khoảng trắng
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/\n[ \t]+/', "\n", $text);
    $text = preg_replace('/\n{3,}/', "\n\n", $text);
    return trim($text);
}

function splitChunks(string $text, int $max = 2800): array
{
    if (mb_strlen($text) <= $max) return [$text];

    $chunks = [];
    $rem    = $text;

    while (mb_strlen($rem) > 0) {
        if (mb_strlen($rem) <= $max) {
            $chunks[] = trim($rem);
            break;
        }
        $slice  = mb_substr($rem, 0, $max);
        $cutPos = false;

        // Ưu tiên: dòng mới → cuối câu
        foreach (["\n", '。', '！', '？', '.', '!', '?', '،'] as $sep) {
            $pos = mb_strrpos($slice, $sep);
            if ($pos !== false && $pos > $max * 0.4) {
                $cutPos = $pos + mb_strlen($sep);
                break;
            }
        }
        if ($cutPos === false) {
            $pos    = mb_strrpos($slice, ' ');
            $cutPos = ($pos !== false) ? $pos : $max;
        }

        $chunk = trim(mb_substr($rem, 0, $cutPos));
        $rem   = trim(mb_substr($rem, $cutPos));
        if ($chunk !== '') $chunks[] = $chunk;
    }

    return array_values(array_filter($chunks, fn($c) => mb_strlen(trim($c)) > 0));
}


// ====================================================================
// FFMPEG
// ====================================================================

function runFfmpeg(string $listFile, string $outPath): array
{
    $bin = findFfmpeg();
    if ($bin === null) {
        return ['success' => false, 'error' =>
            'Không tìm thấy ffmpeg. Cài tại https://ffmpeg.org và thêm vào PATH.'];
    }

    $cmd = sprintf('%s -y -f concat -safe 0 -i %s -c copy %s 2>&1',
        escapeshellarg($bin),
        escapeshellarg($listFile),
        escapeshellarg($outPath)
    );

    $out  = [];
    $code = 0;
    exec($cmd, $out, $code);

    if ($code !== 0 || !file_exists($outPath) || filesize($outPath) < 1024) {
        return ['success' => false,
                'error'   => "exit $code. " . implode(' | ', array_slice($out, -5))];
    }
    return ['success' => true];
}

function findFfmpeg(): ?string
{
    $isWin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    $out   = [];
    exec($isWin ? 'where ffmpeg 2>nul' : 'which ffmpeg 2>/dev/null', $out, $code);
    if ($code === 0 && !empty($out[0])) {
        $p = trim(explode("\n", $out[0])[0]);
        if (file_exists($p)) return $p;
    }
    // Fallback Windows paths
    foreach ([
        'C:\\ffmpeg\\bin\\ffmpeg.exe',
        'C:\\Program Files\\ffmpeg\\bin\\ffmpeg.exe',
        'C:\\xampp\\ffmpeg\\ffmpeg.exe',
    ] as $p) {
        if (file_exists($p)) return $p;
    }
    return null;
}


// ====================================================================
// UTILITIES
// ====================================================================

function sanitizeVoice(string $v): string
{
    return in_array($v, ['nam-minh', 'hoai-my'], true) ? $v : 'nam-minh';
}

function sanitizeId(string $id): string
{
    return preg_replace('/[^a-zA-Z0-9_\-]/', '', $id);
}

function apiSuccess(array $data): void
{
    echo json_encode(array_merge(['success' => true], $data),
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function apiError(string $msg): void
{
    echo json_encode(['success' => false, 'message' => $msg],
        JSON_UNESCAPED_UNICODE);
    exit;
}