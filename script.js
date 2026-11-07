// ============================================================
// HikePass - App Script
// 1. Connect to API  : fetch(API_URL) + parsing JSON
// 2. Display API Data: renderMountains() DOM dinamis
// 3. Handle API State: loading spinner / success / error + retry
// ============================================================

// API PHP (REST endpoint). Fallback ke JSON statis bila server PHP tidak aktif.
const API_URL = './php/api/gunung.php';
const API_FALLBACK_URL = './gunung.json';

// Initial Fallback Data jika API publik tidak tersedia / offline
const INITIAL_GUNUNG = [
  {
    id: 'G1',
    nama: 'Gunung Semeru',
    jalur: 'Ranu Pane',
    lokasi: 'Lumajang, Jawa Timur',
    ketinggian: '3.676 MDPL',
    harga: 25000,
    kuotaMaks: 100,
    sisaKuota: 45,
    status: 'Open',
    gambar: 'https://images.unsplash.com/photo-1589553460730-57f97022d4f2?auto=format&fit=crop&w=600&q=80',
    deskripsi: 'Atap pulau Jawa dengan keindahan Danau Ranu Kumbolo dan Puncak Mahameru.',
    persyaratan: ['Surat Keterangan Sehat', 'Fotokopi KTP', 'Peralatan Standar Pendakian']
  },
  {
    id: 'G2',
    nama: 'Gunung Rinjani',
    jalur: 'Sembalun',
    lokasi: 'Lombok, NTB',
    ketinggian: '3.726 MDPL',
    harga: 50000,
    kuotaMaks: 80,
    sisaKuota: 12,
    status: 'Open',
    gambar: 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=600&q=80',
    deskripsi: 'Dikenal dengan keindahan Danau Segara Anak dan sabana Sembalun yang membentang.',
    persyaratan: ['Surat Keterangan Sehat', 'Kartu Identitas Diri', 'Deposit Bebas Sampah']
  },
  {
    id: 'G3',
    nama: 'Gunung Gede',
    jalur: 'Cibodas',
    lokasi: 'Cianjur, Jawa Barat',
    ketinggian: '2.958 MDPL',
    harga: 30000,
    kuotaMaks: 150,
    sisaKuota: 0,
    status: 'Closed',
    gambar: 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=600&q=80',
    deskripsi: 'Jalur favorit pendaki dengan pemandangan Telaga Warna dan Alun-alun Suryakencana.',
    persyaratan: ['Surat Keterangan Sehat', 'KTP/SIM/Kartu Pelajar']
  },
  {
    id: 'G4',
    nama: 'Gunung Prau',
    jalur: 'Patak Banteng',
    lokasi: 'Wonosobo, Jawa Tengah',
    ketinggian: '2.565 MDPL',
    harga: 20000,
    kuotaMaks: 120,
    sisaKuota: 65,
    status: 'Open',
    gambar: 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=600&q=80',
    deskripsi: 'Pemandangan golden sunrise terbaik se-Asia Tenggara dengan bukit teletubbies.',
    persyaratan: ['Surat Keterangan Sehat', 'Fotokopi KTP']
  }
];

// Application State
let appGunung = [];
let appBookings = [];
let pendingBooking = null;

// ================= INITIALIZATION =================
document.addEventListener('DOMContentLoaded', () => {
  loadDataFromStorage();
  initFormDates();

  // Connect to API & Handle States
  fetchGunungData();

  renderHistory();
  renderAdminView();
});

function loadDataFromStorage() {
  const localG = localStorage.getItem('hikepass_gunung');
  const localB = localStorage.getItem('hikepass_bookings');

  if (localG) appGunung = JSON.parse(localG);

  appBookings = localB ? JSON.parse(localB) : [
    {
      id: 'HKP-8921',
      gunungId: 'G1',
      gunungNama: 'Gunung Semeru (Ranu Pane)',
      ketuaNama: 'Budi Santoso',
      ketuaNik: '3515001234560001',
      ketuaHp: '08123456789',
      jumlahPendaki: 2,
      tanggalNaik: '2026-10-10',
      tanggalTurun: '2026-10-12',
      totalHarga: 50000,
      metodePembayaran: 'QRIS',
      status: 'PAID',
      waktuPesan: new Date().toISOString()
    }
  ];

  saveState();
}

function saveState() {
  localStorage.setItem('hikepass_gunung', JSON.stringify(appGunung));
  localStorage.setItem('hikepass_bookings', JSON.stringify(appBookings));
}

function initFormDates() {
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('booking-tanggal-naik').min = today;
  document.getElementById('booking-tanggal-turun').min = today;
  document.getElementById('filter-kuota-date').value = today;
}

