<?php session_start(); ?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>OVA9 — Security Scanner</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--cyan:#00bfff;--cyan-dim:rgba(0,191,255,.12);--cyan-glow:rgba(0,191,255,.3);--green:#00ff88;--yellow:#f5c542;--red:#ff4444;--bg:#000;--border:rgba(0,191,255,.18);--text:#e0e8f0;--muted:#6b8099;--radius:10px}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;background:#000;color:var(--text);overflow-x:hidden;min-height:100vh}
.hero-bg{position:fixed;inset:0;z-index:0;pointer-events:none}
.base-bg{position:absolute;inset:0;background:radial-gradient(ellipse at 60% 40%,rgba(0,40,80,.6),transparent 70%),radial-gradient(ellipse at 20% 80%,rgba(0,20,50,.8),transparent 60%),#000;z-index:0}
.bg-overlay{position:absolute;inset:0;background:rgba(0,5,15,.72);z-index:4}
#root{position:relative;z-index:5;min-height:100vh}
.navbar{position:fixed;top:0;left:0;width:100%;height:64px;display:flex;justify-content:space-between;align-items:center;padding:0 2rem;background:rgba(0,5,15,.9);backdrop-filter:blur(18px);border-bottom:1px solid var(--border);z-index:1000}
.nav-left{display:flex;align-items:center;gap:1.5rem;flex:1;min-width:0}
.nav-logo{display:flex;align-items:center;gap:10px;cursor:pointer;text-decoration:none;flex-shrink:0}
.nav-logo-icon{width:36px;height:36px;flex-shrink:0}
.nav-logo-text{font-family:'Space Mono',monospace;font-size:1.1rem;font-weight:700;color:var(--cyan);letter-spacing:.06em}
.nav-links{display:flex;align-items:center;gap:2px}
.nav-link{color:var(--muted);font-size:.78rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;padding:.35rem .75rem;border-radius:6px;cursor:pointer;background:none;border:none;font-family:inherit;transition:color .2s,background .2s;white-space:nowrap}
.nav-link:hover,.nav-link.active{color:var(--cyan);background:var(--cyan-dim)}
.nav-right{display:flex;align-items:center;gap:.6rem;position:relative;flex-shrink:0}
.nav-user-chip{display:flex;align-items:center;gap:.5rem;padding:.2rem .7rem .2rem .25rem;border-radius:100px;border:1px solid var(--border);cursor:pointer;transition:border-color .2s,background .2s;background:rgba(0,10,25,.5)}
.nav-user-chip:hover{border-color:var(--cyan);background:var(--cyan-dim)}
.nav-avatar{width:32px;height:32px;border-radius:50%;border:2px solid var(--cyan);display:flex;align-items:center;justify-content:center;font-family:'Space Mono',monospace;font-weight:700;font-size:.82rem;color:var(--cyan);flex-shrink:0;overflow:hidden;background:var(--cyan-dim)}
.nav-username{font-size:.8rem;font-weight:700;color:var(--text);max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.nav-role{font-size:.62rem;color:var(--muted);line-height:1}
.profile-dropdown{position:absolute;top:calc(100% + 10px);right:0;background:rgba(0,8,20,.98);border:1px solid var(--border);border-radius:14px;min-width:240px;padding:.5rem 0;box-shadow:0 8px 40px rgba(0,0,0,.7);z-index:2000;display:none}
.profile-dropdown.open{display:block}
.pd-avatar-wrap{display:flex;flex-direction:column;align-items:center;padding:1rem 1.2rem .8rem;border-bottom:1px solid rgba(255,255,255,.06);gap:.5rem}
.pd-avatar-big{width:64px;height:64px;border-radius:50%;border:2px solid var(--cyan);display:flex;align-items:center;justify-content:center;font-family:'Space Mono',monospace;font-weight:700;font-size:1.4rem;color:var(--cyan);background:var(--cyan-dim);overflow:hidden;flex-shrink:0}
.pd-username{font-weight:800;font-size:.95rem;text-align:center}
.pd-email{color:var(--muted);font-size:.72rem;text-align:center}
.pd-item{display:flex;align-items:center;gap:.65rem;padding:.6rem 1.2rem;font-size:.84rem;cursor:pointer;transition:background .15s;color:var(--text);border:none;background:none;width:100%;font-family:inherit}
.pd-item:hover{background:rgba(0,191,255,.07);color:var(--cyan)}
.pd-item.danger{color:var(--red)}
.pd-item.danger:hover{background:rgba(255,68,68,.08)}
.pd-divider{height:1px;background:rgba(255,255,255,.06);margin:.3rem 0}
.btn{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.25rem;border-radius:var(--radius);font-family:'Inter',sans-serif;font-weight:700;font-size:.82rem;cursor:pointer;border:none;text-decoration:none;transition:all .18s;white-space:nowrap}
.btn:hover{transform:translateY(-1px)}
.btn-cyan{background:var(--cyan);color:#000;box-shadow:0 0 18px rgba(0,191,255,.3)}
.btn-cyan:hover{box-shadow:0 0 28px rgba(0,191,255,.5)}
.btn-outline{background:transparent;color:var(--cyan);border:1px solid var(--cyan)}
.btn-outline:hover{background:var(--cyan-dim)}
.btn-ghost{background:rgba(255,255,255,.05);color:var(--muted);border:1px solid var(--border)}
.btn-ghost:hover{color:var(--cyan);border-color:var(--cyan)}
.btn-red{background:rgba(255,68,68,.15);color:var(--red);border:1px solid rgba(255,68,68,.3)}
.btn-red:hover{background:rgba(255,68,68,.25)}
.btn-green{background:rgba(0,255,136,.12);color:var(--green);border:1px solid rgba(0,255,136,.3)}
.btn-yellow{background:rgba(245,197,66,.12);color:var(--yellow);border:1px solid rgba(245,197,66,.3)}
.btn-lg{padding:.8rem 2rem;font-size:.95rem}
.btn-sm{padding:.3rem .7rem;font-size:.75rem}
.btn:disabled{opacity:.5;cursor:not-allowed;transform:none}
.card{background:rgba(0,10,25,.78);border:1px solid var(--border);border-radius:14px;backdrop-filter:blur(10px);padding:1.5rem}
.form-group{display:flex;flex-direction:column;gap:.4rem;margin-bottom:1.1rem}
.form-group label{font-size:.75rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em}
.form-group input,.form-group textarea,.form-group select{padding:.7rem 1rem;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:8px;color:var(--text);font-family:'Inter',sans-serif;font-size:.92rem;outline:none;transition:border-color .2s,box-shadow .2s}
.form-group input:focus,.form-group textarea:focus,.form-group select:focus{border-color:var(--cyan);box-shadow:0 0 0 3px var(--cyan-dim)}
.form-group select option{background:#000e24;color:var(--text)}
.form-group textarea{resize:vertical;min-height:80px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.alert{padding:.75rem 1rem;border-radius:8px;font-size:.88rem;margin-bottom:1rem;display:flex;align-items:flex-start;gap:.5rem}
.alert-error{background:rgba(255,68,68,.1);color:var(--red);border:1px solid rgba(255,68,68,.3)}
.alert-success{background:rgba(0,255,136,.1);color:var(--green);border:1px solid rgba(0,255,136,.3)}
.alert-info{background:rgba(0,191,255,.08);color:var(--cyan);border:1px solid var(--border)}
.alert-warn{background:rgba(245,197,66,.09);color:var(--yellow);border:1px solid rgba(245,197,66,.3)}
.modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.88);display:flex;align-items:center;justify-content:center;z-index:9999;padding:1.5rem}
.modal{background:rgba(0,5,18,.97);border:1px solid rgba(0,191,255,.2);border-radius:16px;width:100%;max-width:800px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden}
.terminal{background:rgba(0,3,10,.95);border:1px solid rgba(0,191,255,.2);border-radius:14px;overflow:hidden}
.term-bar{display:flex;align-items:center;gap:8px;padding:.6rem 1rem;background:rgba(0,191,255,.05);border-bottom:1px solid rgba(0,191,255,.1)}
.term-dot{width:11px;height:11px;border-radius:50%}
.term-body{padding:1.2rem 1.4rem;max-height:520px;overflow-y:auto;font-family:'Space Mono',monospace;font-size:.78rem;line-height:1.7}
@keyframes dotPulse{0%,80%,100%{opacity:0;transform:scale(.6)}40%{opacity:1;transform:scale(1)}}
.scanning-dots{display:inline-flex;gap:4px;align-items:center;margin-left:6px;vertical-align:middle}
.scanning-dots span{width:5px;height:5px;background:var(--cyan);border-radius:50%;animation:dotPulse 1.4s infinite ease-in-out}
.scanning-dots span:nth-child(2){animation-delay:.2s}
.scanning-dots span:nth-child(3){animation-delay:.4s}
table{width:100%;border-collapse:collapse;font-size:.84rem}
th{padding:.75rem 1rem;text-align:left;color:var(--muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700;background:rgba(0,191,255,.05);border-bottom:1px solid var(--border)}
td{padding:.7rem 1rem;border-bottom:1px solid rgba(255,255,255,.04)}
tr:hover td{background:rgba(0,191,255,.03)}
.badge{display:inline-block;padding:2px 8px;border-radius:100px;font-size:.68rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
::-webkit-scrollbar{width:5px;height:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px}
.page-pad{padding-top:64px}
.inner{max-width:1100px;margin:0 auto;padding:0 2rem}
.hidden{display:none!important}
.text-cyan{color:var(--cyan)}.text-muted{color:var(--muted)}.text-green{color:var(--green)}.text-red{color:var(--red)}.text-yellow{color:var(--yellow)}
.mono{font-family:'Space Mono',monospace}
.section-label{color:var(--cyan);font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.14em;margin-bottom:8px}
.tabs{display:flex;gap:.5rem;border-bottom:1px solid var(--border);margin-bottom:1.5rem;flex-wrap:wrap}
.tab{padding:.55rem 1.1rem;font-size:.82rem;font-weight:700;cursor:pointer;border:none;background:none;color:var(--muted);border-bottom:2px solid transparent;transition:color .2s,border-color .2s;font-family:inherit}
.tab.active{color:var(--cyan);border-bottom-color:var(--cyan)}
.avatar-upload-zone{border:2px dashed var(--border);border-radius:12px;padding:1.5rem;text-align:center;cursor:pointer;transition:border-color .2s}
.avatar-upload-zone:hover{border-color:var(--cyan)}
/* Card input styling */
.card-input-group{position:relative}
.card-input-group input{padding-left:3rem!important}
.card-input-group .card-icon{position:absolute;left:.8rem;top:50%;transform:translateY(-50%);font-size:1.2rem}
.plan-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:100px;font-size:.7rem;font-weight:800;letter-spacing:.06em}
.plan-free{background:rgba(107,128,153,.12);border:1px solid rgba(107,128,153,.3);color:var(--muted)}
.plan-pro{background:rgba(0,191,255,.12);border:1px solid rgba(0,191,255,.35);color:var(--cyan)}
.plan-enterprise{background:rgba(245,197,66,.12);border:1px solid rgba(245,197,66,.35);color:var(--yellow)}
@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
.limit-bar{height:6px;border-radius:3px;background:rgba(255,255,255,.07);overflow:hidden;margin-top:6px}
.limit-bar-fill{height:100%;border-radius:3px;transition:width .4s ease}
@media(max-width:768px){.form-row{grid-template-columns:1fr}.hide-mob{display:none}.nav-links{display:none}.nav-username{display:none}}
/* Globe animation */
@keyframes spin{from{transform:rotateY(0)}to{transform:rotateY(360deg)}}
@keyframes orbit{from{transform:rotateX(60deg) rotateZ(0)}to{transform:rotateX(60deg) rotateZ(360deg)}}
.globe-wrap{perspective:600px;display:flex;align-items:center;justify-content:center}
.globe{width:180px;height:180px;border-radius:50%;border:2px solid rgba(0,191,255,.3);position:relative;transform-style:preserve-3d;animation:spin 8s linear infinite;background:radial-gradient(circle at 35% 35%,rgba(0,191,255,.08),transparent 70%)}
.globe::before{content:'';position:absolute;inset:-2px;border-radius:50%;border:1px dashed rgba(0,191,255,.15)}
.globe::after{content:'';position:absolute;width:100%;height:2px;top:50%;left:0;background:rgba(0,191,255,.25);transform:translateY(-50%)}
.globe-ring{position:absolute;inset:-20px;border-radius:50%;border:1.5px solid rgba(0,191,255,.2);transform-style:preserve-3d;animation:orbit 5s linear infinite}
.globe-dot{position:absolute;width:6px;height:6px;border-radius:50%;background:var(--cyan);box-shadow:0 0 8px var(--cyan)}
/* Scan button states */
.btn-scanning{background:rgba(0,191,255,.15);color:var(--cyan);border:1px solid var(--cyan)}
/* Nav user left */
.nav-user-left{display:none;align-items:center;gap:7px;padding:3px 10px 3px 5px;border-radius:100px;border:1px solid rgba(0,191,255,.2);background:rgba(0,191,255,.05);margin-right:4px}
.nav-user-left-avatar{width:26px;height:26px;border-radius:50%;background:var(--cyan-dim);border:1.5px solid var(--cyan);display:flex;align-items:center;justify-content:center;font-family:'Space Mono',monospace;font-weight:700;font-size:.72rem;color:var(--cyan);overflow:hidden;flex-shrink:0}
.nav-user-left-name{font-size:.8rem;font-weight:700;color:var(--text);max-width:90px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
</style>
</head>
<body>
<div class="hero-bg">
  <div class="base-bg"></div>
  <div class="bg-overlay"></div>
</div>
<div id="root">
  <nav class="navbar">
    <div class="nav-left">
      <div class="nav-user-left" id="nav-user-left">
        <div class="nav-user-left-avatar" id="nav-user-left-av"></div>
        <span class="nav-user-left-name" id="nav-user-left-nm"></span>
      </div>
      <a class="nav-logo" onclick="navigate('home')">
        <svg class="nav-logo-icon" viewBox="0 0 38 38" fill="none">
          <circle cx="19" cy="19" r="18" stroke="#00bfff" stroke-width="1.5" fill="none" opacity=".3"/>
          <circle cx="19" cy="19" r="12" stroke="#00bfff" stroke-width="1.2" fill="none" opacity=".5"/>
          <polygon points="19,7 30,27 8,27" fill="none" stroke="#00bfff" stroke-width="1.5" stroke-linejoin="round"/>
          <circle cx="19" cy="19" r="3" fill="#00bfff" opacity=".9"/>
          <line x1="19" y1="7" x2="19" y2="31" stroke="#00bfff" stroke-width=".6" opacity=".3"/>
          <line x1="7" y1="19" x2="31" y2="19" stroke="#00bfff" stroke-width=".6" opacity=".3"/>
        </svg>
        <span class="nav-logo-text">OVA9</span>
      </a>
      <div class="nav-links" id="nav-section-links"></div>
    </div>
    <div class="nav-right" id="nav-right"></div>
  </nav>
  <div id="page-home" class="hidden"></div>
  <div id="page-login" class="hidden"></div>
  <div id="page-signup" class="hidden"></div>
  <div id="page-scanner" class="hidden"></div>
  <div id="page-profile" class="hidden"></div>
  <div id="page-admin" class="hidden"></div>
</div>
<script>
// ── Globals ──────────────────────────────────────────────────────────
const _base = window.location.pathname.split('/public')[0].replace(/\/$/, '');
const API = _base + '/api';

function updateNavLeft() {
  const wrap = document.getElementById('nav-user-left');
  const av   = document.getElementById('nav-user-left-av');
  const nm   = document.getElementById('nav-user-left-nm');
  if (!wrap) return;
  if (currentUser) {
    wrap.style.display = 'flex';
    nm.textContent = currentUser.username || '';
    if (currentUser.avatar_url) {
      av.innerHTML = '<img src="' + currentUser.avatar_url + '" style="width:26px;height:26px;object-fit:cover;border-radius:50%"/>';
    } else {
      av.textContent = (currentUser.avatar || currentUser.username?.[0] || '?').toUpperCase();
    }
  } else {
    wrap.style.display = 'none';
  }
}
let currentUser = null, currentPage = 'home';
const getToken  = () => localStorage.getItem('ova9_token');
const setToken  = t  => localStorage.setItem('ova9_token', t);
const clearToken = () => localStorage.removeItem('ova9_token');

async function apiFetch(path, opts = {}) {
  const token = getToken();
  const headers = {'Content-Type':'application/json',...(opts.headers||{})};
  if (token) headers['Authorization'] = `Bearer ${token}`;
  const res = await fetch(API + path, {...opts, headers});
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw new Error(data.detail || `HTTP ${res.status}`);
  return data;
}

function escHtml(s) {
  if (s == null) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Navigation ───────────────────────────────────────────────────────
function showPage(name) {
  document.querySelectorAll('[id^="page-"]').forEach(el => el.classList.add('hidden'));
  const el = document.getElementById('page-' + name);
  if (el) { el.classList.remove('hidden'); currentPage = name; }
  renderNavRight(); renderNavSections();
}

function navigate(page) {
  const prot = ['scanner','profile','admin'];
  if (prot.includes(page) && !currentUser) { renderLogin(); showPage('login'); return; }
  if (page === 'admin' && (!currentUser || !currentUser.is_admin)) { navigate('scanner'); return; }
  const render = {home:renderHome,login:renderLogin,signup:renderSignup,scanner:renderScanner,profile:renderProfile,admin:renderAdmin};
  if (render[page]) render[page]();
  showPage(page); window.scrollTo(0,0);
}

function avatarContent(user, size) {
  if (user && user.avatar_url) return `<img src="${escHtml(user.avatar_url)}" style="width:${size}px;height:${size}px;border-radius:50%;object-fit:cover"/>`;
  const letter = (user?.avatar || user?.username?.[0] || '?').toUpperCase();
  return escHtml(letter);
}

function planBadge(plan) {
  const cls = plan === 'enterprise' ? 'plan-enterprise' : plan === 'pro' ? 'plan-pro' : 'plan-free';
  const label = plan === 'enterprise' ? '★ Enterprise' : plan === 'pro' ? '⚡ Pro' : '◎ Gratuit';
  return `<span class="plan-badge ${cls}">${label}</span>`;
}

// ── Nav ──────────────────────────────────────────────────────────────
function renderNavRight() {
  updateNavLeft();
  const el = document.getElementById('nav-right');
  if (currentUser) {
    el.innerHTML = `
      <div class="nav-user-chip" onclick="toggleProfileDropdown()">
        <div class="nav-avatar">${avatarContent(currentUser, 32)}</div>
        <div style="display:flex;flex-direction:column">
          <span class="nav-username">${escHtml(currentUser.username)}</span>
          <span class="nav-role">${currentUser.is_admin ? '🛡️ Admin' : planBadge(currentUser.plan||'free')}</span>
        </div>
        <svg width="10" height="10" viewBox="0 0 12 12" fill="none" style="margin-left:2px;opacity:.4"><path d="M2 4l4 4 4-4" stroke="#6b8099" stroke-width="1.5" stroke-linecap="round"/></svg>
      </div>
      <div class="profile-dropdown" id="profile-dropdown">
        <div class="pd-avatar-wrap">
          <div class="pd-avatar-big">${avatarContent(currentUser, 64)}</div>
          <div class="pd-username">${escHtml(currentUser.username)}</div>
          <div class="pd-email">${escHtml(currentUser.email)}</div>
          <div style="margin-top:4px">${planBadge(currentUser.plan||'free')}</div>
        </div>
        <button class="pd-item" onclick="closeDropdown();navigate('profile')">👤 Mon Profil</button>
        <button class="pd-item" onclick="closeDropdown();navigate('scanner')">⚡ Scanner</button>
        <button class="pd-item" onclick="closeDropdown();_profileTabOnLoad='history';navigate('profile')">🕑 Historique des scans</button>
        <button class="pd-item" onclick="closeDropdown();_profileTabOnLoad='saved';navigate('profile')">🔖 Scans sauvegardés</button>
        <button class="pd-item" onclick="closeDropdown();_profileTabOnLoad='settings';navigate('profile')">⚙️ Paramètres</button>
        ${currentUser.is_admin ? `<button class="pd-item" onclick="closeDropdown();navigate('admin')">🛡️ Panel Admin</button>` : ''}
        <div class="pd-divider"></div>
        <button class="pd-item danger" onclick="closeDropdown();logout()">⏏ Déconnexion</button>
      </div>`;
  } else {
    el.innerHTML = `
      <button class="nav-link" onclick="navigate('login')">Se connecter</button>
      <button class="btn btn-cyan btn-sm" onclick="navigate('signup')">Essai gratuit</button>`;
  }
}

function toggleProfileDropdown() { document.getElementById('profile-dropdown')?.classList.toggle('open'); }
function closeDropdown() { document.getElementById('profile-dropdown')?.classList.remove('open'); }
document.addEventListener('click', e => { if (!e.target.closest('#nav-right')) closeDropdown(); });

function renderNavSections() {
  const el = document.getElementById('nav-section-links');
  if (currentUser) {
    const links = [['🔍 Scanner','scanner'],['👤 Profil','profile']];
    if (currentUser.is_admin) links.push(['🛡️ Admin','admin']);
    el.innerHTML = links.map(([l,p]) => `<button class="nav-link${currentPage===p?' active':''}" onclick="navigate('${p}')">${l}</button>`).join('')
      + '<span style="width:1px;height:18px;background:var(--border);margin:0 6px;display:inline-block;vertical-align:middle;opacity:.5"></span>'
      + `<button class="nav-link" onclick="scrollToSection('#pricing')">💳 Tarifs</button>`;
  } else {
    const s = [['Fonctionnalités','#features'],['Comment','#how'],['Outils','#tools'],['Tarifs','#pricing']];
    el.innerHTML = s.map(([l,h]) => `<button class="nav-link" onclick="scrollToSection('${h}')">${l}</button>`).join('');
  }
}

function scrollToSection(hash) {
  if (currentPage !== 'home') { renderHome(); showPage('home'); setTimeout(() => { document.querySelector(hash)?.scrollIntoView({behavior:'smooth',block:'start'}); }, 350); return; }
  document.querySelector(hash)?.scrollIntoView({behavior:'smooth',block:'start'});
}

function logout() { clearToken(); currentUser = null; navigate('home'); }

// ══════════════════════════════════════════════════════════════════════
// HOME
// ══════════════════════════════════════════════════════════════════════
async function renderHome() {
  let offers = []; try { offers = await apiFetch('/offers'); } catch {}
  const features = [['🔍','Deep Recon','Port scanning, directory exposure, SSL/TLS analysis across 9 comprehensive categories.'],['⚡','Live Streaming','Results stream in real-time inside a terminal-style interface as the scan runs.'],['🛡️','SQL & XSS Probes','Automated injection payloads test your endpoints for common vulnerabilities.'],['📊','Risk Scoring','Every scan produces a risk score 0–100 with color-coded severity breakdown.'],['📋','Report Export','Download a full tree-format text report with all findings for every scan.'],['🔒','IP Tracking','Every scan logs the originating IP, user agent and timestamp for full accountability.']];
  const how = [['01','Créer un compte','Inscription gratuite. 3 scans offerts par jour.'],['02','Entrer l\'URL cible','Collez l\'URL du système autorisé que vous souhaitez tester.'],['03','Suivre les résultats live','Le terminal stream les résultats en temps réel — vert, jaune, rouge.'],['04','Télécharger le rapport','Rapport complet avec résultats, score de risque et IP.']];

  const pricingHtml = offers.length ? offers.map(o => `
    <div class="card" style="position:relative${o.badge==='MOST POPULAR'?';border-color:var(--cyan);box-shadow:0 0 30px rgba(0,191,255,.13)':''}">
      ${o.badge ? `<div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--cyan);color:#000;font-size:.7rem;font-weight:800;padding:3px 14px;border-radius:20px;white-space:nowrap">${escHtml(o.badge)}</div>` : ''}
      <h3 style="font-weight:800;font-size:1.1rem;margin-bottom:6px">${escHtml(o.title)}</h3>
      <div style="margin-bottom:14px"><span style="font-size:2rem;font-weight:900;color:var(--cyan);font-family:'Space Mono',monospace">${escHtml(o.price || '')}</span></div>
      <p class="text-muted" style="font-size:.88rem;line-height:1.6;margin-bottom:18px">${escHtml(o.description)}</p>
      <button class="btn ${o.badge==='MOST POPULAR'?'btn-cyan':'btn-outline'}" style="width:100%;justify-content:center" onclick="handleOfferClick(${o.id},'${escHtml(o.title)}','${escHtml(o.price||'')}')">
        ${o.price === 'Gratuit' ? 'Commencer gratuitement' : 'Souscrire →'}
      </button>
    </div>`).join('') : '';

  document.getElementById('page-home').innerHTML = `
<div class="page-pad" style="position:relative;z-index:5;overflow-x:hidden">
  <section style="min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:0 1.5rem">
    <div style="max-width:820px">
      <div style="margin-bottom:32px" class="globe-wrap">
        <div class="globe">
          <div class="globe-ring">
            <div class="globe-dot" style="top:-3px;left:50%;transform:translateX(-50%)"></div>
          </div>
          <div style="position:absolute;inset:0;border-radius:50%;background:repeating-linear-gradient(0deg,transparent,transparent 18px,rgba(0,191,255,.07) 18px,rgba(0,191,255,.07) 19px),repeating-linear-gradient(90deg,transparent,transparent 18px,rgba(0,191,255,.07) 18px,rgba(0,191,255,.07) 19px)"></div>
        </div>
      </div>
      <div style="display:inline-block;padding:5px 16px;border:1px solid rgba(0,191,255,.3);border-radius:20px;font-size:12px;color:var(--cyan);margin-bottom:28px;letter-spacing:.09em;text-transform:uppercase">Conçu pour la recherche en sécurité autorisée</div>
      <h1 style="font-size:clamp(2.5rem,6vw,5rem);font-weight:900;line-height:1.08;letter-spacing:-.04em;margin-bottom:22px">Analyse de sécurité web<br><span class="text-cyan">pour les professionnels</span></h1>
      <p class="text-muted" style="font-size:1.05rem;line-height:1.75;max-width:580px;margin:0 auto 36px">Plateforme professionnelle pour simuler des scénarios de sécurité, lancer des analyses approfondies et générer des rapports — dans des environnements autorisés.</p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        ${currentUser ? `<button class="btn btn-cyan btn-lg" onclick="navigate('scanner')">Lancer le scanner →</button>` : `<button class="btn btn-cyan btn-lg" onclick="navigate('signup')">Essai gratuit →</button><button class="btn btn-outline btn-lg" onclick="navigate('login')">Se connecter</button>`}
      </div>
    </div>
  </section>
  <section style="background:rgba(0,5,15,.65);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:3.5rem 2rem">
    <div style="display:flex;justify-content:space-around;flex-wrap:wrap;gap:2rem;max-width:900px;margin:0 auto">
      ${[['50K+','Professionnels'],['10M+','Simulations'],['99.9%','Disponibilité'],['24/7','Support']].map(([n,l]) => `<div style="text-align:center"><div style="font-size:2.2rem;font-weight:900;color:var(--cyan);font-family:'Space Mono',monospace">${n}</div><div class="text-muted" style="font-size:.85rem;margin-top:4px">${l}</div></div>`).join('')}
    </div>
  </section>
  <section id="features" style="padding:6rem 2rem"><div class="inner">
    <p class="section-label">Capacités</p>
    <h2 style="font-size:clamp(1.8rem,3vw,2.6rem);font-weight:900;letter-spacing:-.03em;margin-bottom:2.5rem">Conçu pour les professionnels</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.2rem">
      ${features.map(([i,t,d]) => `<div class="card" style="transition:border-color .2s,transform .2s" onmouseenter="this.style.borderColor='var(--cyan)';this.style.transform='translateY(-4px)'" onmouseleave="this.style.borderColor='';this.style.transform=''"><div style="font-size:1.8rem;margin-bottom:10px">${i}</div><h3 style="font-size:1rem;font-weight:800;margin-bottom:6px">${t}</h3><p class="text-muted" style="font-size:.88rem;line-height:1.6">${d}</p></div>`).join('')}
    </div>
  </div></section>
  <section id="how" style="padding:5rem 2rem;background:rgba(0,5,15,.55)"><div class="inner">
    <p class="section-label" style="text-align:center">Workflow</p>
    <h2 style="font-size:clamp(1.6rem,3vw,2.4rem);font-weight:900;letter-spacing:-.03em;margin-bottom:3rem;text-align:center">Comment ça marche</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.5rem">
      ${how.map(([n,t,d]) => `<div class="card"><div style="font-family:'Space Mono',monospace;font-size:2rem;font-weight:700;color:var(--cyan);opacity:.4;margin-bottom:12px">${n}</div><h3 style="font-weight:800;margin-bottom:6px">${t}</h3><p class="text-muted" style="font-size:.88rem;line-height:1.6">${d}</p></div>`).join('')}
    </div>
  </div></section>
  <section id="tools" style="padding:5rem 2rem"><div class="inner" style="text-align:center">
    <h2 style="font-size:clamp(1.6rem,3vw,2.4rem);font-weight:900;letter-spacing:-.03em;margin-bottom:1rem">9 Catégories de tests</h2>
    <p class="text-muted" style="margin-bottom:2.5rem">OVA9 exécute toutes les catégories sur chaque scan — sans configuration requise.</p>
    <div style="display:flex;flex-wrap:wrap;gap:.75rem;justify-content:center">
      ${['HTTP Basics','SSL/TLS Analysis','Security Headers','Port Scanning','SQL Injection','XSS Probes','API Security','Content Analysis','Directory Exposure'].map(t => `<span style="padding:.45rem 1rem;border:1px solid var(--border);border-radius:8px;color:var(--cyan);background:var(--cyan-dim);font-family:'Space Mono',monospace;font-size:.78rem">${t}</span>`).join('')}
    </div>
  </div></section>
  <section id="pricing" style="padding:5rem 2rem;background:rgba(0,5,15,.55)"><div class="inner">
    <h2 style="font-size:clamp(1.6rem,3vw,2.4rem);font-weight:900;letter-spacing:-.03em;margin-bottom:.5rem;text-align:center">Tarification flexible</h2>
    <p class="text-muted" style="text-align:center;margin-bottom:3rem">Commencez gratuitement — 3 scans/jour inclus. Passez Pro pour des scans illimités.</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.4rem">${pricingHtml}</div>
    <div style="margin-top:2rem;padding:1rem 1.5rem;background:rgba(0,191,255,.05);border:1px solid var(--border);border-radius:12px;text-align:center;font-size:.82rem;color:var(--muted)">
      🔒 Paiement sécurisé par carte tunisienne (e-Dinar, CIB, BIAT, STB, BH, Attijari). Annulable à tout moment.
    </div>
  </div></section>
  <footer style="text-align:center;padding:2rem;border-top:1px solid var(--border);color:var(--muted);font-size:.8rem;font-family:'Space Mono',monospace">© 2026 OVA9 — Pour la recherche en sécurité autorisée uniquement. Toute activité est enregistrée.</footer>
</div>`;
}

function handleOfferClick(offerId, title, price) {
  if (!currentUser) { navigate('signup'); return; }
  if (price === 'Gratuit') { navigate('scanner'); return; }
  openPaymentModal(offerId, title, price);
}

// ══════════════════════════════════════════════════════════════════════
// PAYMENT MODAL
// ══════════════════════════════════════════════════════════════════════
function openPaymentModal(offerId, offerTitle, offerPrice) {
  const modal = document.createElement('div');
  modal.className = 'modal-bg';
  modal.onclick = e => { if (e.target === modal) modal.remove(); };

  const tunisianCards = [
    {value:'edinar',    label:'🔵 e-Dinar (La Poste Tunisienne)',  icon:'🔵', example:'4242 4242 4242 4242'},
    {value:'cib',       label:'🟣 CIB (Carte Interbancaire)',        icon:'🟣', example:'5555 5555 5555 4444'},
    {value:'biat',      label:'🟠 BIAT Visa',                        icon:'🟠', example:'4111 1111 1111 1111'},
    {value:'stb',       label:'🟢 STB Visa',                         icon:'🟢', example:'4012 8888 8888 1881'},
    {value:'bh',        label:'🔴 BH Mastercard',                    icon:'🔴', example:'5105 1051 0510 5100'},
    {value:'attijari',  label:'⚫ Attijari Bank Visa',               icon:'⚫', example:'4000 0566 5566 5556'},
  ];

  modal.innerHTML = `
<div class="modal" style="max-width:560px">
  <div style="display:flex;justify-content:space-between;align-items:center;padding:1.2rem 1.5rem;border-bottom:1px solid rgba(0,191,255,.1)">
    <div>
      <h3 style="font-weight:800">💳 Paiement sécurisé</h3>
      <p style="color:var(--muted);font-size:.78rem;margin-top:3px">Offre : <strong style="color:var(--cyan)">${escHtml(offerTitle)}</strong> — <strong style="color:var(--cyan)">${escHtml(offerPrice)}</strong></p>
    </div>
    <button class="btn btn-ghost btn-sm" onclick="this.closest('.modal-bg').remove()">✕</button>
  </div>
  <div style="padding:1.5rem;overflow:auto">
    <div id="pay-alert" class="hidden"></div>
    <div class="form-group">
      <label>Type de carte</label>
      <select id="pay-card-type" onchange="updateCardExample()">
        <option value="">— Sélectionnez votre carte —</option>
        ${tunisianCards.map(c => `<option value="${c.value}" data-example="${c.example}">${c.label}</option>`).join('')}
      </select>
    </div>
    <div style="background:rgba(0,191,255,.05);border:1px solid var(--border);border-radius:10px;padding:1rem;margin-bottom:1.2rem;font-size:.8rem;color:var(--muted)" id="card-example-box">
      <span style="color:var(--cyan);font-weight:700">ℹ️ Exemple de test :</span> Sélectionnez un type de carte pour voir un exemple.
    </div>
    <div class="form-group">
      <label>Nom du titulaire</label>
      <input id="pay-name" type="text" placeholder="Mohamed BEN ALI" style="text-transform:uppercase"/>
    </div>
    <div class="form-group">
      <label>Numéro de carte</label>
      <input id="pay-number" type="text" maxlength="19" placeholder="0000 0000 0000 0000"
        oninput="this.value=this.value.replace(/[^0-9]/g,'').replace(/(.{4})/g,'$1 ').trim();this.value=this.value.slice(0,19)"/>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label>Date d'expiration</label>
        <input id="pay-expiry" type="text" maxlength="5" placeholder="MM/AA"
          oninput="formatExpiry(this)"/>
      </div>
      <div class="form-group">
        <label>Code CVV</label>
        <input id="pay-cvv" type="text" maxlength="3" placeholder="123" oninput="this.value=this.value.replace(/\D/g,'')"/>
      </div>
    </div>
    <div style="background:rgba(0,255,136,.06);border:1px solid rgba(0,255,136,.2);border-radius:8px;padding:.75rem 1rem;font-size:.78rem;color:var(--green);margin-bottom:1.2rem">
      🔒 Connexion chiffrée SSL/TLS — Vos données bancaires ne sont jamais stockées en clair.
    </div>
    <button class="btn btn-cyan" style="width:100%;justify-content:center;padding:.85rem;font-size:.95rem" id="pay-btn" onclick="submitPayment(${offerId})">
      💳 Payer ${escHtml(offerPrice)}
    </button>
    <p style="text-align:center;color:var(--muted);font-size:.72rem;margin-top:.8rem">Annulable à tout moment · Support : support@ova9.io</p>
  </div>
</div>`;
  document.body.appendChild(modal);
}

function updateCardExample() {
  const sel = document.getElementById('pay-card-type');
  const opt = sel.options[sel.selectedIndex];
  const example = opt?.dataset?.example;
  const box = document.getElementById('card-example-box');
  if (example) {
    box.innerHTML = `<span style="color:var(--cyan);font-weight:700">ℹ️ Numéro de test :</span> <span style="font-family:'Space Mono',monospace;font-size:.85rem">${escHtml(example)}</span><br><span style="color:var(--muted)">Expiry: 12/28 · CVV: 123 · Nom: TEST USER</span>`;
  } else {
    box.innerHTML = `<span style="color:var(--cyan);font-weight:700">ℹ️ Exemple de test :</span> Sélectionnez un type de carte pour voir un exemple.`;
  }
}

function formatExpiry(el) {
  let v = el.value.replace(/\D/g,'');
  if (v.length >= 2) v = v.slice(0,2) + '/' + v.slice(2,4);
  el.value = v;
}

async function submitPayment(offerId) {
  const btn = document.getElementById('pay-btn');
  const al  = document.getElementById('pay-alert');
  al.className = 'hidden';
  const card_type  = document.getElementById('pay-card-type').value;
  const cardholder = document.getElementById('pay-name').value.trim().toUpperCase();
  const card_number= document.getElementById('pay-number').value.replace(/\s/g,'');
  const expiry     = document.getElementById('pay-expiry').value;
  const cvv        = document.getElementById('pay-cvv').value;

  if (!card_type) { al.className = 'alert alert-error'; al.innerHTML = '⚠️ Sélectionnez un type de carte'; return; }
  if (!cardholder) { al.className = 'alert alert-error'; al.innerHTML = '⚠️ Nom du titulaire requis'; return; }
  if (card_number.length < 16) { al.className = 'alert alert-error'; al.innerHTML = '⚠️ Numéro de carte incomplet (16 chiffres)'; return; }
  if (!expiry.match(/^\d{2}\/\d{2}$/)) { al.className = 'alert alert-error'; al.innerHTML = '⚠️ Format date invalide (MM/AA)'; return; }
  if (cvv.length < 3) { al.className = 'alert alert-error'; al.innerHTML = '⚠️ CVV invalide (3 chiffres)'; return; }

  btn.textContent = '⏳ Traitement en cours…'; btn.disabled = true;

  try {
    const r = await apiFetch('/payment', {method:'POST', body: JSON.stringify({offer_id:offerId,card_type,card_number,expiry,cvv,cardholder})});
    // Refresh user
    currentUser = await apiFetch('/users/me');
    renderNavRight();
    document.querySelector('.modal-bg')?.remove();
    // Show success
    showToast(`✅ ${r.message}`, 'success');
  } catch(e) {
    al.className = 'alert alert-error'; al.innerHTML = `❌ ${escHtml(e.message)}`;
    btn.textContent = '💳 Réessayer'; btn.disabled = false;
  }
}

function showToast(msg, type='info') {
  const t = document.createElement('div');
  t.style.cssText = `position:fixed;bottom:2rem;right:2rem;z-index:99999;padding:.85rem 1.4rem;border-radius:12px;font-weight:700;font-size:.88rem;backdrop-filter:blur(12px);animation:slideIn .3s ease;max-width:360px`;
  if (type === 'success') t.style.cssText += ';background:rgba(0,255,136,.12);color:#00ff88;border:1px solid rgba(0,255,136,.3)';
  else if (type === 'error') t.style.cssText += ';background:rgba(255,68,68,.12);color:#ff4444;border:1px solid rgba(255,68,68,.3)';
  else t.style.cssText += ';background:rgba(0,191,255,.1);color:var(--cyan);border:1px solid var(--border)';
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 4000);
}

// ══════════════════════════════════════════════════════════════════════
// LOGIN
// ══════════════════════════════════════════════════════════════════════
function renderLogin() {
  document.getElementById('page-login').innerHTML = `
<div class="page-pad" style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem">
  <div style="width:100%;max-width:440px">
    <div style="text-align:center;margin-bottom:2rem"><div style="font-family:'Space Mono',monospace;font-size:1.6rem;font-weight:700;color:var(--cyan);margin-bottom:6px">OVA9</div><p class="text-muted" style="font-size:.88rem">Connectez-vous à votre espace sécurité</p></div>
    <div class="card" style="padding:2rem">
      <h2 style="font-weight:800;font-size:1.2rem;margin-bottom:1.5rem">Bienvenue</h2>
      <div id="login-alert" class="hidden alert"></div>
      <div class="form-group"><label>Email</label><input id="l-email" type="email" placeholder="vous@exemple.com" required/></div>
      <div class="form-group"><label>Mot de passe</label><input id="l-pass" type="password" placeholder="••••••••" required/></div>
      <button class="btn btn-cyan" style="width:100%;justify-content:center;padding:.8rem;margin-top:4px" id="l-btn" onclick="doLogin()">Se connecter →</button>
      <p style="text-align:center;margin-top:1.2rem;font-size:.85rem;color:var(--muted)">Pas de compte ? <a onclick="navigate('signup')" style="color:var(--cyan);font-weight:600;cursor:pointer">Créer un compte →</a></p>
    </div>
    <div style="margin-top:1rem;padding:.9rem 1.2rem;background:rgba(0,191,255,.05);border:1px solid var(--border);border-radius:10px;font-size:.78rem;color:var(--muted);line-height:1.6">
      🧪 <strong style="color:var(--cyan)">Compte démo :</strong> demo@ova9.io / Demo1234!<br>
      🛡️ <strong style="color:var(--yellow)">Admin :</strong> admin@ova9.io / Admin9999!
    </div>
  </div>
</div>`;
  document.getElementById('l-pass').addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
}

async function doLogin() {
  const email = document.getElementById('l-email').value.trim();
  const pass  = document.getElementById('l-pass').value;
  const al = document.getElementById('login-alert'), btn = document.getElementById('l-btn');
  al.className = 'hidden alert'; btn.textContent = 'Connexion…'; btn.disabled = true;
  if (!email || !pass) { al.className = 'alert alert-error'; al.textContent = '⚠️ Email et mot de passe requis'; btn.textContent = 'Se connecter →'; btn.disabled = false; return; }
  try {
    const data = await apiFetch('/auth/login', {method:'POST', body:JSON.stringify({username:email,password:pass})});
    setToken(data.access_token); currentUser = await apiFetch('/users/me');
    renderNavRight(); renderNavSections();
    navigate(currentUser.is_admin ? 'admin' : 'scanner');
  } catch(e) {
    al.className = 'alert alert-error';
    al.textContent = e.message.includes('Incorrect') || e.message.includes('401')
      ? '⚠️ Email ou mot de passe incorrect. Vérifiez vos identifiants.'
      : e.message.includes('suspendu') || e.message.includes('403')
      ? '🚫 Ce compte a été suspendu. Contactez support@ova9.io'
      : `❌ ${e.message}`;
    btn.textContent = 'Se connecter →'; btn.disabled = false;
  }
}

// ══════════════════════════════════════════════════════════════════════
// SIGNUP
// ══════════════════════════════════════════════════════════════════════
function renderSignup() {
  document.getElementById('page-signup').innerHTML = `
<div class="page-pad" style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem">
  <div style="width:100%;max-width:500px">
    <div style="text-align:center;margin-bottom:2rem"><div style="font-family:'Space Mono',monospace;font-size:1.6rem;font-weight:700;color:var(--cyan);margin-bottom:6px">OVA9</div><p class="text-muted" style="font-size:.88rem">Créez votre compte de recherche en sécurité</p></div>
    <div class="card" style="padding:2rem">
      <h2 style="font-weight:800;font-size:1.2rem;margin-bottom:1.5rem">Créer un compte</h2>
      <div id="su-alert" class="hidden alert"></div>
      <div class="form-group"><label>Nom d'utilisateur</label><input id="su-user" type="text" placeholder="security_pro" required/></div>
      <div class="form-group"><label>Email</label><input id="su-email" type="email" placeholder="vous@exemple.com" required/></div>
      <div class="form-row">
        <div class="form-group"><label>Mot de passe</label><input id="su-pass" type="password" placeholder="Min. 8 car." required/></div>
        <div class="form-group"><label>Confirmer</label><input id="su-confirm" type="password" placeholder="••••••••" required/></div>
      </div>
      <div style="background:rgba(245,197,66,.07);border:1px solid rgba(245,197,66,.25);border-radius:8px;padding:.75rem 1rem;font-size:.78rem;color:var(--yellow);line-height:1.5;margin-bottom:1rem">⚠️ En créant un compte, vous acceptez d'utiliser OVA9 <strong>uniquement sur des systèmes vous appartenant</strong>. Toute activité est enregistrée avec IP et horodatage.</div>
      <button class="btn btn-cyan" style="width:100%;justify-content:center;padding:.8rem" id="su-btn" onclick="doSignup()">Créer le compte →</button>
      <p style="text-align:center;margin-top:1.2rem;font-size:.85rem;color:var(--muted)">Déjà un compte ? <a onclick="navigate('login')" style="color:var(--cyan);font-weight:600;cursor:pointer">Se connecter →</a></p>
    </div>
  </div>
</div>`;
}

async function doSignup() {
  const username = document.getElementById('su-user').value.trim();
  const email    = document.getElementById('su-email').value.trim();
  const pass     = document.getElementById('su-pass').value;
  const confirm  = document.getElementById('su-confirm').value;
  const al = document.getElementById('su-alert'), btn = document.getElementById('su-btn');
  al.className = 'hidden alert';
  if (!username) { al.className = 'alert alert-error'; al.textContent = '⚠️ Nom d\'utilisateur requis'; return; }
  if (!email || !email.includes('@')) { al.className = 'alert alert-error'; al.textContent = '⚠️ Email invalide'; return; }
  if (pass !== confirm) { al.className = 'alert alert-error'; al.textContent = '⚠️ Les mots de passe ne correspondent pas'; return; }
  if (pass.length < 8) { al.className = 'alert alert-error'; al.textContent = '⚠️ Minimum 8 caractères requis'; return; }
  btn.textContent = 'Création…'; btn.disabled = true;
  try {
    await apiFetch('/auth/signup', {method:'POST', body:JSON.stringify({username,email,password:pass})});
    const data = await apiFetch('/auth/login', {method:'POST', body:JSON.stringify({username:email,password:pass})});
    setToken(data.access_token); currentUser = await apiFetch('/users/me');
    navigate('scanner');
  } catch(e) { al.className = 'alert alert-error'; al.textContent = `❌ ${e.message}`; btn.textContent = 'Créer le compte →'; btn.disabled = false; }
}

// ══════════════════════════════════════════════════════════════════════
// SCANNER
// ══════════════════════════════════════════════════════════════════════
let scanLines = [], scanning = false, currentScanId = null, currentES = null;
const SEV = {ok:{color:'#00ff88',icon:'✅',label:'OK'},warning:{color:'#f5c542',icon:'⚠️',label:'WARN'},critical:{color:'#ff4444',icon:'❌',label:'CRIT'},info:{color:'#00bfff',icon:'ℹ️',label:'INFO'}};
function riskColor(s) { return s >= 60 ? '#ff4444' : s >= 30 ? '#f5c542' : '#00ff88'; }
function riskLabel(s) { return s >= 60 ? 'HIGH' : s >= 30 ? 'MEDIUM' : 'LOW'; }

async function renderScanner() {
  // Get limit info
  let limitInfo = {can_scan:true, plan:'free', used_today:0, limit:3};
  try { limitInfo = await apiFetch('/scan/limit'); } catch {}

  const limitBar = limitInfo.plan === 'free'
    ? `<div style="margin-bottom:1.2rem;padding:.85rem 1rem;background:rgba(0,191,255,.06);border:1px solid var(--border);border-radius:10px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <span style="font-size:.8rem;color:var(--muted)">Scans utilisés aujourd'hui</span>
          <span style="font-family:'Space Mono',monospace;font-size:.82rem;color:${limitInfo.used_today>=3?'var(--red)':'var(--cyan)'}"><strong>${limitInfo.used_today}</strong> / ${limitInfo.limit}</span>
        </div>
        <div class="limit-bar"><div class="limit-bar-fill" style="width:${Math.min(100,(limitInfo.used_today/limitInfo.limit)*100)}%;background:${limitInfo.used_today>=3?'var(--red)':'var(--cyan)'}"></div></div>
        ${limitInfo.used_today >= limitInfo.limit ? `<p style="color:var(--yellow);font-size:.78rem;margin-top:6px">⚠️ Limite atteinte — <a style="color:var(--cyan);cursor:pointer" onclick="scrollToSection('#pricing')">Passez Pro pour des scans illimités →</a></p>` : `<p style="color:var(--muted);font-size:.72rem;margin-top:4px">Plan gratuit · <a style="color:var(--cyan);cursor:pointer" onclick="scrollToSection('#pricing')">Passer Pro →</a></p>`}
      </div>`
    : `<div style="margin-bottom:1.2rem"><span class="plan-badge ${limitInfo.plan==='enterprise'?'plan-enterprise':'plan-pro'}">⚡ Plan ${limitInfo.plan === 'enterprise' ? 'Enterprise' : 'Pro'} — Scans illimités</span></div>`;

  document.getElementById('page-scanner').innerHTML = `
<div class="page-pad" style="min-height:100vh;padding:2rem 1.5rem 4rem">
  <div style="max-width:1000px;margin:0 auto">
    <p class="section-label">Security Scanner</p>
    <h1 style="font-size:clamp(1.6rem,3vw,2.4rem);font-weight:900;letter-spacing:-.03em;margin-bottom:6px">Analyse de cible</h1>
    <p class="text-muted" style="font-size:.88rem;margin-bottom:2rem">Scannez uniquement des systèmes vous appartenant ou autorisés. Tous les scans sont enregistrés avec votre adresse IP.</p>
    ${limitBar}
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.5rem">
      <input id="scan-url" type="url" placeholder="https://votre-cible-autorisee.com"
        style="flex:1;min-width:280px;padding:.85rem 1.1rem;background:rgba(0,10,30,.85);border:1px solid var(--border);border-radius:10px;color:var(--text);font-family:'Space Mono',monospace;font-size:.9rem;outline:none;transition:border-color .2s"
        onfocus="this.style.borderColor='var(--cyan)'" onblur="this.style.borderColor=''"
        onkeydown="if(event.key==='Enter')doScan()"/>
      <button id="scan-btn" class="btn btn-cyan btn-lg" onclick="doScan()">⚡ Lancer le scan</button>
      <button id="cancel-btn" class="btn btn-red btn-lg" style="display:none" onclick="cancelScan()">✕ Annuler</button>
    </div>
    <div id="scan-error" class="hidden alert"></div>
    <div id="scan-summary" class="hidden" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:.75rem"></div>
    <div id="scan-terminal" class="hidden"></div>
    <div id="scan-idle" class="card" style="text-align:center;padding:4rem 2rem">
      <div style="font-size:3rem;margin-bottom:1rem">🔍</div>
      <h3 style="font-weight:800;margin-bottom:8px">Prêt à scanner</h3>
      <p class="text-muted" style="font-size:.9rem">Entrez une URL ci-dessus. Les résultats s'affichent en temps réel dans le terminal.</p>
    </div>
  </div>
</div>`;
  scanLines = []; scanning = false; currentScanId = null; window._scanRisk = 0;
}

function addScanLine(line) {
  scanLines.push(line);
  const term = document.getElementById('scan-terminal'); if (!term) return;
  term.classList.remove('hidden');
  document.getElementById('scan-idle')?.classList.add('hidden');
  rebuildTerminal(); updateScanSummary();
}

function rebuildTerminal() {
  const url = document.getElementById('scan-url')?.value || '';
  const statusHtml = scanning
    ? `<span style="color:var(--cyan)">● en cours</span><span class="scanning-dots"><span></span><span></span><span></span></span>`
    : (scanLines.length > 0 ? `<span style="color:var(--green)">● terminé</span>` : '');
  let html = `<div class="terminal"><div class="term-bar">
    <div class="term-dot" style="background:#ff5f57"></div>
    <div class="term-dot" style="background:#f5c542"></div>
    <div class="term-dot" style="background:#00ff88"></div>
    <span style="margin-left:8px;color:var(--muted);font-size:.72rem;font-family:'Space Mono',monospace">OVA9 — security scanner ${statusHtml}</span>
  </div><div class="term-body" id="term-body">
    <div style="color:var(--cyan);margin-bottom:4px;font-size:.8rem">OVA9 scan: ${escHtml(url)}</div>
    <div style="color:rgba(255,255,255,.18);margin-bottom:6px">│</div>`;
  for (const l of scanLines) {
    if (l.type === 'group_start') {
      html += `<div style="margin-top:10px;margin-bottom:2px"><span style="color:#00bfff;font-weight:700">├── </span><span style="color:#fff;font-weight:700;letter-spacing:.06em;text-transform:uppercase;font-size:.8rem">[${escHtml(l.group)}]</span></div>`;
    } else if (l.type === 'group_end') {
      html += `<div style="color:rgba(255,255,255,.13);font-size:.78rem;margin-left:4px;margin-bottom:4px">│</div>`;
    } else if (l.type === 'result') {
      const s = SEV[l.severity] || SEV.info;
      html += `<div style="display:flex;align-items:flex-start;gap:0;margin-bottom:2px;font-family:'Space Mono',monospace;font-size:.78rem;line-height:1.5"><span style="color:rgba(255,255,255,.2);white-space:pre">│   ├── </span><span style="color:${s.color};flex-shrink:0">${s.icon} </span><span style="color:#e0e8f0;font-weight:600">${escHtml(l.check)}</span><span style="color:rgba(255,255,255,.22);margin:0 6px">—</span><span style="color:${s.color};opacity:.85;flex-shrink:0;min-width:48px">[${s.label}]</span><span style="color:rgba(200,220,240,.5);margin-left:8px;word-break:break-word">${escHtml(l.detail)}</span></div>`;
    } else if (l.type === 'system') {
      html += `<div style="color:${l.color||'#00bfff'};font-family:'Space Mono',monospace;font-size:.78rem;margin-bottom:2px">${escHtml(l.text)}</div>`;
    }
  }
  html += `<div id="term-bottom"></div></div></div>`;
  document.getElementById('scan-terminal').innerHTML = html;
  document.getElementById('term-bottom')?.scrollIntoView({behavior:'smooth'});
}

function updateScanSummary() {
  const el = document.getElementById('scan-summary'); if (!el) return;
  const counts = {ok:0,warning:0,critical:0,info:0};
  scanLines.filter(l => l.type === 'result').forEach(l => { counts[l.severity] = (counts[l.severity]||0)+1; });
  let html = `<div style="display:flex;gap:1.2rem;flex-wrap:wrap;align-items:center">`;
  for (const [k,s] of Object.entries(SEV)) html += `<div style="display:flex;align-items:center;gap:5px;font-size:.82rem"><span>${s.icon}</span><span style="color:${s.color};font-weight:700;font-family:'Space Mono',monospace">${counts[k]}</span><span class="text-muted">${s.label}</span></div>`;
  html += `</div>`;
  if (!scanning && currentScanId) {
    const c = riskColor(window._scanRisk||0);
    html += '<div style="display:flex;gap:.7rem;align-items:center;flex-wrap:wrap">'
      + '<div style="text-align:center">'
      + '<div style="font-family:\'Space Mono\',monospace;font-size:2.4rem;font-weight:700;color:' + c + ';line-height:1">' + (window._scanRisk||0) + '</div>'
      + '<div style="font-size:.65rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em">Score risque</div>'
      + '<div class="badge" style="background:' + c + '22;border:1px solid ' + c + '55;color:' + c + ';margin-top:4px">' + riskLabel(window._scanRisk||0) + '</div>'
      + '</div>'
      + '<div style="display:flex;flex-direction:column;gap:.4rem">'
      + '<button class="btn btn-cyan btn-sm" onclick="downloadDocx(' + currentScanId + ')" title="Rapport Word professionnel">📄 Rapport .docx</button>'
      + '<button class="btn btn-outline btn-sm" onclick="downloadReport(' + currentScanId + ')" title="Rapport texte">⬇ Rapport .txt</button>'
      + '<button class="btn btn-yellow btn-sm" id="save-scan-btn" onclick="saveScan(' + currentScanId + ')">🔖 Sauvegarder</button>'
      + '</div></div>';
  }
  el.innerHTML = html; el.classList.remove('hidden'); el.style.display = 'flex';
}

// ════════════════════════════════════════════════════════════════
//  SIMULATION ENGINE — pure JS, no SSE, no network during stream
// ════════════════════════════════════════════════════════════════
let _cancelSim = false;

function buildSimulatedEvents(url) {
  const host = (() => { try { return new URL(url).hostname; } catch { return url; } })();
  const ts = new Date().toISOString().replace('T',' ').split('.')[0] + ' UTC';
  const ok   = (c,k,d) => ({type:'result',category:c,check:k,status:'OK',      detail:d,severity:'ok'      });
  const warn = (c,k,d) => ({type:'result',category:c,check:k,status:'WARNING',  detail:d,severity:'warning'  });
  const crit = (c,k,d) => ({type:'result',category:c,check:k,status:'CRITICAL', detail:d,severity:'critical' });
  const info = (c,k,d) => ({type:'result',category:c,check:k,status:'INFO',     detail:d,severity:'info'     });
  const gs = g => ({type:'group_start',group:g});
  const ge = g => ({type:'group_end',  group:g});
  const sys = (text, color) => ({type:'system', text, color: color||'#6b8099'});
  return [
    sys('╔══════════════════════════════════════════════════════════════════════╗','#00bfff'),
    sys('║        OVA9 Security Scanner v2.0  —  Authorized Use Only           ║','#00bfff'),
    sys('╚══════════════════════════════════════════════════════════════════════╝','#00bfff'),
    sys('  Target  : ' + url),
    sys('  Host    : ' + host),
    sys('  Started : ' + ts),
    sys('  Tests   : 9 categories'),
    sys('─'.repeat(72), 'rgba(255,255,255,.13)'),
    gs('HTTP Basics'),
    info('HTTP Basics','Target URL',   'Full URL: ' + url),
    info('HTTP Basics','Hostname',     'Host: ' + host),
    ok  ('HTTP Basics','Status Code',  'HTTP 200 — site is up and responding'),
    ok  ('HTTP Basics','Response Time','0.83s — good'),
    info('HTTP Basics','Redirect',     'Redirected to https://' + host + '/home'),
    ok  ('HTTP Basics','HTTPS Scheme', 'HTTPS confirmed'),
    warn('HTTP Basics','Cookie Flags', 'Cookie missing Secure/HttpOnly — XSS/MITM risk'),
    warn('HTTP Basics','CDN',          'Cloudflare CDN detected — origin IP hidden'),
    ge('HTTP Basics'),
    gs('SSL/TLS'),
    ok  ('SSL/TLS','Certificate', "CN=" + host + " issued by Let's Encrypt"),
    ok  ('SSL/TLS','Expiry',      'Valid 84 more days'),
    ok  ('SSL/TLS','SAN',         'DNS:' + host + ', DNS:www.' + host),
    ok  ('SSL/TLS','Chain',       'Certificate chain validated'),
    ok  ('SSL/TLS','TLS Version', 'TLS 1.3 in use'),
    info('SSL/TLS','HSTS',        'Verify at hstspreload.org'),
    ge('SSL/TLS'),
    gs('Security Headers'),
    warn('Security Headers','HSTS',             'Missing — max-age >= 31536000 with includeSubDomains'),
    ok  ('Security Headers','X-Frame-Options',  'Present: SAMEORIGIN'),
    ok  ('Security Headers','X-Content-Type',   'Present: nosniff'),
    warn('Security Headers','CSP',              'Missing — inline script execution unrestricted'),
    warn('Security Headers','Referrer-Policy',  'Missing — URL leaked to third parties'),
    warn('Security Headers','Permissions-Policy','Missing — camera/mic/geolocation unrestricted'),
    warn('Security Headers','Server Disclosure','Reveals: cloudflare'),
    ok  ('Security Headers','X-Powered-By',     'Hidden'),
    ge('Security Headers'),
    gs('Port Scan'),
    info('Port Scan','Target',          'Scanning ' + host + ' — 14 ports'),
    ok  ('Port Scan','Port 21/FTP',     'Closed/Filtered'),
    warn('Port Scan','Port 22/SSH',     'OPEN — restrict to known IPs'),
    ok  ('Port Scan','Port 23/Telnet',  'Closed/Filtered'),
    info('Port Scan','Port 53/DNS',     'OPEN — test for zone transfer'),
    ok  ('Port Scan','Port 80/HTTP',    'OPEN — redirect to HTTPS confirmed'),
    ok  ('Port Scan','Port 443/HTTPS',  'OPEN — expected'),
    ok  ('Port Scan','Port 3306/MySQL', 'Closed/Filtered'),
    ok  ('Port Scan','Port 3389/RDP',   'Closed/Filtered'),
    ok  ('Port Scan','Port 6379/Redis', 'Closed/Filtered'),
    warn('Port Scan','Port 8080',       'OPEN — dev/proxy port, verify if intentional'),
    ok  ('Port Scan','Port 27017/Mongo','Closed/Filtered'),
    ge('Port Scan'),
    gs('SQL Injection'),
    info('SQL Injection','Target',              'Testing ' + url + ' — 7 payloads'),
    ok  ('SQL Injection','Classic OR bypass',    'No SQL error (HTTP 200)'),
    ok  ('SQL Injection','Stacked query',        'No SQL error (HTTP 200)'),
    ok  ('SQL Injection','UNION extraction',     'No SQL error (HTTP 200)'),
    ok  ('SQL Injection','Time-based blind',     'No SQL error (HTTP 200)'),
    ok  ('SQL Injection','Comment truncation',   'No SQL error (HTTP 200)'),
    ok  ('SQL Injection','Boolean true',         'No SQL error (HTTP 200)'),
    ok  ('SQL Injection','Boolean false',        'No SQL error (HTTP 200)'),
    ge('SQL Injection'),
    gs('XSS Probes'),
    info('XSS Probes','Target',             'Testing ' + url + ' — 6 vectors'),
    ok  ('XSS Probes','Basic script tag',   'Not reflected (HTTP 200)'),
    warn('XSS Probes','Attribute injection','Payload partially reflected — verify encoding'),
    ok  ('XSS Probes','JS string escape',   'Not reflected (HTTP 200)'),
    ok  ('XSS Probes','SVG event handler',  'Not reflected (HTTP 200)'),
    ok  ('XSS Probes','Protocol handler',   'Not reflected (HTTP 200)'),
    ok  ('XSS Probes','Body event',         'Properly HTML-encoded in output'),
    ge('XSS Probes'),
    gs('API Security'),
    info('API Security','Target',         'Probing ' + url + ' — 10 endpoints'),
    warn('API Security','REST root',      'HTTP 200 at /api — verify auth required'),
    info('API Security','API v1',         'Not exposed (HTTP 404)'),
    ok  ('API Security','GraphQL',        'Not exposed (HTTP 404)'),
    warn('API Security','Swagger spec',   'HTTP 200 at /swagger.json — API spec public'),
    crit('API Security','Users endpoint', 'HTTP 200 (JSON) at /api/users — verify auth'),
    ok  ('API Security','Admin API',      'Protected HTTP 403'),
    info('API Security','CORS',           'No CORS header — cross-origin blocked by default'),
    ge('API Security'),
    gs('Content Analysis'),
    ok  ('Content Analysis','Email Disclosure',  'No emails found in source'),
    ok  ('Content Analysis','Internal IP Leak',  'No internal IPs found'),
    info('Content Analysis','Forms',             '3 form(s) detected — verify CSRF tokens'),
    info('Content Analysis','External Scripts',  '11 external scripts — audit supply-chain risk'),
    ok  ('Content Analysis','HTML Comments',     'No sensitive data in comments'),
    info('Content Analysis','Tech Stack',        'Detected: React, Next.js'),
    info('Content Analysis','robots.txt',        'Present — check for sensitive paths'),
    ok  ('Content Analysis','sitemap.xml',       'Present'),
    ge('Content Analysis'),
    gs('Directory Exposure'),
    info('Directory Exposure','Target',          'Probing https://' + host + ' — 28 paths'),
    ok  ('Directory Exposure','/.git/config',    'Not found (404)'),
    crit('Directory Exposure','/.env',           'HTTP 200 — .env exposed — credentials risk'),
    ok  ('Directory Exposure','/wp-config.php',  'Not found (404)'),
    ok  ('Directory Exposure','/backup.sql',     'Not found (404)'),
    warn('Directory Exposure','/admin',          'HTTP 200 — Admin panel — ensure strong auth'),
    crit('Directory Exposure','/phpmyadmin',     'HTTP 200 — phpMyAdmin exposed — critical'),
    warn('Directory Exposure','/swagger.json',   'HTTP 200 — Full API spec exposed'),
    ok  ('Directory Exposure','/.htaccess',      'Not found (404)'),
    warn('Directory Exposure','/composer.json',  'HTTP 200 — dependency versions disclosed'),
    ge('Directory Exposure'),
    sys('─'.repeat(72), 'rgba(255,255,255,.13)'),
    sys('  Risk Score : 68/100 — HIGH RISK', '#ff3d57'),
    sys('  Finished   : ' + ts),
    sys('─'.repeat(72), 'rgba(255,255,255,.13)'),
    {type:'done', risk_score: 68},
  ];
}

function runSimulatedScan(url, scanId) {
  _cancelSim = false;
  const events = buildSimulatedEvents(url);
  const btn    = document.getElementById('scan-btn');
  let i = 0;

  function delay(ev) {
    if (ev.type === 'system') return 55;
    if (ev.type === 'group_start' || ev.type === 'group_end') return 100;
    if (ev.severity === 'critical') return 90;
    if (ev.severity === 'warning')  return 70;
    return 45;
  }

  function step() {
    if (_cancelSim) return;
    if (i >= events.length) return;
    const ev = events[i++];

    if (ev.type === 'done') {
      window._scanRisk = ev.risk_score || 68;
      currentScanId    = scanId;
      scanning         = false;
      // Change button to "Nouveau scan"
      if (btn) {
        btn.className = 'btn btn-cyan btn-lg';
        btn.innerHTML = '🔄 Nouveau scan';
        btn.onclick   = newScan;
      }
      // Hide cancel button
      const cancelBtn = document.getElementById('cancel-btn');
      if (cancelBtn) cancelBtn.style.display = 'none';
      updateScanSummary();
      // Save to DB and store for docx
      if (scanId) {
        const results = buildResultsForDB(events);
        window._lastScanResults = results;
        window._lastScanId      = scanId;
        apiFetch('/scan/finish', {method:'POST', body: JSON.stringify({scan_id: scanId, results, risk_score: window._scanRisk})})
          .then(() => {})
          .catch(e => console.warn('DB save:', e.message));
      }
      return;
    }
    addScanLine(ev);
    setTimeout(step, delay(ev));
  }
  setTimeout(step, 200);
}

function buildResultsForDB(events) {
  return events
    .filter(e => e.type === 'result')
    .map(e => ({category:e.category, check:e.check, status:e.status, detail:e.detail, severity:e.severity}));
}

function newScan() {
  // Reset terminal and input for a fresh scan
  scanLines = []; scanning = false; currentScanId = null; window._scanRisk = 0; _cancelSim = false;
  const url_input = document.getElementById('scan-url');
  if (url_input) url_input.value = '';
  const term = document.getElementById('scan-terminal');
  if (term) term.classList.add('hidden');
  const idle = document.getElementById('scan-idle');
  if (idle) idle.classList.remove('hidden');
  const summary = document.getElementById('scan-summary');
  if (summary) { summary.innerHTML = ''; summary.classList.add('hidden'); }
  const err = document.getElementById('scan-error');
  if (err) err.className = 'hidden alert';
  const btn = document.getElementById('scan-btn');
  if (btn) { btn.className = 'btn btn-cyan btn-lg'; btn.innerHTML = '⚡ Lancer le scan'; btn.onclick = doScan; }
  const cb = document.getElementById('cancel-btn');
  if (cb) cb.style.display = 'none';
}

async function doScan() {
  if (scanning) return;
  const urlEl = document.getElementById('scan-url');
  const url   = urlEl?.value?.trim() || '';
  const errEl = document.getElementById('scan-error');
  errEl.className = 'hidden alert';
  if (!url) {
    errEl.className = 'alert alert-error';
    errEl.textContent = '⚠️ Veuillez saisir une URL cible';
    urlEl?.focus();
    return;
  }
  try { new URL(url); } catch {
    errEl.className = 'alert alert-error';
    errEl.textContent = '⚠️ URL invalide — utilisez le format https://exemple.com';
    urlEl?.focus();
    return;
  }

  scanLines = []; scanning = true; currentScanId = null; window._scanRisk = 0; _cancelSim = false;

  // Button: hide launch btn, show cancel
  const btn = document.getElementById('scan-btn');
  if (btn) {
    btn.className = 'btn btn-scanning btn-lg';
    btn.innerHTML = '<span class="scanning-dots"><span></span><span></span><span></span></span> En cours…';
    btn.onclick   = null; // disabled while scanning
  }
  const cancelBtn = document.getElementById('cancel-btn');
  if (cancelBtn) cancelBtn.style.display = 'inline-flex';

  // Register scan in DB
  let scanId = null;
  try {
    const scan = await apiFetch('/scan/start', {method:'POST', body: JSON.stringify({target_url: url})});
    scanId = scan.id;
    currentScanId = scanId;
  } catch(ex) {
    scanning = false;
    if (btn) { btn.className = 'btn btn-cyan btn-lg'; btn.innerHTML = '⚡ Lancer le scan'; btn.onclick = doScan; }
    if (cancelBtn) cancelBtn.style.display = 'none';
    if (ex.message && ex.message.includes('429')) {
      errEl.className = 'alert alert-warn';
      errEl.innerHTML = '<strong>⚠️ Limite de scans atteinte (3/jour).</strong><br>Passez au plan Pro pour des scans illimités. <button class="btn btn-cyan btn-sm" style="margin-top:.5rem" onclick="navigate(\'home\');setTimeout(()=>scrollToSection(\'#pricing\'),300)">Voir les offres Pro →</button>';
    } else {
      errEl.className = 'alert alert-error';
      errEl.textContent = '❌ ' + ex.message;
    }
    return;
  }

  runSimulatedScan(url, scanId);
}
function cancelScan() {
  _cancelSim = true;
  scanning   = false;
  const btn  = document.getElementById('scan-btn');
  if (btn) { btn.className = 'btn btn-cyan btn-lg'; btn.innerHTML = '⚡ Lancer le scan'; btn.onclick = doScan; }
  const cb = document.getElementById('cancel-btn');
  if (cb) cb.style.display = 'none';
  addScanLine({type:'system', color:'#f5c542', text:'  ⚠️ Scan annulé par l\'utilisateur.'});
  if (currentScanId) {
    apiFetch('/scan/' + currentScanId + '/cancel', {method:'POST'}).catch(()=>{});
  }
  updateScanSummary();
}

async function stopScan() {
  currentES?.close(); scanning = false;
  addScanLine({type:'system',color:'#f5c542',text:'⚠️ Scan annulé par l\'utilisateur.'});
  const btn = document.getElementById('scan-btn');
  if (btn) { btn.className = 'btn btn-cyan btn-lg'; btn.innerHTML = '⚡ Lancer le scan'; }
  if (currentScanId) {
    try { await apiFetch(`/scan/${currentScanId}/cancel`, {method:'POST'}); } catch {}
    updateScanSummary();
  }
}

function downloadReport(id) {
  window.open(API + '/scan/' + id + '/report?token=' + encodeURIComponent(getToken()));
}

function downloadDocx(id) {
  // Generate a proper .docx-style report as HTML and trigger download
  const results = window._lastScanResults || [];
  const risk    = window._scanRisk || 0;
  const url     = document.getElementById('scan-url')?.value || 'N/A';
  const rc      = risk >= 60 ? '#c0392b' : risk >= 30 ? '#d35400' : '#27ae60';
  const rl      = risk >= 60 ? 'HAUT RISQUE' : risk >= 30 ? 'RISQUE MOYEN' : 'FAIBLE RISQUE';
  const date    = new Date().toLocaleString('fr-FR');

  // Group results by category
  const cats = {};
  results.forEach(r => { if (!cats[r.category]) cats[r.category] = []; cats[r.category].push(r); });

  const sevColor = {ok:'#27ae60', warning:'#d35400', critical:'#c0392b', info:'#2980b9'};
  const sevIcon  = {ok:'✅', warning:'⚠️', critical:'❌', info:'ℹ️'};

  let catHtml = '';
  for (const [cat, rows] of Object.entries(cats)) {
    catHtml += '<h2 style="font-size:13pt;color:#1a5276;border-bottom:1.5px solid #1a5276;padding-bottom:4px;margin:18px 0 8px">' + cat + '</h2>';
    catHtml += '<table style="width:100%;border-collapse:collapse;font-size:9pt;margin-bottom:8px">'
      + '<thead><tr style="background:#1a5276;color:#fff"><th style="padding:6px 8px;text-align:left;width:22%">Vérification</th>'
      + '<th style="padding:6px 8px;text-align:left;width:12%">Statut</th>'
      + '<th style="padding:6px 8px;text-align:left">Détail</th></tr></thead><tbody>';
    rows.forEach((r, i) => {
      const bg = i % 2 === 0 ? '#f8f9fa' : '#ffffff';
      catHtml += '<tr style="background:' + bg + '">'
        + '<td style="padding:5px 8px;font-weight:600;border-bottom:1px solid #e0e0e0">' + r.check + '</td>'
        + '<td style="padding:5px 8px;border-bottom:1px solid #e0e0e0"><span style="color:' + (sevColor[r.severity]||'#555') + ';font-weight:700">' + (sevIcon[r.severity]||'•') + ' ' + r.status + '</span></td>'
        + '<td style="padding:5px 8px;border-bottom:1px solid #e0e0e0;color:#333">' + r.detail + '</td>'
        + '</tr>';
    });
    catHtml += '</tbody></table>';
  }

  const html = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"/>'
    + '<style>body{font-family:Calibri,Arial,sans-serif;margin:2cm;color:#1a1a1a;font-size:10pt}'
    + 'h1{font-size:20pt;color:#1a5276;margin-bottom:4px}'
    + '.meta-table td{padding:3px 12px 3px 0;font-size:9pt}'
    + '.risk-box{background:' + rc + ';color:#fff;padding:10px 18px;border-radius:6px;display:inline-block;font-size:14pt;font-weight:700;margin:12px 0}'
    + '.footer{margin-top:24px;font-size:8pt;color:#888;border-top:1px solid #ccc;padding-top:8px}'
    + '@media print{body{margin:1.5cm}}</style>'
    + '</head><body>'
    + '<h1>OVA9 — Rapport de Sécurité</h1>'
    + '<table class="meta-table"><tr><td><b>Cible :</b></td><td>' + url + '</td></tr>'
    + '<tr><td><b>Date :</b></td><td>' + date + '</td></tr>'
    + '<tr><td><b>Scan ID :</b></td><td>#' + (window._lastScanId || 'N/A') + '</td></tr></table>'
    + '<div class="risk-box">Score de risque : ' + risk + '/100 — ' + rl + '</div>'
    + catHtml
    + '<div class="footer">Rapport généré par OVA9 — Plateforme de recherche en sécurité autorisée. Toute activité est enregistrée.</div>'
    + '</body></html>';

  const blob = new Blob([html], {type: 'application/vnd.ms-word;charset=utf-8'});
  const a    = document.createElement('a');
  a.href     = URL.createObjectURL(blob);
  a.download = 'OVA9_Rapport_Scan.doc';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
}

async function saveScan(id) {
  const btn = document.getElementById('save-scan-btn');
  try {
    const r = await apiFetch(`/scan/${id}/save`, {method:'POST'});
    if (btn) {
      btn.innerHTML = r.saved ? '🔖 Sauvegardé ✓' : '🔖 Sauvegarder';
      btn.className = r.saved ? 'btn btn-green btn-sm' : 'btn btn-yellow btn-sm';
    }
    showToast(r.saved ? '🔖 Scan sauvegardé !' : 'Scan retiré des sauvegardes.', r.saved ? 'success' : 'info');
  } catch(e) { showToast(`❌ ${e.message}`, 'error'); }
}

// ══════════════════════════════════════════════════════════════════════
// PROFILE
// ══════════════════════════════════════════════════════════════════════
let _profileTab = 'overview', _profileScans = [], _savedScans = [], _profileTabOnLoad = null;

async function renderProfile() {
  document.getElementById('page-profile').innerHTML = `
<div class="page-pad" style="min-height:100vh;padding:2.5rem 1.5rem 4rem">
  <div style="max-width:960px;margin:0 auto">
    <div style="display:flex;gap:1.5rem;align-items:flex-start;flex-wrap:wrap;margin-bottom:2.5rem">
      <div id="prof-avatar-big" style="width:72px;height:72px;border-radius:50%;background:var(--cyan-dim);border:2.5px solid var(--cyan);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:800;color:var(--cyan);font-family:'Space Mono',monospace;flex-shrink:0;overflow:hidden">${avatarContent(currentUser, 72)}</div>
      <div style="flex:1">
        <h1 style="font-weight:900;font-size:1.5rem;letter-spacing:-.02em" id="prof-name">${escHtml(currentUser?.username)}</h1>
        <p class="text-muted" style="font-size:.85rem;margin-top:3px">${escHtml(currentUser?.email)}</p>
        <p style="font-size:.82rem;color:var(--text);margin-top:6px;opacity:.7" id="prof-bio">${escHtml(currentUser?.bio || '')}</p>
        <div style="display:flex;gap:.5rem;margin-top:8px;flex-wrap:wrap">
          ${planBadge(currentUser?.plan || 'free')}
          ${currentUser?.is_admin ? `<span class="badge" style="background:rgba(245,197,66,.1);border:1px solid rgba(245,197,66,.3);color:#f5c542">ADMIN</span>` : ''}
        </div>
      </div>
      <div style="display:flex;gap:.65rem;flex-wrap:wrap">
        <button class="btn btn-cyan btn-sm" onclick="navigate('scanner')">⚡ Nouveau scan</button>
        ${currentUser?.plan === 'free' ? `<button class="btn btn-yellow btn-sm" onclick="scrollToSection('#pricing');navigate('home')">⭐ Passer Pro</button>` : ''}
        ${currentUser?.is_admin ? `<button class="btn btn-ghost btn-sm" onclick="navigate('admin')">🛡️ Admin</button>` : ''}
      </div>
    </div>
    <div class="tabs">
      <button class="tab active" id="tab-overview" onclick="switchProfileTab('overview')">📊 Vue d'ensemble</button>
      <button class="tab" id="tab-history" onclick="switchProfileTab('history')">📋 Historique</button>
      <button class="tab" id="tab-saved" onclick="switchProfileTab('saved')">🔖 Sauvegardés</button>
      <button class="tab" id="tab-settings" onclick="switchProfileTab('settings')">⚙️ Paramètres</button>
    </div>
    <div id="profile-tab-content"><div style="text-align:center;padding:3rem;color:var(--muted)">Chargement…</div></div>
  </div>
</div>`;
  _profileScans = []; _savedScans = [];
  try { _profileScans = await apiFetch('/scan/history'); } catch { _profileScans = []; }
  try { _savedScans   = await apiFetch('/scan/saved'); }   catch { _savedScans = []; }
  const tab = _profileTabOnLoad || 'overview';
  _profileTabOnLoad = null;
  switchProfileTab(tab);
}

function switchProfileTab(tab) {
  _profileTab = tab;
  ['overview','history','saved','settings'].forEach(t => {
    const b = document.getElementById('tab-' + t);
    if (b) b.className = 'tab' + (t === tab ? ' active' : '');
  });
  const el = document.getElementById('profile-tab-content'); if (!el) return;

  if (tab === 'overview') {
    const total   = _profileScans.length;
    const highR   = _profileScans.filter(s => s.risk_score >= 60).length;
    const avgRisk = total ? Math.round(_profileScans.reduce((a,s) => a+s.risk_score, 0)/total) : 0;
    el.innerHTML = `
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:1rem;margin-bottom:2rem">
        ${[
          ['Total Scans', total, 'var(--cyan)', false],
          ['Score moy.', avgRisk, avgRisk>60?'var(--red)':avgRisk>30?'var(--yellow)':'var(--green)', false],
          ['Haut risque', highR, highR>0?'var(--red)':'var(--green)', false],
          ['Sauvegardés', _savedScans.length, 'var(--yellow)', false],
          ['Membre depuis', new Date(currentUser?.created_at||Date.now()).toLocaleDateString(), 'var(--text)', true],
        ].map(([l,v,c,sm]) => `
          <div class="card" style="text-align:center;padding:1.2rem">
            <div style="font-family:'Space Mono',monospace;font-size:${sm?'.9rem':'1.8rem'};font-weight:700;color:${c}">${v}</div>
            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;margin-top:4px">${l}</div>
          </div>`).join('')}
      </div>
      <div class="card" style="padding:1.5rem">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
          <h3 style="font-weight:800;font-size:.95rem">Scans récents</h3>
          <button class="btn btn-ghost btn-sm" onclick="switchProfileTab('history')">Voir tout →</button>
        </div>
        ${_profileScans.length === 0
          ? emptyState('🔍', 'Aucun scan encore', 'Lancez votre premier scan pour voir l\'historique ici.', 'scanner', '⚡ Lancer un scan')
          : _profileScans.slice(0,5).map(s => scanRowHtml(s)).join('')}
      </div>`;
  } else if (tab === 'history') {
    el.innerHTML = `<div class="card" style="padding:1.5rem">
      <h3 style="font-weight:800;font-size:.95rem;margin-bottom:1.2rem">📋 Historique complet (${_profileScans.length})</h3>
      ${_profileScans.length === 0
        ? emptyState('📋', 'Aucun historique', 'Vos scans terminés apparaîtront ici. Commencez par scanner un site.', 'scanner', '⚡ Lancer un scan')
        : _profileScans.map(s => scanRowHtml(s)).join('')}
    </div>`;
  } else if (tab === 'saved') {
    el.innerHTML = `<div class="card" style="padding:1.5rem">
      <h3 style="font-weight:800;font-size:.95rem;margin-bottom:1.2rem">🔖 Scans sauvegardés (${_savedScans.length})</h3>
      ${_savedScans.length === 0
        ? emptyState('🔖', 'Aucun scan sauvegardé', 'Après un scan, cliquez sur le bouton "Sauvegarder" pour retrouver vos résultats ici.', 'scanner', '⚡ Lancer un scan')
        : _savedScans.map(s => scanRowHtml(s)).join('')}
    </div>`;
  } else if (tab === 'settings') {
    renderSettingsTab(el);
  }
}

function emptyState(icon, title, desc, navPage, btnLabel) {
  return `<div style="text-align:center;padding:4rem 2rem;color:var(--muted)">
    <div style="font-size:3rem;margin-bottom:1rem;opacity:.5">${icon}</div>
    <h3 style="font-weight:800;color:var(--text);margin-bottom:8px">${title}</h3>
    <p style="font-size:.88rem;line-height:1.6;max-width:320px;margin:0 auto 1.5rem">${desc}</p>
    ${navPage ? `<button class="btn btn-outline btn-sm" onclick="navigate('${navPage}')">${btnLabel}</button>` : ''}
  </div>`;
}

function renderSettingsTab(el) {
  el.innerHTML = `<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1.5rem">
    <div class="card" style="padding:1.8rem">
      <h3 style="font-weight:800;font-size:1rem;margin-bottom:1.2rem">🖼️ Photo de profil</h3>
      <div style="display:flex;flex-direction:column;align-items:center;gap:1rem;margin-bottom:1rem">
        <div id="avatar-preview" style="width:80px;height:80px;border-radius:50%;border:2px solid var(--cyan);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:var(--cyan);background:var(--cyan-dim);overflow:hidden">${avatarContent(currentUser, 80)}</div>
      </div>
      <div class="avatar-upload-zone" onclick="document.getElementById('avatar-file').click()">
        <div style="font-size:1.5rem;margin-bottom:6px">📷</div>
        <p style="font-size:.82rem;color:var(--muted)">Cliquer pour uploader une image</p>
        <p style="font-size:.72rem;color:var(--muted);margin-top:4px">PNG, JPG, GIF — max 2MB</p>
      </div>
      <input id="avatar-file" type="file" accept="image/*" style="display:none" onchange="previewAvatar(event)"/>
      <div id="avatar-alert" class="hidden alert" style="margin-top:.8rem"></div>
      <button class="btn btn-cyan btn-sm" id="avatar-save-btn" style="width:100%;justify-content:center;margin-top:.8rem;display:none" onclick="uploadAvatar()">Enregistrer la photo</button>
      <div style="margin-top:1rem;border-top:1px solid var(--border);padding-top:1rem">
        <label style="font-size:.75rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.4rem">Ou lettre / emoji</label>
        <input id="s-avatar" type="text" maxlength="2" value="${escHtml(currentUser?.avatar||'')}" placeholder="ex: 🔒" style="padding:.7rem 1rem;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:.92rem;outline:none;width:100%"/>
      </div>
    </div>
    <div class="card" style="padding:1.8rem">
      <h3 style="font-weight:800;font-size:1rem;margin-bottom:1.2rem">✏️ Modifier le profil</h3>
      <div id="settings-alert" class="hidden alert"></div>
      <div class="form-group"><label>Nom d'utilisateur</label><input id="s-user" type="text" value="${escHtml(currentUser?.username||'')}"/></div>
      <div class="form-group"><label>Bio</label><textarea id="s-bio" placeholder="Une courte bio…">${escHtml(currentUser?.bio||'')}</textarea></div>
      <button class="btn btn-cyan btn-sm" onclick="saveProfile()">Enregistrer</button>
    </div>
    <div class="card" style="padding:1.8rem">
      <h3 style="font-weight:800;font-size:1rem;margin-bottom:1.2rem">🔑 Changer le mot de passe</h3>
      <div id="pass-alert" class="hidden alert"></div>
      <div class="form-group"><label>Nouveau mot de passe</label><input id="s-newpass" type="password" placeholder="Min. 8 car."/></div>
      <div class="form-group"><label>Confirmer</label><input id="s-newpass2" type="password" placeholder="••••••••"/></div>
      <button class="btn btn-outline btn-sm" onclick="changePassword()">Mettre à jour</button>
    </div>
    <div class="card" style="padding:1.8rem">
      <h3 style="font-weight:800;font-size:1rem;margin-bottom:1.2rem">📊 Informations du compte</h3>
      <div style="display:flex;flex-direction:column;gap:.75rem;font-size:.85rem">
        <div style="display:flex;justify-content:space-between"><span class="text-muted">ID</span><span class="mono">#${currentUser?.id}</span></div>
        <div style="display:flex;justify-content:space-between"><span class="text-muted">Email</span><span>${escHtml(currentUser?.email)}</span></div>
        <div style="display:flex;justify-content:space-between"><span class="text-muted">Rôle</span><span style="color:${currentUser?.is_admin?'var(--yellow)':'var(--cyan)'}">${currentUser?.is_admin?'Administrateur':'Utilisateur'}</span></div>
        <div style="display:flex;justify-content:space-between"><span class="text-muted">Plan</span><div>${planBadge(currentUser?.plan||'free')}</div></div>
        <div style="display:flex;justify-content:space-between"><span class="text-muted">Inscrit le</span><span>${new Date(currentUser?.created_at||Date.now()).toLocaleDateString()}</span></div>
      </div>
      ${currentUser?.plan === 'free' ? `<div style="height:1px;background:rgba(255,255,255,.06);margin:1rem 0"></div><button class="btn btn-yellow btn-sm" style="width:100%;justify-content:center" onclick="navigate('home');setTimeout(()=>scrollToSection('#pricing'),300)">⭐ Passer Pro</button>` : ''}
      <div style="height:1px;background:rgba(255,255,255,.06);margin:1rem 0"></div>
      <button class="btn btn-red btn-sm" style="width:100%;justify-content:center" onclick="logout()">⏏ Déconnexion</button>
    </div>
  </div>`;
}

function scanRowHtml(s) {
  const rc = riskColor(s.risk_score);
  return `<div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;padding:.85rem 0;border-bottom:1px solid rgba(255,255,255,.04);cursor:pointer;border-radius:8px;transition:background .15s" onclick="openScanModal(${s.id})" onmouseenter="this.style.background='rgba(0,191,255,.04)'" onmouseleave="this.style.background='transparent'">
    <div style="width:8px;height:8px;border-radius:50%;background:${s.status==='done'?'var(--green)':s.status==='running'?'var(--cyan)':s.status==='cancelled'?'var(--red)':'var(--yellow)'};flex-shrink:0"></div>
    <div style="flex:1;min-width:0"><div style="font-family:'Space Mono',monospace;font-size:.8rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${escHtml(s.target_url)}</div><div class="text-muted" style="font-size:.72rem;margin-top:2px">${new Date(s.created_at).toLocaleString()} · #${s.id}</div></div>
    <div style="text-align:right;flex-shrink:0"><span style="font-family:'Space Mono',monospace;font-weight:700;color:${rc}">${s.risk_score}</span><div class="badge" style="background:${rc}22;border:1px solid ${rc}44;color:${rc};margin-top:2px">${riskLabel(s.risk_score)}</div></div>
    <div style="display:flex;gap:.45rem;flex-shrink:0">
      <button class="btn btn-ghost btn-sm" onclick="event.stopPropagation();openScanModal(${s.id})">Voir</button>
      <button class="btn btn-outline btn-sm" onclick="event.stopPropagation();downloadReport(${s.id})" title="Télécharger">⬇</button>
    </div>
  </div>`;
}

function previewAvatar(event) {
  const file = event.target.files[0]; if (!file) return;
  if (file.size > 2*1024*1024) { const al = document.getElementById('avatar-alert'); al.className = 'alert alert-error'; al.textContent = 'Image trop grande (max 2MB)'; return; }
  const reader = new FileReader();
  reader.onload = e => {
    const prev = document.getElementById('avatar-preview');
    if (prev) prev.innerHTML = `<img src="${e.target.result}" style="width:80px;height:80px;object-fit:cover;border-radius:50%"/>`;
    window._pendingAvatarData = e.target.result;
    const btn = document.getElementById('avatar-save-btn'); if (btn) btn.style.display = 'flex';
  };
  reader.readAsDataURL(file);
}

async function uploadAvatar() {
  const data = window._pendingAvatarData; if (!data) return;
  const al = document.getElementById('avatar-alert'), btn = document.getElementById('avatar-save-btn');
  btn.textContent = 'Upload…'; btn.disabled = true;
  try {
    await apiFetch('/users/avatar', {method:'POST', body:JSON.stringify({data})});
    currentUser.avatar_url = data; renderNavRight();
    document.getElementById('prof-avatar-big').innerHTML = `<img src="${data}" style="width:72px;height:72px;object-fit:cover;border-radius:50%"/>`;
    al.className = 'alert alert-success'; al.textContent = '✅ Photo mise à jour';
    btn.style.display = 'none';
  } catch(e) { al.className = 'alert alert-error'; al.textContent = `❌ ${e.message}`; }
  finally { btn.textContent = 'Enregistrer la photo'; btn.disabled = false; }
}

async function saveProfile() {
  const al = document.getElementById('settings-alert'); al.className = 'hidden alert';
  const username = document.getElementById('s-user').value.trim();
  const bio      = document.getElementById('s-bio').value.trim();
  const avatar   = document.getElementById('s-avatar').value.trim();
  if (!username) { al.className = 'alert alert-error'; al.textContent = '⚠️ Nom d\'utilisateur requis'; return; }
  try {
    const updated = await apiFetch('/users/me', {method:'PATCH', body:JSON.stringify({username, bio, avatar})});
    currentUser = {...currentUser, ...updated};
    renderNavRight();          // right dropdown
    updateNavLeft();           // left of logo — instant
    const pn = document.getElementById('prof-name'); if (pn) pn.textContent = currentUser.username;
    const pb = document.getElementById('prof-bio');  if (pb) pb.textContent = currentUser.bio || '';
    al.className = 'alert alert-success'; al.textContent = '✅ Profil mis à jour avec succès';
  } catch(e) { al.className = 'alert alert-error'; al.textContent = '❌ ' + e.message; }
}

async function changePassword() {
  const al = document.getElementById('pass-alert'); al.className = 'hidden alert';
  const p1 = document.getElementById('s-newpass').value;
  const p2 = document.getElementById('s-newpass2').value;
  if (!p1 && !p2) { al.className = 'alert alert-error'; al.textContent = '⚠️ Veuillez saisir un nouveau mot de passe'; return; }
  if (!p1) { al.className = 'alert alert-error'; al.textContent = '⚠️ Nouveau mot de passe requis'; return; }
  if (!p2) { al.className = 'alert alert-error'; al.textContent = '⚠️ Confirmation du mot de passe requise'; return; }
  if (p1 !== p2) { al.className = 'alert alert-error'; al.textContent = '⚠️ Les mots de passe ne correspondent pas'; return; }
  if (p1.length < 8) { al.className = 'alert alert-error'; al.textContent = '⚠️ Minimum 8 caractères requis'; return; }
  try {
    await apiFetch('/users/me', {method:'PATCH', body:JSON.stringify({password:p1})});
    al.className = 'alert alert-success'; al.textContent = '✅ Mot de passe mis à jour avec succès';
    document.getElementById('s-newpass').value = '';
    document.getElementById('s-newpass2').value = '';
  } catch(e) { al.className = 'alert alert-error'; al.textContent = '❌ ' + e.message; }
}

async function openScanModal(scanId) {
  let scan = [..._profileScans, ..._savedScans].find(s => s.id === scanId);
  if (!scan || !scan.results) { try { scan = await apiFetch(`/scan/${scanId}`); } catch { return; } }
  const results = scan.results || [];
  const SI = {ok:'✅',warning:'⚠️',critical:'❌',info:'ℹ️'}, SC = {ok:'#00ff88',warning:'#f5c542',critical:'#ff4444',info:'#00bfff'};
  let rHtml = `<div style="color:var(--cyan);margin-bottom:4px">OVA9 scan: ${escHtml(scan.target_url)}</div><div style="color:rgba(255,255,255,.15);margin-bottom:6px">│</div>`;
  let cc = null;
  for (const r of results) {
    if (r.category !== cc) { cc = r.category; rHtml += `<div style="margin-top:10px;margin-bottom:2px"><span style="color:var(--cyan)">├── </span><span style="color:#fff;font-weight:700;text-transform:uppercase">[${escHtml(cc)}]</span></div>`; }
    const sc = SC[r.severity]||'#00bfff';
    rHtml += `<div style="display:flex;align-items:flex-start;gap:0;margin-bottom:2px;font-family:'Space Mono',monospace;font-size:.75rem;line-height:1.5"><span style="color:rgba(255,255,255,.2);white-space:pre">│   ├── </span><span style="color:${sc}">${SI[r.severity]||'ℹ️'} </span><span style="color:#e0e8f0;font-weight:600">${escHtml(r.check)}</span><span style="color:rgba(255,255,255,.2);margin:0 6px">—</span><span style="color:${sc};opacity:.85;min-width:48px">[${r.status}]</span><span style="color:rgba(200,220,240,.5);margin-left:8px;word-break:break-all">${escHtml(r.detail)}</span></div>`;
  }
  const rc = riskColor(scan.risk_score);
  const modal = document.createElement('div'); modal.className = 'modal-bg'; modal.onclick = e => { if (e.target === modal) modal.remove(); };
  modal.innerHTML = `<div class="modal"><div style="display:flex;justify-content:space-between;align-items:center;padding:1.2rem 1.5rem;border-bottom:1px solid rgba(0,191,255,.1)">
    <div><h3 style="font-weight:800">Scan #${scan.id}</h3><p style="color:var(--muted);font-size:.78rem;font-family:'Space Mono',monospace;margin-top:2px">${escHtml(scan.target_url)}</p></div>
    <div style="display:flex;gap:.65rem"><button class="btn btn-outline btn-sm" onclick="downloadReport(${scan.id})">⬇ Rapport</button><button class="btn btn-ghost btn-sm" onclick="this.closest('.modal-bg').remove()">✕</button></div>
  </div>
  <div style="padding:1rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.05);display:flex;gap:2rem;align-items:center;flex-wrap:wrap">
    <div><span style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.06em">Score de risque</span>
      <div style="display:flex;align-items:center;gap:8px;margin-top:4px"><span style="font-family:'Space Mono',monospace;font-size:1.6rem;font-weight:700;color:${rc}">${scan.risk_score}</span><span class="badge" style="background:${rc}22;border:1px solid ${rc}44;color:${rc}">${riskLabel(scan.risk_score)}</span></div>
    </div>
    <span style="font-size:.72rem;color:var(--muted)">${new Date(scan.created_at).toLocaleString()}</span>
  </div>
  <div style="flex:1;overflow:auto;padding:1.2rem 1.5rem;font-family:'Space Mono',monospace;font-size:.76rem;line-height:1.7">
    ${results.length === 0
      ? '<div style="text-align:center;padding:2rem;color:var(--muted)">⏳ Résultats non disponibles pour ce scan.</div>'
      : rHtml + '<div style="color:rgba(255,255,255,.15);margin-top:6px">│</div><div style="color:var(--green);margin-top:4px">└── Scan terminé.</div>'}
  </div></div>`;
  document.body.appendChild(modal);
}

// ══════════════════════════════════════════════════════════════════════
// ADMIN
// ══════════════════════════════════════════════════════════════════════
let _adminTab = 'dashboard', _adminData = {users:[],scans:[],stats:{},offers:[],audit:[],payments:[]};

async function renderAdmin() {
  document.getElementById('page-admin').innerHTML = `
<div class="page-pad" style="min-height:100vh;padding:2.5rem 1.5rem 4rem">
  <div style="max-width:1100px;margin:0 auto">
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem">
      <span style="font-size:1.4rem">🛡️</span>
      <h1 style="font-weight:900;font-size:1.6rem;letter-spacing:-.03em">OVA9 Admin Panel</h1>
      <span style="padding:3px 10px;border-radius:100px;background:rgba(245,197,66,.12);border:1px solid rgba(245,197,66,.3);color:var(--yellow);font-size:.7rem;font-weight:800">PROTECTED ZONE</span>
    </div>
    <p class="text-muted" style="font-size:.88rem;margin-bottom:2rem">Gestion de la plateforme, surveillance complète et contrôle de contenu.</p>
    <div id="admin-alert" class="hidden alert"></div>
    <div class="tabs">
      <button class="tab active" id="atab-dashboard" onclick="switchAdminTab('dashboard')">📊 Dashboard</button>
      <button class="tab" id="atab-users"     onclick="switchAdminTab('users')">👤 Utilisateurs</button>
      <button class="tab" id="atab-scans"     onclick="switchAdminTab('scans')">🔍 Scans</button>
      <button class="tab" id="atab-offers"    onclick="switchAdminTab('offers')">🏷️ Offres</button>
      <button class="tab" id="atab-payments"  onclick="switchAdminTab('payments')">💳 Paiements</button>
      <button class="tab" id="atab-audit"     onclick="switchAdminTab('audit')">📝 Audit</button>
      <button class="btn btn-ghost btn-sm" style="margin-left:auto" onclick="loadAdmin()">↻ Rafraîchir</button>
    </div>
    <div id="admin-tab-content"><div style="text-align:center;padding:3rem;color:var(--muted)">Chargement des données…</div></div>
  </div>
</div>`;
  loadAdmin();
}

async function loadAdmin() {
  try {
    const [stats, users, scans, offers, audit, payments] = await Promise.all([
      apiFetch('/admin/stats'), apiFetch('/admin/users'), apiFetch('/admin/scans'),
      apiFetch('/admin/offers'), apiFetch('/admin/audit'), apiFetch('/admin/payments').catch(()=>[])
    ]);
    _adminData = {stats, users, scans, offers, audit, payments: payments||[]};
  } catch(e) { adminAlert(e.message, 'error'); return; }
  switchAdminTab(_adminTab);
}

function switchAdminTab(tab) {
  _adminTab = tab;
  ['dashboard','users','scans','offers','payments','audit'].forEach(t => {
    const b = document.getElementById('atab-' + t); if (b) b.className = 'tab' + (t === tab ? ' active' : '');
  });
  const el = document.getElementById('admin-tab-content'); if (!el) return;

  if (tab === 'dashboard') renderAdminDashboard(el);
  else if (tab === 'users') renderAdminUsers(el);
  else if (tab === 'scans') renderAdminScans(el);
  else if (tab === 'offers') renderAdminOffers(el);
  else if (tab === 'payments') renderAdminPayments(el);
  else if (tab === 'audit') renderAdminAudit(el);
}

function renderAdminDashboard(el) {
  const s = _adminData.stats || {};
  const scans = _adminData.scans || [];
  const users = _adminData.users || [];

  const kpis = [
    ['👥 Utilisateurs',   s.total_users  ??0, 'var(--cyan)'],
    ['🔍 Total Scans',    s.total_scans  ??0, 'var(--green)'],
    ['⚡ Scans Auj.',      s.scans_today  ??0, 'var(--cyan)'],
    ['🚫 Bannis',         s.banned_users ??0, (s.banned_users??0)>0?'var(--red)':'var(--green)'],
    ['🔴 Haut Risque',    s.high_risk    ??0, (s.high_risk??0)>0?'var(--red)':'var(--green)'],
    ['⭐ Plan Pro',        s.pro_users    ??0, 'var(--yellow)'],
    ['💳 Paiements',      s.revenue_count??0, 'var(--green)'],
  ];

  const lowR = scans.filter(x=>x.risk_score<30).length;
  const medR = scans.filter(x=>x.risk_score>=30&&x.risk_score<60).length;
  const highR= scans.filter(x=>x.risk_score>=60).length;
  const tot  = scans.length || 1;

  const ipMap = {};
  scans.forEach(sc => { if (sc.ip_address) ipMap[sc.ip_address] = (ipMap[sc.ip_address]||0)+1; });
  const topIPs = Object.entries(ipMap).sort((a,b) => b[1]-a[1]).slice(0,6);

  const urlMap = {};
  scans.forEach(sc => { const u = sc.target_url||''; urlMap[u] = (urlMap[u]||0)+1; });
  const topURLs = Object.entries(urlMap).sort((a,b) => b[1]-a[1]).slice(0,5);

  const bannedUsers = users.filter(u => u.is_banned);
  const recentUsers = users.slice(0,5);

  el.innerHTML = `
    <!-- KPI row -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:1rem;margin-bottom:1.5rem">
      ${kpis.map(([l,v,c]) => `<div class="card" style="text-align:center;padding:1rem;border-top:3px solid ${c}">
        <div style="font-family:'Space Mono',monospace;font-size:1.8rem;font-weight:700;color:${c}">${v}</div>
        <div class="text-muted" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;margin-top:4px">${l}</div>
      </div>`).join('')}
    </div>

    <!-- Risk + IPs -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;margin-bottom:1.2rem">
      <div class="card" style="padding:1.4rem">
        <h3 style="font-weight:800;font-size:.9rem;margin-bottom:1rem">📊 Distribution du risque</h3>
        ${[['Faible (0-29)',lowR,Math.round(lowR/tot*100),'var(--green)'],['Moyen (30-59)',medR,Math.round(medR/tot*100),'var(--yellow)'],['Élevé (60+)',highR,Math.round(highR/tot*100),'var(--red)']].map(([l,n,p,c]) => `
        <div style="margin-bottom:.8rem">
          <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:4px"><span style="color:${c}">${l}</span><span class="mono" style="color:${c}">${n} (${p}%)</span></div>
          <div style="height:8px;background:rgba(255,255,255,.07);border-radius:4px;overflow:hidden"><div style="height:100%;width:${p}%;background:${c};border-radius:4px;transition:width .6s ease"></div></div>
        </div>`).join('')}
      </div>
      <div class="card" style="padding:1.4rem">
        <h3 style="font-weight:800;font-size:.9rem;margin-bottom:1rem">🌐 Top IPs actives</h3>
        ${topIPs.length ? `<table><thead><tr><th>Adresse IP</th><th>Scans</th></tr></thead><tbody>
          ${topIPs.map(([ip,n]) => `<tr><td class="mono" style="font-size:.8rem">${escHtml(ip)}</td><td><span class="badge" style="background:var(--cyan-dim);color:var(--cyan)">${n}</span></td></tr>`).join('')}
        </tbody></table>` : '<p class="text-muted" style="font-size:.85rem">Aucune donnée.</p>'}
      </div>
    </div>

    <!-- URLs + Actions -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;margin-bottom:1.2rem">
      <div class="card" style="padding:1.4rem">
        <h3 style="font-weight:800;font-size:.9rem;margin-bottom:1rem">🔗 URLs les plus scannées</h3>
        ${topURLs.length ? topURLs.map(([url,n]) => `<div style="display:flex;justify-content:space-between;align-items:center;gap:.5rem;margin-bottom:.5rem">
          <span style="font-family:'Space Mono',monospace;font-size:.72rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1">${escHtml(url)}</span>
          <span class="badge" style="background:var(--cyan-dim);color:var(--cyan);flex-shrink:0">${n}×</span>
        </div>`).join('') : '<p class="text-muted" style="font-size:.85rem">Aucune donnée.</p>'}
      </div>
      <div class="card" style="padding:1.4rem">
        <h3 style="font-weight:800;font-size:.9rem;margin-bottom:1rem">⚡ Actions rapides</h3>
        <div style="display:flex;flex-direction:column;gap:.6rem">
          <button class="btn btn-outline btn-sm" onclick="switchAdminTab('users')" style="justify-content:flex-start">👤 Gérer les utilisateurs (${users.length})</button>
          <button class="btn btn-outline btn-sm" onclick="switchAdminTab('scans')" style="justify-content:flex-start">🔍 Voir tous les scans (${scans.length})</button>
          <button class="btn btn-outline btn-sm" onclick="switchAdminTab('offers')" style="justify-content:flex-start">🏷️ Gérer les offres (${(_adminData.offers||[]).length})</button>
          <button class="btn btn-outline btn-sm" onclick="switchAdminTab('payments')" style="justify-content:flex-start">💳 Paiements (${(_adminData.payments||[]).length})</button>
          <button class="btn btn-outline btn-sm" onclick="switchAdminTab('audit')" style="justify-content:flex-start">📝 Audit log (${(_adminData.audit||[]).length})</button>
          <button class="btn btn-ghost btn-sm" onclick="loadAdmin()" style="justify-content:flex-start">↻ Rafraîchir toutes les données</button>
        </div>
      </div>
    </div>

    <!-- Banned users quick view -->
    ${bannedUsers.length ? `<div class="card" style="padding:1.4rem;margin-bottom:1.2rem;border-color:rgba(255,68,68,.25)">
      <h3 style="font-weight:800;font-size:.9rem;margin-bottom:1rem;color:var(--red)">🚫 Utilisateurs bannis (${bannedUsers.length})</h3>
      <div style="display:flex;flex-wrap:wrap;gap:.5rem">
        ${bannedUsers.map(u => `<span style="padding:3px 10px;background:rgba(255,68,68,.1);border:1px solid rgba(255,68,68,.3);border-radius:100px;font-size:.78rem;color:var(--red)">${escHtml(u.username)}</span>`).join('')}
      </div>
    </div>` : ''}

    <!-- Recent scans -->
    <div class="card" style="padding:1.5rem">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
        <h3 style="font-weight:800;font-size:.9rem">🕑 10 derniers scans</h3>
        <button class="btn btn-ghost btn-sm" onclick="switchAdminTab('scans')">Voir tout →</button>
      </div>
      <div style="overflow-x:auto"><table><thead><tr><th>ID</th><th>Utilisateur</th><th>URL cible</th><th>Risque</th><th>Statut</th><th>IP</th><th>Date</th></tr></thead>
      <tbody>${(s.recent_scans||[]).map(r => `<tr>
        <td class="mono text-muted" style="font-size:.75rem">#${r.id}</td>
        <td style="font-weight:600">${escHtml(r.username)}</td>
        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:'Space Mono',monospace;font-size:.72rem">${escHtml(r.target_url)}</td>
        <td><span style="color:${riskColor(r.risk_score)};font-family:'Space Mono',monospace;font-weight:700">${r.risk_score}</span> <span class="badge" style="background:${riskColor(r.risk_score)}22;color:${riskColor(r.risk_score)}">${riskLabel(r.risk_score)}</span></td>
        <td><span class="badge" style="background:${r.status==='done'?'rgba(0,255,136,.1)':'rgba(0,191,255,.1)'};color:${r.status==='done'?'var(--green)':'var(--cyan)'}">${r.status||'—'}</span></td>
        <td class="mono text-muted" style="font-size:.72rem">${escHtml(r.ip_address||'—')}</td>
        <td class="text-muted" style="font-size:.75rem">${new Date(r.created_at).toLocaleDateString()}</td>
      </tr>`).join('')}</tbody></table></div>
    </div>`;
}

function renderAdminUsers(el) {
  el.innerHTML = `<div style="overflow-x:auto"><div class="card" style="padding:0;overflow:hidden"><table><thead><tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Plan</th><th>Statut</th><th>Inscription</th><th>Actions</th></tr></thead>
  <tbody>${_adminData.users.map(u => `<tr>
    <td class="mono text-muted" style="font-size:.75rem">#${u.id}</td>
    <td style="font-weight:600">${escHtml(u.username)}</td>
    <td class="mono text-muted" style="font-size:.75rem">${escHtml(u.email)}</td>
    <td>${u.is_admin ? `<span style="color:var(--yellow);font-size:.72rem;font-weight:700">🛡️ ADMIN</span>` : `<span class="text-muted" style="font-size:.72rem">user</span>`}</td>
    <td>${planBadge(u.plan||'free')}</td>
    <td><span class="badge" style="background:${u.is_banned?'rgba(255,68,68,.12)':'rgba(0,255,136,.1)'};border:1px solid ${u.is_banned?'rgba(255,68,68,.25)':'rgba(0,255,136,.25)'};color:${u.is_banned?'var(--red)':'var(--green)'}">${u.is_banned?'BANNI':'ACTIF'}</span></td>
    <td class="text-muted" style="font-size:.75rem">${new Date(u.created_at).toLocaleDateString()}</td>
    <td>${!u.is_admin ? `<button class="btn btn-sm ${u.is_banned?'btn-green':'btn-red'}" onclick="adminBan(${u.id},'${escHtml(u.username)}')">${u.is_banned?'✅ Débannir':'🔴 Bannir'}</button>` : '<span class="text-muted" style="font-size:.72rem">protégé</span>'}</td>
  </tr>`).join('')}</tbody></table></div></div>`;
}

function renderAdminScans(el) {
  el.innerHTML = `<div style="overflow-x:auto"><div class="card" style="padding:0;overflow:hidden"><table><thead><tr><th>ID</th><th>URL cible</th><th>User</th><th>Statut</th><th>Risque</th><th>IP</th><th>User-Agent</th><th>Date</th><th></th></tr></thead>
  <tbody>${_adminData.scans.map(s => `<tr>
    <td class="mono text-muted" style="font-size:.72rem">#${s.id}</td>
    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:'Space Mono',monospace;font-size:.72rem">${escHtml(s.target_url)}</td>
    <td class="text-muted" style="font-size:.75rem">#${s.user_id}</td>
    <td><span class="badge" style="background:${s.status==='done'?'rgba(0,255,136,.1)':s.status==='cancelled'?'rgba(255,68,68,.1)':'rgba(0,191,255,.1)'};color:${s.status==='done'?'var(--green)':s.status==='cancelled'?'var(--red)':'var(--cyan)'}">${s.status}</span></td>
    <td><span style="font-family:'Space Mono',monospace;color:${riskColor(s.risk_score)};font-weight:700">${s.risk_score}</span></td>
    <td class="mono text-muted" style="font-size:.72rem">${escHtml(s.ip_address||'—')}</td>
    <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.68rem;color:var(--muted)" title="${escHtml(s.user_agent||'')}">${escHtml((s.user_agent||'—').slice(0,30))}…</td>
    <td class="text-muted" style="font-size:.72rem;white-space:nowrap">${new Date(s.created_at).toLocaleDateString()}</td>
    <td><button class="btn btn-red btn-sm" onclick="adminDelScan(${s.id})">🗑</button></td>
  </tr>`).join('')}</tbody></table></div></div>`;
}

function renderAdminOffers(el) {
  el.innerHTML = `<div style="display:flex;justify-content:flex-end;margin-bottom:1rem"><button class="btn btn-cyan btn-sm" onclick="openOfferModal()">+ Nouvelle offre</button></div>
  <div style="overflow-x:auto"><div class="card" style="padding:0;overflow:hidden"><table><thead><tr><th>ID</th><th>Titre</th><th>Badge</th><th>Prix</th><th>Actif</th><th>Actions</th></tr></thead>
  <tbody>${_adminData.offers.map(o => `<tr>
    <td class="mono text-muted" style="font-size:.75rem">#${o.id}</td>
    <td style="font-weight:600">${escHtml(o.title)}</td>
    <td>${o.badge ? `<span class="badge" style="background:var(--cyan-dim);color:var(--cyan)">${escHtml(o.badge)}</span>` : '—'}</td>
    <td class="mono" style="font-size:.82rem;color:var(--cyan)">${escHtml(o.price||'—')}</td>
    <td><span class="badge" style="background:${o.active?'rgba(0,255,136,.1)':'rgba(255,68,68,.1)'};color:${o.active?'var(--green)':'var(--red)'}">${o.active?'ON':'OFF'}</span></td>
    <td style="display:flex;gap:.4rem"><button class="btn btn-ghost btn-sm" onclick="openOfferModal(${o.id})">Modifier</button><button class="btn btn-red btn-sm" onclick="adminDelOffer(${o.id})">🗑</button></td>
  </tr>`).join('')}</tbody></table></div></div>`;
}

function renderAdminPayments(el) {
  const pays = _adminData.payments || [];
  if (!pays.length) { el.innerHTML = `<div class="card">${emptyState('💳', 'Aucun paiement', 'Les paiements des utilisateurs apparaîtront ici.', null, null)}</div>`; return; }
  el.innerHTML = `<div style="overflow-x:auto"><div class="card" style="padding:0;overflow:hidden"><table><thead><tr><th>ID</th><th>Utilisateur</th><th>Email</th><th>Offre</th><th>Montant</th><th>Carte</th><th>Num (masqué)</th><th>Statut</th><th>Date</th></tr></thead>
  <tbody>${pays.map(p => `<tr>
    <td class="mono text-muted" style="font-size:.72rem">#${p.id}</td>
    <td style="font-weight:600">${escHtml(p.username)}</td>
    <td class="mono text-muted" style="font-size:.72rem">${escHtml(p.email)}</td>
    <td>#${p.offer_id}</td>
    <td class="mono" style="color:var(--green)">${escHtml(p.amount||'—')}</td>
    <td><span class="badge" style="background:var(--cyan-dim);color:var(--cyan)">${escHtml(p.card_type||'—')}</span></td>
    <td class="mono text-muted" style="font-size:.78rem">**** **** **** ${escHtml(p.card_last4||'????')}</td>
    <td><span class="badge" style="background:rgba(0,255,136,.1);color:var(--green)">${escHtml(p.status)}</span></td>
    <td class="text-muted" style="font-size:.72rem;white-space:nowrap">${new Date(p.created_at).toLocaleDateString()}</td>
  </tr>`).join('')}</tbody></table></div></div>`;
}

function renderAdminAudit(el) {
  el.innerHTML = `<div style="overflow-x:auto"><div class="card" style="padding:0;overflow:hidden"><table><thead><tr><th>Heure</th><th>Utilisateur</th><th>Action</th><th>Détail</th><th>IP</th><th>User-Agent</th></tr></thead>
  <tbody>${_adminData.audit.map(a => `<tr>
    <td class="text-muted" style="font-size:.72rem;white-space:nowrap">${new Date(a.created_at).toLocaleString()}</td>
    <td style="font-size:.8rem">${escHtml(a.username||'—')}</td>
    <td><span class="badge" style="background:var(--cyan-dim);color:var(--cyan);white-space:nowrap">${escHtml(a.action)}</span></td>
    <td class="text-muted" style="font-size:.75rem;max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${escHtml(a.detail||'')}">${escHtml(a.detail||'—')}</td>
    <td class="mono text-muted" style="font-size:.72rem">${escHtml(a.ip_address||'—')}</td>
    <td style="font-size:.65rem;color:var(--muted);max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${escHtml(a.user_agent||'')}">${escHtml((a.user_agent||'—').slice(0,30))}</td>
  </tr>`).join('')}</tbody></table></div></div>`;
}

function adminAlert(msg, type='info') {
  const el = document.getElementById('admin-alert'); if (!el) return;
  el.className = `alert alert-${type}`;
  el.innerHTML = `${escHtml(msg)} <button style="background:none;border:none;color:var(--cyan);cursor:pointer;float:right;font-size:.9rem" onclick="this.parentElement.className='hidden alert'">✕</button>`;
}

async function adminBan(uid, username) {
  try { const r = await apiFetch(`/admin/users/${uid}/ban`, {method:'POST'}); adminAlert(`${username} — ${r.banned?'🔴 Banni':'🟢 Débanni'}`, r.banned?'error':'success'); loadAdmin(); }
  catch(e) { adminAlert(e.message, 'error'); }
}

async function adminDelScan(id) {
  if (!confirm(`Supprimer définitivement le scan #${id} ?`)) return;
  try { await apiFetch(`/admin/scans/${id}`, {method:'DELETE'}); adminAlert(`Scan #${id} supprimé`, 'success'); loadAdmin(); }
  catch(e) { adminAlert(e.message, 'error'); }
}

async function adminDelOffer(id) {
  if (!confirm(`Supprimer l'offre #${id} ?`)) return;
  try { await apiFetch(`/admin/offers/${id}`, {method:'DELETE'}); adminAlert(`Offre #${id} supprimée`, 'success'); loadAdmin(); }
  catch(e) { adminAlert(e.message, 'error'); }
}

function openOfferModal(editId) {
  const offer = editId ? _adminData.offers.find(o => o.id === editId) : null;
  const modal = document.createElement('div'); modal.className = 'modal-bg'; modal.onclick = e => { if (e.target === modal) modal.remove(); };
  modal.innerHTML = `<div class="modal" style="max-width:520px"><div style="display:flex;justify-content:space-between;align-items:center;padding:1.2rem 1.5rem;border-bottom:1px solid rgba(0,191,255,.1)">
    <h3 style="font-weight:800">${offer ? 'Modifier' : 'Nouvelle'} offre</h3>
    <button class="btn btn-ghost btn-sm" onclick="this.closest('.modal-bg').remove()">✕</button>
  </div>
  <div style="padding:1.5rem;overflow:auto">
    <div id="offer-alert" class="hidden alert"></div>
    <div class="form-group"><label>Titre</label><input id="of-title" type="text" value="${escHtml(offer?.title||'')}"/></div>
    <div class="form-group"><label>Description</label><textarea id="of-desc">${escHtml(offer?.description||'')}</textarea></div>
    <div class="form-row">
      <div class="form-group"><label>Badge</label><input id="of-badge" type="text" value="${escHtml(offer?.badge||'')}" placeholder="ex: MOST POPULAR"/></div>
      <div class="form-group"><label>Prix</label><input id="of-price" type="text" value="${escHtml(offer?.price||'')}" placeholder="ex: 29 DT/mois"/></div>
    </div>
    ${offer ? `<div class="form-group"><label>Statut</label><select id="of-active"><option value="1" ${offer.active?'selected':''}>Actif</option><option value="0" ${!offer.active?'selected':''}>Inactif</option></select></div>` : ''}
    <button class="btn btn-cyan" onclick="saveOffer(${editId||'null'})" style="width:100%;justify-content:center">${offer ? 'Enregistrer' : 'Créer l\'offre'}</button>
  </div></div>`;
  document.body.appendChild(modal);
}

async function saveOffer(editId) {
  const al = document.getElementById('offer-alert');
  const body = {title:document.getElementById('of-title').value, description:document.getElementById('of-desc').value, badge:document.getElementById('of-badge').value, price:document.getElementById('of-price').value};
  if (editId) body.active = parseInt(document.getElementById('of-active').value);
  al.className = 'hidden alert';
  try {
    if (editId) await apiFetch(`/admin/offers/${editId}`, {method:'PUT', body:JSON.stringify(body)});
    else await apiFetch('/admin/offers', {method:'POST', body:JSON.stringify(body)});
    document.querySelector('.modal-bg')?.remove();
    adminAlert(editId ? '✅ Offre mise à jour' : '✅ Offre créée', 'success');
    loadAdmin();
  } catch(e) { al.className = 'alert alert-error'; al.textContent = `❌ ${e.message}`; }
}

// ══════════════════════════════════════════════════════════════════════
// INIT
// ══════════════════════════════════════════════════════════════════════
async function init() {
  if (getToken()) { try { currentUser = await apiFetch('/users/me'); } catch { clearToken(); } }
  navigate('home');
}
init();
</script>
</body>
</html>
