<?php
// index.php
// صفحة عرض وتحميل تطبيقات داخل نفس المجلد
// ضع هذا الملف في المجلد الذي يحتوي ملفات التطبيقات

// ----------------------- إعدادات -----------------------
$allowed_ext = [
    'apk','zip','rar','exe','msi','dmg','pkg','ipa','tar','gz','7z','pdf','txt'
]; // امتدادات مسموح بعرضها
$exclude_files = ['index.php','.htaccess','download.php']; // ملفات لا تعرض
$show_hidden = false; // إظهار الملفات المخفية التي تبدأ بنقطة؟
$sort_by = 'name'; // 'name' | 'size' | 'mtime'
$sort_dir = 'asc'; // 'asc' | 'desc'
// --------------------------------------------------------

function human_filesize($bytes, $decimals = 2) {
    $size = ['B','KB','MB','GB','TB','PB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    if ($factor == 0) return $bytes . ' ' . $size[$factor];
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $size[$factor];
}

// نقرأ ملفات المجلد الحالي
$files = [];
$dir = __DIR__;
$dh = opendir($dir);
if ($dh) {
    while (($f = readdir($dh)) !== false) {
        if ($f === '.' || $f === '..') continue;
        if (in_array($f, $exclude_files)) continue;
        if (!$show_hidden && strpos($f, '.') === 0) continue;
        $path = $dir . DIRECTORY_SEPARATOR . $f;
        if (!is_file($path)) continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) continue;
        $files[] = [
            'name' => $f,
            'url' => rawurlencode($f),
            'ext' => $ext,
            'size' => filesize($path),
            'size_read' => human_filesize(filesize($path)),
            'mtime' => filemtime($path),
            'mtime_read' => date("Y-m-d H:i:s", filemtime($path))
        ];
    }
    closedir($dh);
}

// فرز افتراضي
usort($files, function($a, $b) use ($sort_by, $sort_dir) {
    if ($a[$sort_by] == $b[$sort_by]) return 0;
    if ($sort_dir === 'asc') return ($a[$sort_by] < $b[$sort_by]) ? -1 : 1;
    return ($a[$sort_by] > $b[$sort_by]) ? -1 : 1;
});