// ================= 1. CONNECT TO API & HANDLE API STATES =================
async function fetchGunungData() {
  const container = document.getElementById('gunung-grid');
  const reloadBtn = document.getElementById('btn-reload-gunung');
  if (!container) return;

  // --- STATE 1: LOADING SPINNER ---
  if (reloadBtn) {
    reloadBtn.disabled = true;
    reloadBtn.classList.add('opacity-50', 'pointer-events-none');
  }
  container.className = 'flex flex-col items-center justify-center py-20 col-span-full space-y-4';
  container.innerHTML = `
    <div class="spinner"></div>
    <p class="text-slate-400 text-sm font-semibold animate-pulse">Menghubungkan ke API HikePass...</p>
    <p class="text-slate-500 text-xs">GET ${API_URL}</p>
  `;

  try {
    // Simulasi delay jaringan (800ms) agar transisi loading terasa jelas
    await new Promise((resolve) => setTimeout(resolve, 800));

    // 1. CONNECT: fetch REST API (PHP), fallback ke JSON statis
    let response = await fetch(API_URL, { cache: 'no-store' });
    let sumberData = 'REST API PHP';

    if (!response.ok) {
      response = await fetch(API_FALLBACK_URL, { cache: 'no-store' });
      sumberData = 'JSON fallback';
    }

    if (!response.ok) {
      throw new Error(`Koneksi API gagal (HTTP ${response.status})`);
    }

    // 1. PARSING JSON + validasi bentuk data
    const data = await response.json();
    if (!Array.isArray(data)) {
      throw new Error('Format JSON API tidak valid: root harus array.');
    }
    appGunung = normalizeGunungList(data);
    if (appGunung.length === 0) {
      throw new Error('API mengembalikan data kosong.');
    }
    saveState();

    // --- STATE 2: SUCCESS DATA (Dynamic DOM Manipulation) ---
    renderMountains(appGunung);
    showToast(`Data berhasil dimuat dari ${sumberData} (${appGunung.length} gunung)`, 'success');
  } catch (error) {
    console.warn('Fetch API error, menggunakan data cadangan lokal:', error.message);

    // Fallback agar UI tetap bisa dipakai offline / saat API down
    if (appGunung.length === 0) {
      appGunung = [...INITIAL_GUNUNG];
      saveState();
    }

    // --- STATE 3: ERROR + TOMBOL COBA LAGI ---
    renderErrorState(error.message);
  } finally {
    if (reloadBtn) {
      reloadBtn.disabled = false;
      reloadBtn.classList.remove('opacity-50', 'pointer-events-none');
    }
  }
}

/** Normalisasi field API agar render tidak pecah jika ada field kosong. */
function normalizeGunungList(list) {
  return list
    .filter((g) => g && g.id && g.nama)
    .map((g) => ({
      id: String(g.id),
      nama: g.nama || 'Gunung Tanpa Nama',
      jalur: g.jalur || '-',
      lokasi: g.lokasi || '-',
      ketinggian: g.ketinggian || '-',
      harga: Number(g.harga) || 0,
      kuotaMaks: Number(g.kuotaMaks) || 0,
      sisaKuota: g.sisaKuota !== undefined ? Number(g.sisaKuota) : 0,
      status: g.status === 'Closed' ? 'Closed' : 'Open',
      gambar: g.gambar || '',
      deskripsi: g.deskripsi || '',
      persyaratan: Array.isArray(g.persyaratan) ? g.persyaratan : [],
    }));
}

/**
 * Render Tampilan Error State + Tombol Coba Lagi.
 * Jika ada cache lokal, data tetap ditampilkan + banner retry di atasnya.
 */
function renderErrorState(errorMessage) {
  const container = document.getElementById('gunung-grid');
  const safeMsg = errorMessage || 'Terjadi gangguan jaringan saat terhubung ke server HikePass.';

  // Jika sudah ada data di cache lokal, tetap tampilkan data + banner offline
  if (appGunung && appGunung.length > 0) {
    renderMountains(appGunung);
    container.insertAdjacentHTML(
      'afterbegin',
      `
      <div class="col-span-full bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="text-xs text-amber-200">
          <i class="fa-solid fa-wifi mr-2"></i>
          <span class="font-bold">Mode Offline:</span> memakai data tersimpan. (${safeMsg})
        </div>
        <button onclick="fetchGunungData()" class="btn-secondary px-3 py-2 text-xs shrink-0">
          <i class="fa-solid fa-rotate-right mr-2"></i>Coba Lagi
        </button>
      </div>
      `
    );
    showToast(`Mode Offline: ${safeMsg}`, 'error');
    return;
  }

  container.className = 'flex flex-col items-center justify-center py-12 col-span-full';
  container.innerHTML = `
    <div class="bg-slate-800/90 border border-red-500/30 rounded-2xl p-8 max-w-md text-center shadow-2xl space-y-4">
      <div class="w-14 h-14 bg-red-500/10 text-red-400 rounded-full flex items-center justify-center mx-auto text-2xl border border-red-500/20">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <div>
        <h3 class="text-lg font-bold text-white mb-1">Gagal Memuat Data dari API</h3>
        <p class="text-xs text-slate-400 leading-relaxed">${safeMsg}</p>
      </div>
      <button onclick="fetchGunungData()" class="btn-primary bg-emerald-600 hover:bg-emerald-500 text-white w-full py-2.5 text-xs font-bold shadow-lg transition">
        <i class="fa-solid fa-rotate-right mr-2"></i> Coba Lagi
      </button>
    </div>
  `;
}

