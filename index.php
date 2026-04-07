<?php // index.php — Giao diện & JS ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🎙️ Web Đọc Truyện Tự Động</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=JetBrains+Mono:wght@400;500&family=Noto+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
/* ─── TOKENS ─────────────────────────────────────────── */
:root{
  --bg0:#0c0c0c; --bg1:#141414; --bg2:#1a1a1a; --bg3:#212121;
  --border:#272727; --border2:#333;
  --gold:#c9a84c; --gold2:#e8c86d; --gold-dim:#7a6030;
  --green:#4cb87a; --red:#c05050; --blue:#4c8ab8;
  --t1:#e8e2d9; --t2:#8a8278; --t3:#4a4642;
  --r:8px; --r2:14px;
  --ff-head:'Playfair Display',serif;
  --ff-mono:'JetBrains Mono',monospace;
  --ff-body:'Noto Sans',sans-serif;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:var(--bg0);color:var(--t1);font-family:var(--ff-body);
     font-weight:300;min-height:100vh}

/* ─── HEADER ─────────────────────────────────────────── */
.hdr{text-align:center;padding:52px 20px 0}
.hdr .eye{font-family:var(--ff-mono);font-size:10px;letter-spacing:.4em;
           color:var(--gold);opacity:.75;margin-bottom:10px}