// سنمرّر مصفوفة الملفات للـJS بشكل JSON
$files_json = json_encode($files, JSON_UNESCAPED_UNICODE);
?>
<!doctype html>
<html lang="ar">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>مكتبة التطبيقات — تحميل التطبيقات</title>
<link rel="icon" href="data:;base64,iVBORw0KGgo=">
<style>
/* --- CSS احترافي وبسيط --- */
:root{
  --bg:#0f1724; --card:#0b1220; --muted:#9aa6b2; --accent:#06b6d4; --accent-2:#06d6a0;
  --glass: rgba(255,255,255,0.03);
}
*{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0; font-family:Inter, "Segoe UI", Tahoma, Arial;
  background: linear-gradient(180deg,#071028 0%, #071a2a 60%), url('') fixed;
  color:#e6eef6; -webkit-font-smoothing:antialiased;
}
.container{
  max-width:1100px; margin:28px auto; padding:20px;
}
.header{
  display:flex; gap:16px; align-items:center; justify-content:space-between;
  margin-bottom:18px;
}
.brand{
  display:flex; gap:14px; align-items:center;
}
.logo{
  width:56px; height:56px; background:linear-gradient(135deg,var(--accent),var(--accent-2));
  border-radius:14px; display:flex; align-items:center; justify-content:center;
  font-weight:700; color:#042; box-shadow:0 6px 22px rgba(6,182,212,0.12);
}
.title{font-size:18px; font-weight:700}
.subtitle{font-size:13px; color:var(--muted)}
.controls{display:flex; gap:10px; align-items:center;}
.search{
  background:var(--glass); border:1px solid rgba(255,255,255,0.03); padding:8px 12px;
  border-radius:10px; min-width:260px; color:inherit;
}
.select, .btn{
  background:transparent; border:1px solid rgba(255,255,255,0.06); padding:8px 12px; border-radius:10px;
  color:inherit; cursor:pointer;
}
.grid{
  display:grid; grid-template-columns: repeat(auto-fill,minmax(240px,1fr)); gap:14px;
}
.card{
  background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
  border-radius:12px; padding:12px; border:1px solid rgba(255,255,255,0.03);
  display:flex; gap:10px; align-items:center;
}
.file-icon{
  width:56px; height:56px; border-radius:10px; display:flex; align-items:center; justify-content:center;
  background:rgba(255,255,255,0.03);
  font-weight:700; font-size:14px; color:var(--muted);
}
.file-meta{flex:1; min-width:0}
.file-name{font-size:15px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis}
.file-sub{font-size:12px; color:var(--muted); margin-top:6px}
.actions{display:flex; gap:8px; align-items:center}
.download{
  background:linear-gradient(90deg,var(--accent),var(--accent-2)); border:none; color:#042;
  padding:8px 12px; border-radius:10px; font-weight:700; cursor:pointer; text-decoration:none;
}
.small{font-size:12px; padding:6px 8px}
.empty{
  text-align:center; padding:40px; color:var(--muted); border:1px dashed rgba(255,255,255,0.03); border-radius:12px;
}
.footer{margin-top:18px; text-align:center; color:var(--muted); font-size:13px}
@media (max-width:640px){
  .controls{flex-direction:column; align-items:stretch}
  .brand{gap:10px}
}
</style>
</head>
<body>
<div class="container" dir="rtl">
  <div class="header">
    <div class="brand">
      <div class="logo">APP</div>
      <div>
        <div class="title">مكتبة التطبيقات</div>
        <div class="subtitle">تحميل التطبيقات المتاحة في هذا المجلد</div>
      </div>
    </div>

    <div class="controls" role="region" aria-label="أدوات البحث والفرز">
      <input id="search" class="search" placeholder="ابحث باسم التطبيق أو الامتداد..." />
      <select id="sort" class="select" title="ترتيب">
        <option value="name|asc">الاسم ↑</option>
        <option value="name|desc">الاسم ↓</option>
        <option value="size|desc">الحجم ↓</option>
        <option value="size|asc">الحجم ↑</option>
        <option value="mtime|desc">الأحدث أولاً</option>
        <option value="mtime|asc">الأقدم أولاً</option>
      </select>
      <button id="refresh" class="btn small" title="تحديث القائمة">تحديث</button>
    </div>
  </div>

  <main id="main">
    <div id="grid" class="grid" aria-live="polite"></div>
    <div id="empty" class="empty" style="display:none">
      لا يوجد ملفات متاحة للتحميل في هذا المجلد.
    </div>
  </main>

  <div class="footer">
    صفحة تنزيل احترافية — توضع على سيرفر يدعم PHP. حقوق التصميم لك.
  </div>
</div>

<script>
// بيانات الملفات مرّرت بواسطة PHP
const FILES = <?php echo $files_json ?: '[]'; ?>;

// العناصر
const grid = document.getElementById('grid');
const empty = document.getElementById('empty');
const searchInput = document.getElementById('search');
const sortSelect = document.getElementById('sort');
const refreshBtn = document.getElementById('refresh');

let files = FILES.map(f => {
  f.url_decoded = decodeURIComponent(f.url);
  return f;
});

function extIcon(ext){
  // أيقونة نصية بحسب الامتداد — يمكن استبدال بصور لاحقاً
  return ext.toUpperCase();
}

function renderList(list){
  grid.innerHTML = '';
  if (!list.length) {
    empty.style.display = 'block';
    return;
  }
  empty.style.display = 'none';
  for (const f of list){
    const card = document.createElement('div');
    card.className = 'card';
    card.innerHTML = `
      <div class="file-icon" aria-hidden="true">${extIcon(f.ext)}</div>
      <div class="file-meta">
        <div class="file-name" title="${escapeHtml(f.name)}">${escapeHtml(f.name)}</div>
        <div class="file-sub">${f.size_read} • ${f.mtime_read}</div>
      </div>
      <div class="actions">
        <a class="download small" href="${f.url_decoded}" download="${escapeAttr(f.name)}">تنزيل</a>
        <button class="btn small" onclick="copyLink('${escapeAttr(location.pathname.replace(/\/[^\/]*$/,'/') + f.url_decoded)}')">انسخ الرابط</button>
      </div>
    `;
    grid.appendChild(card);
  }
}

function escapeHtml(s){
  return String(s).replace(/[&<>"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]});
}
function escapeAttr(s){
  return String(s).replace(/"/g,'&quot;');
}

function filterAndSort(){
  const q = searchInput.value.trim().toLowerCase();
  let out = files.filter(f => {
    if (!q) return true;
    return f.name.toLowerCase().includes(q) || f.ext.toLowerCase().includes(q);
  });
  const [k,dir] = sortSelect.value.split('|');
  out.sort((a,b)=>{
    if (a[k] === b[k]) return 0;
    if (k === 'name') {
      return dir === 'asc' ? a.name.localeCompare(b.name) : b.name.localeCompare(a.name);
    }
    return dir === 'asc' ? (a[k] - b[k]) : (b[k] - a[k]);
  });
  renderList(out);
}

function copyLink(url){
  // رابط كامل: الموقع الحالي + اسم الملف (مع ترميز)
  const origin = location.origin + location.pathname.replace(/\/[^\/]*$/,'/');
  const full = origin + url;
  navigator.clipboard?.writeText(full).then(()=>{
    alert('تم نسخ الرابط: ' + full);
  }).catch(()=>{ prompt('انسخ الرابط يدوياً:', full); });
}

// أحداث
searchInput.addEventListener('input', debounce(filterAndSort, 180));
sortSelect.addEventListener('change', filterAndSort);
refreshBtn.addEventListener('click', ()=> location.reload());

// utils
function debounce(fn, t){ let timer; return function(){ clearTimeout(timer); timer = setTimeout(()=>fn.apply(this,arguments), t); } }

// بداية
filterAndSort();
</script>
</body>
</html>