// ================= 2. DOM MANIPULATION DINAMIS =================
function renderMountains(data) {
  const container = document.getElementById('gunung-grid');
  container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
  container.innerHTML = '';

  if (!data || data.length === 0) {
    container.innerHTML = `<div class="col-span-full text-center py-12 text-slate-400">Tidak ada jalur pendakian tersedia.</div>`;
    return;
  }

  data.forEach(g => {
    const isClosed = g.status === 'Closed' || g.sisaKuota <= 0;
    const card = document.createElement('article');
    card.className = 'bg-slate-800 rounded-2xl overflow-hidden border border-slate-700 shadow-lg flex flex-col hover:border-emerald-500/50 transition duration-300';
    card.setAttribute('aria-label', `${g.nama} jalur ${g.jalur}`);
    card.innerHTML = `
      <div class="relative h-48 overflow-hidden">
        <img src="${g.gambar}" alt="${g.nama}" class="w-full h-full object-cover" onerror="this.src='https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=600&q=80'">
        <span class="absolute top-3 right-3 px-3 py-1 rounded-full text-xs font-bold ${g.status === 'Open' ? 'bg-emerald-500 text-slate-950' : 'bg-red-500 text-white'} shadow">
          ${g.status === 'Open' ? 'Buka' : 'Tutup'}
        </span>
        <div class="absolute bottom-3 left-3 bg-slate-900/80 backdrop-blur-sm px-2.5 py-1 rounded-md text-xs text-slate-200 font-semibold">
          <i class="fa-solid fa-mountain text-emerald-400 mr-1"></i> ${g.ketinggian}
        </div>
      </div>
      <div class="p-5 flex-1 flex flex-col justify-between">
        <div>
          <div class="text-xs text-emerald-400 font-semibold mb-1"><i class="fa-solid fa-location-dot mr-1"></i>${g.lokasi}</div>
          <h3 class="text-xl font-bold text-white mb-2">${g.nama} - Jalur ${g.jalur}</h3>
          <p class="text-slate-400 text-xs line-clamp-2 mb-4">${g.deskripsi}</p>
        </div>

        <div class="border-t border-slate-700/60 pt-4 mt-2">
          <div class="flex justify-between items-center mb-3 text-xs">
            <span class="text-slate-400">Sisa Kuota Hari Ini:</span>
            <span class="font-bold ${g.sisaKuota > 0 ? 'text-emerald-400' : 'text-red-400'}">${g.sisaKuota} / ${g.kuotaMaks} Slot</span>
          </div>
          <div class="flex items-center justify-between gap-2">
            <div>
              <span class="text-[10px] text-slate-400 block">Tiket Masuk:</span>
              <span class="text-base font-extrabold text-white">Rp ${Number(g.harga).toLocaleString('id-ID')}</span>
            </div>
            <div class="flex gap-2">
              <button onclick="openDetailModal('${g.id}')" class="btn-secondary px-3 py-2 text-xs">Detail</button>
              <button onclick="startBooking('${g.id}')" ${isClosed ? 'disabled class="btn-secondary px-3 py-2 text-xs opacity-50 cursor-not-allowed"' : 'class="btn-primary px-3 py-2 text-xs"'}>
                Pesan Tiket
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
    container.appendChild(card);
  });

  populateBookingDropdown();
  renderKuotaView();
  renderAdminView();
}

function filterGunung() {
  const query = document.getElementById('search-gunung').value.toLowerCase();
  const cards = document.getElementById('gunung-grid').children;

  Array.from(cards).forEach(card => {
    const text = card.innerText.toLowerCase();
    card.style.display = text.includes(query) ? 'flex' : 'none';
  });
}

function openDetailModal(id) {
  const g = appGunung.find(item => item.id === id);
  if (!g) return;

  const content = document.getElementById('modal-detail-content');
  content.innerHTML = `
    <div class="relative h-56 -mx-6 -mt-6 mb-4 overflow-hidden rounded-t-2xl">
      <img src="${g.gambar}" class="w-full h-full object-cover">
      <div class="absolute inset-0 bg-gradient-to-t from-slate-800 to-transparent"></div>
      <div class="absolute bottom-4 left-6">
        <span class="px-2.5 py-1 rounded bg-emerald-500 text-slate-950 font-bold text-xs">${g.ketinggian}</span>
        <h3 class="text-2xl font-black text-white mt-1">${g.nama} (${g.jalur})</h3>
      </div>
    </div>
    <div class="space-y-4 text-sm text-slate-300">
      <p>${g.deskripsi}</p>
      <div>
        <h4 class="font-bold text-white mb-2"><i class="fa-solid fa-clipboard-check text-emerald-400 mr-2"></i>Persyaratan Pendakian:</h4>
        <ul class="list-disc list-inside space-y-1 text-xs text-slate-300">
          ${g.persyaratan ? g.persyaratan.map(p => `<li>${p}</li>`).join('') : '<li>Membawa KTP/Identitas Diri</li>'}
        </ul>
      </div>
      <div class="bg-slate-900 p-4 rounded-xl border border-slate-700 flex justify-between items-center">
        <div>
          <span class="text-xs text-slate-400 block">Tarif Tiket Per Orang</span>
          <span class="text-lg font-bold text-emerald-400">Rp ${Number(g.harga).toLocaleString('id-ID')}</span>
        </div>
        <button onclick="closeModal('modal-detail'); startBooking('${g.id}');" class="btn-primary">Pesan Sekarang</button>
      </div>
    </div>
  `;
  document.getElementById('modal-detail').classList.remove('hidden');
}

// ================= NAVIGATION & ROLE SWITCHING =================
function switchTab(tabId) {
  document.querySelectorAll('.view-section').forEach(el => el.classList.add('hidden'));
  document.querySelectorAll('.nav-btn').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.mobile-nav-btn').forEach(el => el.classList.toggle('active', el.dataset.tab === tabId));

  const activeView = document.getElementById(`view-${tabId}`);
  if (activeView) activeView.classList.remove('hidden');

  const activeNav = document.getElementById(`tab-${tabId}`);
  if (activeNav) activeNav.classList.add('active');

  if (tabId === 'kuota') renderKuotaView();
  if (tabId === 'riwayat') renderHistory();
  if (tabId === 'admin') renderAdminView();
}

function changeRole(role) {
  const navPendaki = document.getElementById('nav-pendaki');
  const mobileNav = document.getElementById('mobile-nav');

  if (mobileNav) {
    mobileNav.classList.toggle('hidden', role !== 'pendaki');
  }

  if (role === 'pendaki') {
    navPendaki.classList.remove('role-hidden');
    switchTab('explore');
    showToast('Mode beralih ke Pendaki/Wisatawan', 'info');
  } else if (role === 'ranger') {
    navPendaki.classList.add('role-hidden');
    switchTab('ranger');
    showToast('Mode beralih ke Ranger Pos Verifikasi', 'info');
  } else if (role === 'admin') {
    navPendaki.classList.add('role-hidden');
    switchTab('admin');
    showToast('Mode beralih ke Admin Pengelola', 'info');
  }
}

// ================= 3. CEK KUOTA =================
function renderKuotaView() {
  const container = document.getElementById('kuota-container');
  const statusFilter = document.getElementById('filter-kuota-status').value;
  container.innerHTML = '';

  appGunung.forEach(g => {
    if (statusFilter !== 'all' && g.status !== statusFilter) return;

    const persentase = Math.round(((g.kuotaMaks - g.sisaKuota) / g.kuotaMaks) * 100);
    const card = document.createElement('article');
    card.className = 'bg-slate-800 border border-slate-700 rounded-xl p-5 shadow-md space-y-3';
    card.setAttribute('aria-label', `Kuota ${g.nama} jalur ${g.jalur}`);
    card.innerHTML = `
      <div class="flex justify-between items-start">
        <div>
          <h4 class="font-bold text-white text-lg">${g.nama}</h4>
          <span class="text-xs text-slate-400">Jalur ${g.jalur}</span>
        </div>
        <span class="px-2.5 py-1 rounded text-xs font-bold ${g.status === 'Open' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-red-500/20 text-red-300 border border-red-500/30'}">
          ${g.status === 'Open' ? 'Jalur Buka' : 'Jalur Tutup'}
        </span>
      </div>

      <div>
        <div class="flex justify-between text-xs mb-1">
          <span class="text-slate-400">Terisi: ${persentase}%</span>
          <span class="font-semibold text-white">${g.sisaKuota} Tersedia / ${g.kuotaMaks} Maks</span>
        </div>
        <div class="w-full bg-slate-900 h-2.5 rounded-full overflow-hidden">
          <div class="bg-emerald-500 h-full transition-all duration-500" style="width: ${persentase}%"></div>
        </div>
      </div>

      <button onclick="startBooking('${g.id}')" ${g.status === 'Closed' || g.sisaKuota <= 0 ? 'disabled class="w-full btn-secondary py-2 text-xs opacity-50"' : 'class="w-full btn-primary py-2 text-xs"'}>
        ${g.sisaKuota <= 0 ? 'Kuota Habis' : 'Booking Jalur Ini'}
      </button>
    `;
    container.appendChild(card);
  });
}

// ================= 4. WIZARD PEMESANAN TIKET =================
function populateBookingDropdown() {
  const select = document.getElementById('booking-gunung-id');
  if (!select) return;
  select.innerHTML = '';
  appGunung.forEach(g => {
    const opt = document.createElement('option');
    opt.value = g.id;
    opt.textContent = `${g.nama} (${g.jalur}) - Rp ${Number(g.harga).toLocaleString('id-ID')}`;
    select.appendChild(opt);
  });
}

function startBooking(gunungId) {
  switchTab('booking');
  if (gunungId) {
    document.getElementById('booking-gunung-id').value = gunungId;
  }
  goToStep(1);
  generateAnggotaFields();
}

function goToStep(stepNumber) {
  [1, 2, 3].forEach(s => {
    document.getElementById(`form-step-${s}`).classList.add('hidden');
    document.getElementById(`node-step-${s}`).classList.remove('active');
  });

  document.getElementById(`form-step-${stepNumber}`).classList.remove('hidden');
  for (let i = 1; i <= stepNumber; i++) {
    document.getElementById(`node-step-${i}`).classList.add('active');
  }

  const widthMap = { 1: '0%', 2: '50%', 3: '100%' };
  document.getElementById('step-bar').style.width = widthMap[stepNumber];
}

function generateAnggotaFields() {
  const count = parseInt(document.getElementById('booking-jumlah-pendaki').value) || 1;
  const container = document.getElementById('anggota-fields-container');
  container.innerHTML = '';

  for (let i = 2; i <= count; i++) {
    const box = document.createElement('div');
    box.className = 'p-4 bg-slate-900/40 rounded-xl border border-slate-700/80 space-y-3';
    box.innerHTML = `
      <h5 class="text-xs font-bold text-slate-300">Data Anggota Pendaki #${i}</h5>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <input type="text" id="anggota-nama-${i}" placeholder="Nama Lengkap Anggota ${i}" class="input-field" required>
        <input type="text" id="anggota-nik-${i}" placeholder="No. KTP / NIK Anggota ${i}" class="input-field" required>
      </div>
    `;
    container.appendChild(box);
  }
}

function handleNextStep(e, currentStep) {
  e.preventDefault();
  if (currentStep === 1) {
    const gId = document.getElementById('booking-gunung-id').value;
    const g = appGunung.find(item => item.id === gId);
    const jml = parseInt(document.getElementById('booking-jumlah-pendaki').value);

    if (g && g.sisaKuota < jml) {
      showToast(`Sisa kuota tidak mencukupi! Hanya tersisa ${g.sisaKuota} slot.`, 'error');
      return;
    }
    goToStep(2);
  } else if (currentStep === 2) {
    renderBookingSummary();
    goToStep(3);
  }
}

function updateBookingPriceCalculations() {
  // Dipanggil onchange form step-1. Ringkasan harga final dihitung
  // di renderBookingSummary() saat masuk Step 3 agar tidak duplikasi logic.
}

function renderBookingSummary() {
  const gId = document.getElementById('booking-gunung-id').value;
  const g = appGunung.find(item => item.id === gId);
  const naik = document.getElementById('booking-tanggal-naik').value;
  const turun = document.getElementById('booking-tanggal-turun').value;
  const jml = parseInt(document.getElementById('booking-jumlah-pendaki').value);
  const ketuaNama = document.getElementById('ketua-nama').value;
  const total = g.harga * jml;

  const box = document.getElementById('booking-summary-box');
  box.innerHTML = `
    <div class="flex justify-between items-center border-b border-slate-700 pb-3">
      <div>
        <h4 class="font-bold text-white text-base">${g.nama} - Jalur ${g.jalur}</h4>
        <span class="text-xs text-slate-400">Ketua Rombongan: ${ketuaNama}</span>
      </div>
      <span class="px-2.5 py-1 rounded bg-emerald-500/20 text-emerald-300 text-xs font-bold">${jml} Pendaki</span>
    </div>
    <div class="grid grid-cols-2 gap-2 text-xs text-slate-300">
      <div><span class="text-slate-400 block">Tanggal Naik:</span> ${naik}</div>
      <div><span class="text-slate-400 block">Tanggal Turun:</span> ${turun}</div>
      <div><span class="text-slate-400 block">Tarif / Orang:</span> Rp ${Number(g.harga).toLocaleString('id-ID')}</div>
      <div><span class="text-slate-400 block">Biaya Layanan Admin:</span> GRATIS</div>
    </div>
    <div class="border-t border-slate-700 pt-3 flex justify-between items-center">
      <span class="font-bold text-white text-sm">Total Pembayaran</span>
      <span class="text-xl font-black text-emerald-400">Rp ${Number(total).toLocaleString('id-ID')}</span>
    </div>
  `;
}

function processCheckout() {
  const gId = document.getElementById('booking-gunung-id').value;
  const g = appGunung.find(item => item.id === gId);
  const jml = parseInt(document.getElementById('booking-jumlah-pendaki').value);
  const naik = document.getElementById('booking-tanggal-naik').value;
  const turun = document.getElementById('booking-tanggal-turun').value;
  const ketuaNama = document.getElementById('ketua-nama').value;
  const ketuaNik = document.getElementById('ketua-nik').value;
  const ketuaHp = document.getElementById('ketua-hp').value;
  const method = document.querySelector('input[name="payment_method"]:checked').value;
  const total = g.harga * jml;
  const bookingId = 'HKP-' + Math.floor(1000 + Math.random() * 9000);

  pendingBooking = {
    id: bookingId,
    gunungId: g.id,
    gunungNama: `${g.nama} (${g.jalur})`,
    ketuaNama,
    ketuaNik,
    ketuaHp,
    jumlahPendaki: jml,
    tanggalNaik: naik,
    tanggalTurun: turun,
    totalHarga: total,
    metodePembayaran: method,
    status: 'PAID',
    waktuPesan: new Date().toISOString()
  };

  document.getElementById('pay-modal-code').innerText = bookingId;
  document.getElementById('pay-modal-total').innerText = `Rp ${Number(total).toLocaleString('id-ID')}`;
  document.getElementById('pay-modal-method').innerText = method;

  const qrTarget = document.getElementById('payment-qrcode-target');
  qrTarget.innerHTML = '';
  new QRCode(qrTarget, {
    text: `PAYMENT-${bookingId}-${total}`,
    width: 128,
    height: 128
  });

  document.getElementById('modal-payment').classList.remove('hidden');
}

function simulatePaymentSuccess() {
  if (!pendingBooking) return;

  const g = appGunung.find(item => item.id === pendingBooking.gunungId);
  if (g) {
    g.sisaKuota = Math.max(0, g.sisaKuota - pendingBooking.jumlahPendaki);
  }

  appBookings.unshift(pendingBooking);
  saveState();

  closeModal('modal-payment');
  showToast(`Pembayaran Berhasil! Kode Booking: ${pendingBooking.id}`, 'success');

  openTicketModal(pendingBooking.id);
  pendingBooking = null;

  document.getElementById('form-step-1').reset();
  document.getElementById('form-step-2').reset();
  goToStep(1);
}

// ================= 5. E-TICKET & RIWAYAT =================
function renderHistory() {
  const container = document.getElementById('riwayat-list');
  container.innerHTML = '';

  if (appBookings.length === 0) {
    container.innerHTML = `<div class="text-center text-slate-400 py-12 bg-slate-800 rounded-xl border border-slate-700">Belum ada riwayat pemesanan tiket.</div>`;
    return;
  }

  appBookings.forEach(b => {
    const card = document.createElement('article');
    card.className = 'bg-slate-800 border border-slate-700 rounded-xl p-5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4';
    card.setAttribute('aria-label', `Tiket ${b.id}`);

    let badgeClass = 'bg-emerald-500/20 text-emerald-300';
    let statusText = 'Aktif (Siap Check-In)';
    if (b.status === 'CHECKED_IN') {
      badgeClass = 'bg-amber-500/20 text-amber-300';
      statusText = 'Sedang Mendaki';
    } else if (b.status === 'COMPLETED') {
      badgeClass = 'bg-blue-500/20 text-blue-300';
      statusText = 'Selesai / Turun';
    }

    card.innerHTML = `
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span class="font-mono text-xs font-bold text-emerald-400 bg-slate-900 px-2 py-0.5 rounded">${b.id}</span>
          <span class="px-2 py-0.5 rounded text-xs font-bold ${badgeClass}">${statusText}</span>
        </div>
        <h4 class="font-bold text-white text-lg">${b.gunungNama}</h4>
        <div class="text-xs text-slate-400 space-x-3 mt-1">
          <span><i class="fa-solid fa-user mr-1"></i>${b.ketuaNama} (${b.jumlahPendaki} Orang)</span>
          <span><i class="fa-solid fa-calendar mr-1"></i>${b.tanggalNaik}</span>
        </div>
      </div>
      <button onclick="openTicketModal('${b.id}')" class="btn-primary py-2 text-xs shrink-0">
        <i class="fa-solid fa-qrcode mr-2"></i> Lihat E-Ticket
      </button>
    `;
    container.appendChild(card);
  });
}

function openTicketModal(bookingId) {
  const b = appBookings.find(item => item.id === bookingId);
  if (!b) return;

  const printable = document.getElementById('printable-ticket');
  printable.innerHTML = `
    <div class="flex justify-between items-center border-b border-slate-700 pb-4">
      <div>
        <span class="text-xs text-emerald-400 font-bold uppercase tracking-wider">E-Ticket Official</span>
        <h3 class="text-2xl font-black text-white">HikePass Boarding Pass</h3>
      </div>
      <div id="ticket-qr-container" class="bg-white p-2 rounded-lg"></div>
    </div>

    <div class="grid grid-cols-2 gap-4 text-xs py-2">
      <div>
        <span class="text-slate-400 block">Kode Booking:</span>
        <span class="font-mono font-bold text-emerald-400 text-base">${b.id}</span>
      </div>
      <div>
        <span class="text-slate-400 block">Status Registrasi:</span>
        <span class="font-bold text-white uppercase">${b.status}</span>
      </div>
      <div>
        <span class="text-slate-400 block">Destinasi Gunung:</span>
        <span class="font-semibold text-white">${b.gunungNama}</span>
      </div>
      <div>
        <span class="text-slate-400 block">Ketua Rombongan:</span>
        <span class="font-semibold text-white">${b.ketuaNama}</span>
      </div>
      <div>
        <span class="text-slate-400 block">Tanggal Pendakian:</span>
        <span class="font-semibold text-white">${b.tanggalNaik} s/d ${b.tanggalTurun}</span>
      </div>
      <div>
        <span class="text-slate-400 block">Jumlah Pendaki:</span>
        <span class="font-semibold text-white">${b.jumlahPendaki} Orang</span>
      </div>
    </div>

    <div class="bg-slate-900/60 p-3 rounded-lg border border-slate-800 text-[11px] text-slate-400">
      <i class="fa-solid fa-circle-info text-emerald-400 mr-1"></i> Tunjukkan QR Code ini kepada Ranger di pos perizinan pendakian untuk verifikasi fisik & identitas KTP.
    </div>
  `;

  setTimeout(() => {
    const qrContainer = document.getElementById('ticket-qr-container');
    if (qrContainer) {
      qrContainer.innerHTML = '';
      new QRCode(qrContainer, {
        text: b.id,
        width: 80,
        height: 80
      });
    }
  }, 50);

  document.getElementById('modal-ticket').classList.remove('hidden');
}

// ================= 6. VERIFIKASI POS RANGER =================
function searchTicketRanger() {
  const code = document.getElementById('ranger-search-code').value.trim().toUpperCase();
  const b = appBookings.find(item => item.id === code);
  const container = document.getElementById('ranger-result-container');

  if (!code) {
    showToast('Masukkan kode booking terlebih dahulu!', 'error');
    return;
  }

  container.classList.remove('hidden');

  if (!b) {
    container.innerHTML = `
      <div class="text-center py-6 text-red-400 font-bold">
        <i class="fa-solid fa-triangle-exclamation text-3xl mb-2 block"></i>
        Tiket / Kode Booking ${code} Tidak Ditemukan!
      </div>
    `;
    return;
  }

  container.innerHTML = `
    <div class="flex justify-between items-start border-b border-slate-800 pb-4">
      <div>
        <span class="text-xs font-mono font-bold text-emerald-400">${b.id}</span>
        <h3 class="text-xl font-bold text-white">${b.gunungNama}</h3>
        <p class="text-xs text-slate-400">Ketua: ${b.ketuaNama} (NIK: ${b.ketuaNik})</p>
      </div>
      <span class="px-3 py-1 rounded text-xs font-bold ${b.status === 'PAID' ? 'bg-emerald-500/20 text-emerald-300' : b.status === 'CHECKED_IN' ? 'bg-amber-500/20 text-amber-300' : 'bg-slate-700 text-slate-300'}">
        ${b.status}
      </span>
    </div>

    <div class="grid grid-cols-2 gap-3 text-xs text-slate-300">
      <div><span class="text-slate-400 block">Jumlah Anggota:</span> ${b.jumlahPendaki} Orang</div>
      <div><span class="text-slate-400 block">No HP Ketua:</span> ${b.ketuaHp}</div>
      <div><span class="text-slate-400 block">Jadwal:</span> ${b.tanggalNaik} s/d ${b.tanggalTurun}</div>
    </div>

    <div class="pt-4 flex gap-3 border-t border-slate-800">
      ${b.status === 'PAID' ? `
        <button onclick="updateTicketStatusRanger('${b.id}', 'CHECKED_IN')" class="btn-primary flex-1 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold">
          <i class="fa-solid fa-person-walking-luggage mr-2"></i> Verifikasi & Check-In (Naik)
        </button>
      ` : ''}

      ${b.status === 'CHECKED_IN' ? `
        <button onclick="updateTicketStatusRanger('${b.id}', 'COMPLETED')" class="btn-primary flex-1 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold">
          <i class="fa-solid fa-house-chimney-check mr-2"></i> Check-Out (Selesai Turun)
        </button>
      ` : ''}

      ${b.status === 'COMPLETED' ? `
        <div class="w-full text-center py-2 text-xs text-emerald-400 font-bold bg-emerald-950/50 rounded-lg border border-emerald-800">
          Pendakian Selesai. Pendaki telah kembali dengan selamat.
        </div>
      ` : ''}
    </div>
  `;
}

function updateTicketStatusRanger(bookingId, newStatus) {
  const b = appBookings.find(item => item.id === bookingId);
  if (b) {
    b.status = newStatus;
    saveState();
    searchTicketRanger();
    renderHistory();
    renderAdminView();
    showToast(`Status Tiket ${bookingId} diperbarui ke ${newStatus}`, 'success');
  }
}

// ================= 7. ADMIN PENGELOLA =================
function renderAdminView() {
  document.getElementById('stat-total-tiket').innerText = appBookings.length;
  document.getElementById('stat-pendaki-naik').innerText = appBookings
    .filter(b => b.status === 'CHECKED_IN')
    .reduce((sum, b) => sum + b.jumlahPendaki, 0);
  document.getElementById('stat-pendaki-turun').innerText = appBookings
    .filter(b => b.status === 'COMPLETED')
    .reduce((sum, b) => sum + b.jumlahPendaki, 0);

  const tbody = document.getElementById('admin-table-body');
  if (!tbody) return;
  tbody.innerHTML = '';

  appGunung.forEach(g => {
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-800/50';
    tr.innerHTML = `
      <td class="p-4 font-bold text-white">${g.nama} <span class="text-xs text-slate-400 font-normal">(${g.jalur})</span></td>
      <td class="p-4">${g.kuotaMaks} Slot</td>
      <td class="p-4 font-semibold text-emerald-400">${g.sisaKuota} Slot</td>
      <td class="p-4">
        <span class="px-2.5 py-1 rounded text-xs font-bold ${g.status === 'Open' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-red-500/20 text-red-300'}">
          ${g.status}
        </span>
      </td>
      <td class="p-4 text-center space-x-2">
        <button onclick="toggleStatusAdmin('${g.id}')" class="btn-secondary py-1 px-3 text-xs">
          Toggle ${g.status === 'Open' ? 'Tutup' : 'Buka'}
        </button>
        <button onclick="editKuotaAdmin('${g.id}')" class="btn-primary py-1 px-3 text-xs">
          Edit Kuota
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function toggleStatusAdmin(id) {
  const g = appGunung.find(item => item.id === id);
  if (g) {
    g.status = g.status === 'Open' ? 'Closed' : 'Open';
    saveState();
    renderMountains(appGunung);
    showToast(`Status ${g.nama} diubah menjadi ${g.status}`, 'info');
  }
}

function editKuotaAdmin(id) {
  const g = appGunung.find(item => item.id === id);
  if (!g) return;

  const newKuota = prompt(`Masukkan jumlah Kuota Maksimal Harian untuk ${g.nama}:`, g.kuotaMaks);
  if (newKuota !== null && !isNaN(newKuota)) {
    const val = parseInt(newKuota);
    g.kuotaMaks = val;
    g.sisaKuota = Math.min(g.sisaKuota, val);
    saveState();
    renderMountains(appGunung);
    showToast(`Kuota ${g.nama} diperbarui menjadi ${val}`, 'success');
  }
}

// ================= UTILS & HELPERS =================
function closeModal(modalId) {
  document.getElementById(modalId).classList.add('hidden');
}

function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  let bgClass = 'bg-slate-800 border-slate-700 text-white';

  if (type === 'success') bgClass = 'bg-emerald-600 text-white font-bold';
  if (type === 'error') bgClass = 'bg-red-600 text-white font-bold';

  toast.className = `toast px-4 py-3 rounded-xl border shadow-xl text-xs flex items-center gap-2 ${bgClass}`;
  toast.innerHTML = `
    <i class="fa-solid ${type === 'success' ? 'fa-circle-check' : type === 'error' ? 'fa-circle-exclamation' : 'fa-info-circle'} text-base"></i>
    <span>${message}</span>
  `;

  container.appendChild(toast);
  setTimeout(() => {
    toast.remove();
  }, 3000);
}