.hdr h1{font-family:var(--ff-head);font-size:clamp(1.9rem,5vw,3.2rem);
         font-weight:900;line-height:1.1;
         background:linear-gradient(135deg,var(--gold2),var(--gold),#8c6820);
         -webkit-background-clip:text;-webkit-text-fill-color:transparent;
         background-clip:text}
.hdr .sub{margin-top:8px;font-size:13px;color:var(--t2)}
.hdr-rule{width:50px;height:1px;margin:18px auto 0;
          background:linear-gradient(90deg,transparent,var(--gold),transparent)}

/* ─── WRAP ───────────────────────────────────────────── */
.wrap{max-width:920px;margin:0 auto;padding:36px 20px 80px;
      display:flex;flex-direction:column;gap:20px}

/* ─── CARD ───────────────────────────────────────────── */
.card{background:var(--bg1);border:1px solid var(--border);
      border-radius:var(--r2);padding:24px 28px;
      transition:border-color .3s}
.card:focus-within{border-color:rgba(201,168,76,.3)}
.clabel{font-family:var(--ff-mono);font-size:10px;letter-spacing:.35em;
         text-transform:uppercase;color:var(--gold);margin-bottom:14px;
         display:flex;align-items:center;gap:8px}
.clabel::after{content:'';flex:1;height:1px;background:var(--border)}

/* ─── TEXTAREA INPUT ─────────────────────────────────── */
.ta{width:100%;height:200px;background:var(--bg2);border:1px solid var(--border);
    border-radius:var(--r);color:var(--t1);font-family:var(--ff-mono);
    font-size:12px;line-height:1.75;padding:14px;resize:vertical;
    outline:none;transition:border-color .2s}
.ta::placeholder{color:var(--t3)}
.ta:focus{border-color:rgba(201,168,76,.4)}

/* ─── TEXT SẠCH OUTPUT ───────────────────────────────── */
.clean-box{width:100%;min-height:140px;max-height:320px;
           overflow-y:auto;background:var(--bg2);
           border:1px solid var(--border);border-radius:var(--r);
           padding:14px;font-family:var(--ff-body);font-size:13.5px;
           line-height:1.85;color:var(--t1);white-space:pre-wrap;
           word-break:break-word}
.clean-box.empty{color:var(--t3);font-style:italic;font-size:13px}
.clean-meta{margin-top:8px;font-family:var(--ff-mono);font-size:11px;
             color:var(--t2);display:flex;gap:16px;flex-wrap:wrap}
.clean-meta span{display:flex;align-items:center;gap:4px}

/* copy btn */
.btn-copy{margin-left:auto;padding:4px 12px;background:var(--bg3);
          border:1px solid var(--border2);border-radius:4px;
          color:var(--t2);font-family:var(--ff-mono);font-size:10px;
          letter-spacing:.15em;cursor:pointer;transition:all .2s}
.btn-copy:hover{border-color:var(--gold);color:var(--gold)}

/* ─── VOICE SELECT ───────────────────────────────────── */
.vgrid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.vopt{position:relative;cursor:pointer}
.vopt input{position:absolute;opacity:0;width:0;height:0}
.vcard{display:flex;align-items:center;gap:12px;padding:14px 18px;
       background:var(--bg2);border:1px solid var(--border);
       border-radius:var(--r);transition:all .2s}
.vopt input:checked+.vcard{border-color:var(--gold);
  background:rgba(201,168,76,.06);box-shadow:0 0 14px rgba(201,168,76,.1)}
.vcard:hover{border-color:rgba(201,168,76,.25);background:var(--bg3)}
.vava{width:42px;height:42px;border-radius:50%;display:flex;
      align-items:center;justify-content:center;font-size:18px;
      flex-shrink:0;border:1px solid var(--border)}
.vava.m{background:rgba(76,138,184,.15);border-color:rgba(76,138,184,.3)}
.vava.f{background:rgba(184,76,76,.12);border-color:rgba(184,76,76,.25)}
.vinfo .vn{font-size:14px;font-weight:500}
.vinfo .vd{font-size:11px;color:var(--t2);margin-top:2px}
.vchk{margin-left:auto;width:17px;height:17px;border-radius:50%;
      border:1px solid var(--border);display:flex;align-items:center;
      justify-content:center;transition:all .2s;flex-shrink:0}
.vopt input:checked+.vcard .vchk{background:var(--gold);border-color:var(--gold)}
.vopt input:checked+.vcard .vchk::after{content:'✓';font-size:10px;
  color:#0c0c0c;font-weight:700}

/* ─── BUTTON ─────────────────────────────────────────── */
.btn-go{width:100%;padding:15px;
        background:linear-gradient(135deg,var(--gold),#9a6820);
        border:none;border-radius:var(--r);color:#0c0c0c;
        font-family:var(--ff-mono);font-size:12px;font-weight:600;
        letter-spacing:.25em;text-transform:uppercase;cursor:pointer;
        transition:all .25s;box-shadow:0 4px 18px rgba(201,168,76,.25)}
.btn-go:hover:not(:disabled){
  background:linear-gradient(135deg,var(--gold2),var(--gold));
  box-shadow:0 6px 28px rgba(201,168,76,.4);transform:translateY(-1px)}
.btn-go:disabled{opacity:.45;cursor:not-allowed;transform:none}

/* ─── PROGRESS ───────────────────────────────────────── */
.step{display:flex;align-items:flex-start;gap:14px;
      padding:14px 0;border-bottom:1px solid var(--border);
      position:relative}
.step:last-child{border-bottom:none}
/* connector */
.step:not(:last-child)::after{content:'';position:absolute;
  left:15px;top:42px;bottom:-14px;width:1px;
  background:var(--border);z-index:0}
.step.done::after{background:var(--green)}
.step.active::after{background:linear-gradient(to bottom,var(--gold),var(--border))}
/* icon */
.sico{position:relative;z-index:1;width:32px;height:32px;
      border-radius:50%;border:1px solid var(--border);
      background:var(--bg2);display:flex;align-items:center;
      justify-content:center;font-size:13px;flex-shrink:0;
      transition:all .3s}
.step.done   .sico{background:rgba(76,184,122,.12);border-color:var(--green)}
.step.active .sico{background:rgba(201,168,76,.1);border-color:var(--gold);
  animation:pulse 1.6s ease-in-out infinite}
.step.error  .sico{background:rgba(192,80,80,.12);border-color:var(--red)}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(201,168,76,.4)}
                 50%{box-shadow:0 0 0 7px rgba(201,168,76,0)}}
.stitle{font-size:13px;font-weight:500}
.sdetail{font-family:var(--ff-mono);font-size:11px;color:var(--t2);
          margin-top:3px;line-height:1.5}
.step.done   .stitle{color:var(--green)}
.step.active .stitle{color:var(--gold)}
.step.error  .stitle{color:var(--red)}
/* mini bar */
.mbar{margin-top:7px;height:3px;background:var(--border);
      border-radius:99px;overflow:hidden;display:none}
.mbar.on{display:block}
.mbar-fill{height:100%;background:linear-gradient(90deg,var(--gold),var(--gold2));
           border-radius:99px;transition:width .5s ease;width:0}

/* ─── ERROR ──────────────────────────────────────────── */
.err-box{display:none;background:rgba(192,80,80,.07);
         border:1px solid rgba(192,80,80,.3);border-radius:var(--r);
         padding:14px 18px;font-family:var(--ff-mono);font-size:12px;
         color:#e08080;line-height:1.65;white-space:pre-wrap;word-break:break-all}
.err-box.on{display:block}

/* ─── RESULT ─────────────────────────────────────────── */
.result-box{display:none;background:rgba(76,184,122,.05);
            border:1px solid rgba(76,184,122,.2);border-radius:var(--r2);
            padding:26px 28px;text-align:center}
.result-box.on{display:block}
.r-ico{font-size:38px;margin-bottom:10px}
.r-title{font-family:var(--ff-head);font-size:19px;color:var(--green)}
.r-meta{font-family:var(--ff-mono);font-size:11px;color:var(--t2);margin:6px 0 18px}
.btn-dl{display:inline-flex;align-items:center;gap:8px;padding:11px 26px;
        background:var(--green);color:#0c0c0c;text-decoration:none;
        border-radius:var(--r);font-family:var(--ff-mono);font-size:11px;
        font-weight:600;letter-spacing:.15em;text-transform:uppercase;
        transition:all .2s;box-shadow:0 4px 16px rgba(76,184,122,.3)}
.btn-dl:hover{background:#5dd68e;transform:translateY(-1px)}
audio{width:100%;margin-top:18px;filter:invert(.85) hue-rotate(160deg);
      border-radius:var(--r)}

/* ─── SCROLLBAR ──────────────────────────────────────── */
.clean-box::-webkit-scrollbar{width:6px}
.clean-box::-webkit-scrollbar-track{background:var(--bg2)}
.clean-box::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px}

/* ─── RESPONSIVE ─────────────────────────────────────── */
@media(max-width:600px){
  .vgrid{grid-template-columns:1fr}
  .card{padding:18px 16px}
  .wrap{padding:24px 14px 60px}
}
</style>
</head>
<body>

<header class="hdr">
  <p class="eye">⚙ edge-tts · ffmpeg · PHP · Không API bên thứ 3</p>
  <h1>Web Đọc Truyện Tự Động</h1>
  <p class="sub">Paste HTML → Làm sạch → Tổng hợp giọng nói → Xuất MP3</p>
  <div class="hdr-rule"></div>
</header>

<main class="wrap">

  <!-- ① HTML INPUT -->
  <div class="card">
    <p class="clabel">① Dán HTML bài viết / chương truyện</p>
    <textarea id="htmlIn" class="ta"
      placeholder="Dán HTML vào đây...&#10;&#10;Ví dụ:&#10;&lt;p&gt;Một buổi sáng mùa thu, Minh thức dậy sớm...&lt;/p&gt;&#10;&lt;br&gt;&#10;&lt;p&gt;Trời trong vắt, gió nhẹ thổi qua khung cửa.&lt;/p&gt;"></textarea>
  </div>

  <!-- ② TEXT SẠCH -->
  <div class="card" id="cleanCard">
    <p class="clabel">② Nội dung văn bản đã làm sạch</p>
    <div id="cleanBox" class="clean-box empty">
      Nội dung sạch sẽ hiển thị ở đây sau khi xử lý HTML...
    </div>
    <div class="clean-meta" id="cleanMeta" style="display:none">
      <span id="metaChars">📝 0 ký tự</span>
      <span id="metaChunks">📦 0 đoạn</span>
      <button class="btn-copy" onclick="copyCleanText()">⎘ Copy</button>
    </div>
  </div>

  <!-- ③ VOICE -->
  <div class="card">
    <p class="clabel">③ Chọn giọng đọc</p>
    <div class="vgrid">
      <label class="vopt">
        <input type="radio" name="voice" value="nam-minh" checked>
        <div class="vcard">
          <div class="vava m">🎙️</div>
          <div class="vinfo"><div class="vn">Nam Minh</div>
            <div class="vd">vi-VN-NamMinhNeural · Nam</div></div>
          <div class="vchk"></div>
        </div>
      </label>
      <label class="vopt">
        <input type="radio" name="voice" value="hoai-my">
        <div class="vcard">
          <div class="vava f">🎤</div>
          <div class="vinfo"><div class="vn">Hoài My</div>
            <div class="vd">vi-VN-HoaiMyNeural · Nữ</div></div>
          <div class="vchk"></div>
        </div>
      </label>
    </div>
  </div>

  <!-- ④ BUTTON -->
  <button class="btn-go" id="btnGo" onclick="startProcess()">
    🎬 &nbsp; BẮT ĐẦU TẠO AUDIO
  </button>

  <!-- ⑤ PROGRESS -->
  <div class="card" id="progressCard" style="display:none">
    <p class="clabel">④ Tiến trình xử lý</p>

    <div class="step" id="s-clean">
      <div class="sico">🧹</div>
      <div style="flex:1">
        <div class="stitle">Bước 1 — Dọn dẹp HTML &amp; Tách chunks</div>
        <div class="sdetail" id="d-clean">Đang chờ...</div>
        <div class="mbar" id="b-clean"><div class="mbar-fill"></div></div>
      </div>
    </div>

    <div class="step" id="s-tts">
      <div class="sico">🔊</div>
      <div style="flex:1">
        <div class="stitle">Bước 2 — Tổng hợp giọng nói (edge-tts)</div>
        <div class="sdetail" id="d-tts">Đang chờ...</div>
        <div class="mbar" id="b-tts"><div class="mbar-fill"></div></div>
      </div>
    </div>

    <div class="step" id="s-merge">
      <div class="sico">🎼</div>
      <div style="flex:1">
        <div class="stitle">Bước 3 — Ghép file audio (ffmpeg)</div>
        <div class="sdetail" id="d-merge">Đang chờ...</div>
        <div class="mbar" id="b-merge"><div class="mbar-fill"></div></div>
      </div>
    </div>
  </div>

  <!-- ⑥ LỖI -->
  <div class="err-box" id="errBox"></div>

  <!-- ⑦ KẾT QUẢ -->
  <div class="result-box" id="resultBox">
    <div class="r-ico">✅</div>
    <div class="r-title">Hoàn tất!</div>
    <div class="r-meta" id="rMeta"></div>
    <a href="#" id="dlLink" class="btn-dl" download>⬇️ &nbsp; TẢI FILE MP3</a>
    <audio id="player" controls></audio>
  </div>

</main>

<script>
/* ================================================================
   JAVASCRIPT — AJAX Orchestrator
================================================================ */

const API = 'api.php';
let _cleanText = '';   // lưu text sạch để copy

// ── UI helpers ──────────────────────────────────────────────────

function setStep(id, state, detail) {
  const el = document.getElementById('s-' + id);
  // reset class
  el.className = 'step' + (state ? ' ' + state : '');
  if (detail != null) document.getElementById('d-' + id).textContent = detail;
}

function setBar(id, pct) {
  const bar = document.getElementById('b-' + id);
  bar.classList.add('on');
  bar.querySelector('.mbar-fill').style.width = pct + '%';
}

function showErr(msg) {
  const el = document.getElementById('errBox');
  el.textContent = '⚠  ' + msg;
  el.classList.add('on');
}

function hideErr() {
  document.getElementById('errBox').classList.remove('on');
}

// ── POST ────────────────────────────────────────────────────────

async function post(payload) {
  const res = await fetch(API, {
    method : 'POST',
    headers: {'Content-Type': 'application/json'},
    body   : JSON.stringify(payload)
  });

  // Đọc body dưới dạng text trước để debug khi có 500
  const raw = await res.text();

  if (!res.ok) {
    // Thử parse JSON để lấy message rõ hơn
    try {
      const j = JSON.parse(raw);
      throw new Error(j.message || `HTTP ${res.status}`);
    } catch(_) {
      throw new Error(`HTTP ${res.status}: ${raw.substring(0, 300)}`);
    }
  }

  let json;
  try { json = JSON.parse(raw); }
  catch(e) { throw new Error('Response không phải JSON: ' + raw.substring(0, 300)); }

  if (!json.success) throw new Error(json.message || 'Lỗi không xác định từ server.');
  return json;
}

// ── MAIN FLOW ────────────────────────────────────────────────────

async function startProcess() {
  const html  = document.getElementById('htmlIn').value.trim();
  const voice = document.querySelector('input[name="voice"]:checked').value;

  if (!html) { alert('Vui lòng dán HTML vào ô nhập liệu!'); return; }

  // Reset
  hideErr();
  document.getElementById('resultBox').classList.remove('on');
  document.getElementById('progressCard').style.display = 'block';
  document.getElementById('btnGo').disabled = true;
  document.getElementById('btnGo').textContent = '⏳  Đang xử lý...';
  ['clean','tts','merge'].forEach(s => setStep(s, '', 'Đang chờ...'));

  // Reset clean box
  const cBox = document.getElementById('cleanBox');
  cBox.textContent = 'Đang xử lý...';
  cBox.className = 'clean-box empty';
  document.getElementById('cleanMeta').style.display = 'none';

  try {

    /* ── BƯỚC 1: clean_html ─────────────────────────────── */
    setStep('clean', 'active', 'Đang làm sạch HTML...');
    setBar('clean', 20);

    const r1 = await post({ action: 'clean_html', html, voice });

    setBar('clean', 100);
    setStep('clean', 'done',
      `✓ ${r1.char_count.toLocaleString()} ký tự → ${r1.chunks.length} đoạn`);

    // Hiển thị text sạch
    _cleanText = r1.clean_text;
    cBox.textContent = r1.clean_text;
    cBox.className = 'clean-box';
    document.getElementById('metaChars').textContent =
      '📝 ' + r1.char_count.toLocaleString() + ' ký tự';
    document.getElementById('metaChunks').textContent =
      '📦 ' + r1.chunks.length + ' đoạn';
    document.getElementById('cleanMeta').style.display = 'flex';

    const { session_id, chunks } = r1;

    /* ── BƯỚC 2: generate_chunk (loop) ──────────────────── */
    const audioFiles = [];
    setStep('tts', 'active', `Đoạn 1/${chunks.length}...`);
    setBar('tts', 0);

    for (let i = 0; i < chunks.length; i++) {
      const pct = Math.round(((i + 1) / chunks.length) * 100);
      setStep('tts', 'active', `Đang tổng hợp đoạn ${i+1}/${chunks.length}...`);

      const r2 = await post({
        action     : 'generate_chunk',
        session_id,
        chunk_index: i,
        text       : chunks[i],
        voice
      });

      audioFiles.push(r2.audio_file);
      setBar('tts', pct);
    }

    setStep('tts', 'done', `✓ ${audioFiles.length} file audio tạm đã tạo xong`);

    /* ── BƯỚC 3: merge_audio ────────────────────────────── */
    setStep('merge', 'active', 'ffmpeg đang ghép...');
    setBar('merge', 30);

    const r3 = await post({
      action    : 'merge_audio',
      session_id,
      files     : audioFiles,
      voice
    });

    setBar('merge', 100);
    setStep('merge', 'done', '✓ Ghép xong, đã dọn file tạm');

    /* ── HIỂN THỊ KẾT QUẢ ──────────────────────────────── */
    document.getElementById('rMeta').textContent = 'File: ' + r3.output_file;
    const dlLink = document.getElementById('dlLink');
    dlLink.href     = r3.output_url;
    dlLink.download = r3.output_file;
    document.getElementById('player').src = r3.output_url;
    document.getElementById('resultBox').classList.add('on');
    document.getElementById('resultBox').scrollIntoView({behavior:'smooth'});

  } catch(err) {
    showErr(err.message);
    ['clean','tts','merge'].forEach(s => {
      if (document.getElementById('s-' + s).classList.contains('active')) {
        setStep(s, 'error', '✗ ' + err.message);
      }
    });
  } finally {
    document.getElementById('btnGo').disabled = false;
    document.getElementById('btnGo').textContent = '🎬  BẮT ĐẦU TẠO AUDIO';
  }
}

function copyCleanText() {
  if (!_cleanText) return;
  navigator.clipboard.writeText(_cleanText).then(() => {
    const btn = document.querySelector('.btn-copy');
    const orig = btn.textContent;
    btn.textContent = '✓ Đã copy!';
    setTimeout(() => btn.textContent = orig, 1800);
  });
}
</script>
</body>
</html>