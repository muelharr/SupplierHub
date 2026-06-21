<?php
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$apiBase = $scriptDir === '/' ? '' : $scriptDir;
?>
<style>
    .sp-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.045);
    }

    .sp-label {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .sp-stat-value {
        color: #0f172a;
        font-size: clamp(1.25rem, 1.8vw, 1.8rem);
        font-weight: 900;
        line-height: 1.08;
        white-space: nowrap;
    }

    .sp-skeleton {
        position: relative;
        overflow: hidden;
        background: #e2e8f0;
    }

    .sp-skeleton::after {
        content: '';
        position: absolute;
        inset: 0;
        transform: translateX(-100%);
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.7), transparent);
        animation: spShimmer 1.25s infinite;
    }

    @keyframes spShimmer {
        100% { transform: translateX(100%); }
    }
</style>

<section class="min-h-full bg-slate-50 -m-4 md:-m-6 p-4 md:p-6">
    <div class="max-w-[1500px] mx-auto space-y-5">
        <header class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <div class="inline-flex items-center rounded-full bg-emerald-50 border border-emerald-100 px-3 py-1 text-xs font-extrabold text-emerald-700 mb-3">
                    Fitur Pengadaan SupplierHub
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-slate-950 tracking-tight">Smart Procurement</h1>
                <p class="mt-2 text-sm md:text-base text-slate-600">Optimasi pengadaan material berdasarkan anggaran yang tersedia.</p>
            </div>
            <div class="flex flex-wrap gap-2 lg:justify-end">
                <span class="inline-flex items-center rounded-full bg-white border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600">
                    Terakhir Dianalisis: <strong id="last-analyzed" class="ml-1 text-slate-900">Belum ada</strong>
                </span>
                <span class="inline-flex items-center rounded-full bg-emerald-50 border border-emerald-100 px-3 py-1.5 text-xs font-extrabold text-emerald-700">
                    Status: Siap Digunakan
                </span>
            </div>
        </header>

        <!-- Baris 1: Input Anggaran + Ringkasan Hasil -->
        <div class="grid grid-cols-1 xl:grid-cols-[minmax(280px,0.32fr)_minmax(0,0.68fr)] gap-5 items-stretch">
            <section class="sp-card rounded-2xl p-5">
                <div class="mb-5">
                    <p class="sp-label text-emerald-600">Input Anggaran</p>
                    <h2 class="mt-1 text-xl font-extrabold text-slate-900">Analisis Anggaran</h2>
                </div>

                <form id="smart-procurement-form" class="space-y-4">
                    <div>
                        <label for="budget-allocation" class="block text-sm font-bold text-slate-700 mb-2">Alokasi Anggaran</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-extrabold text-slate-400">Rp</span>
                            <input id="budget-allocation" type="text" inputmode="numeric" placeholder="Masukkan anggaran" value="250.000" class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-white text-slate-900 font-extrabold outline-none transition-all focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                        </div>
                    </div>

                    <div>
                        <label for="target-material-count" class="block text-sm font-bold text-slate-700 mb-2">Jumlah Material</label>
                        <input id="target-material-count" type="number" min="1" max="50" placeholder="Jumlah material" value="5" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-slate-900 font-extrabold outline-none transition-all focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                    </div>

                    <button id="analyze-budget-btn" type="submit" class="w-full rounded-xl bg-emerald-600 px-5 py-3.5 text-sm font-extrabold text-white shadow-sm transition-all hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-500/20">
                        Analisis Anggaran
                    </button>
                </form>
            </section>

            <section class="sp-card rounded-2xl p-5">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-2 mb-5">
                    <div>
                        <p class="sp-label text-emerald-600">Ringkasan Hasil</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-900">Ringkasan Anggaran</h2>
                    </div>
                    <span id="summary-status" class="inline-flex w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-extrabold text-slate-600">Belum Dianalisis</span>
                </div>

                <div id="stats-grid" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4" title="Total anggaran penuh akan tampil setelah analisis">
                        <p class="sp-label">Total Anggaran</p>
                        <p id="stat-total-budget" class="sp-stat-value mt-2">-</p>
                    </div>
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4" title="Anggaran digunakan penuh akan tampil setelah analisis">
                        <p class="sp-label">Anggaran Digunakan</p>
                        <p id="stat-used-budget" class="sp-stat-value mt-2">-</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white p-4" title="Sisa anggaran penuh akan tampil setelah analisis">
                        <p class="sp-label">Sisa Anggaran</p>
                        <p id="stat-remaining-budget" class="sp-stat-value mt-2">-</p>
                    </div>
                    <div class="rounded-xl border border-emerald-100 bg-white p-4" title="Efisiensi dihitung berdasarkan pemanfaatan anggaran, variasi item, dan skor manfaat material yang dipilih.">
                        <p class="sp-label">Efisiensi Estimasi</p>
                        <p id="stat-efficiency" class="sp-stat-value mt-2">-</p>
                        <p class="mt-2 text-[11px] leading-relaxed text-slate-500">Berdasarkan anggaran, variasi item, dan skor manfaat.</p>
                    </div>
                </div>
            </section>
        </div>

        <!-- State awal / loading / error -->
        <div id="empty-state" class="sp-card rounded-2xl p-6 border-dashed text-center">
            <p class="text-sm font-bold text-slate-700">Masukkan anggaran dan jumlah material, lalu klik Analisis Anggaran untuk menampilkan rekomendasi.</p>
        </div>

        <div id="loading-state" class="hidden space-y-5">
            <section class="sp-card rounded-2xl p-6">
                <h2 class="text-xl font-extrabold text-slate-900">Menganalisis rencana pengadaan...</h2>
                <p class="mt-1 text-sm text-slate-500">SupplierHub sedang menyusun rekomendasi material berdasarkan anggaran.</p>
                <div class="mt-5 h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full w-2/3 rounded-full bg-emerald-500 animate-pulse"></div>
                </div>
            </section>
            <section class="sp-card rounded-2xl p-6 space-y-3">
                <div class="sp-skeleton h-7 w-72 rounded-lg"></div>
                <div class="sp-skeleton h-16 rounded-xl"></div>
                <div class="sp-skeleton h-16 rounded-xl"></div>
            </section>
        </div>

        <div id="error-state" class="hidden sp-card rounded-2xl p-6 text-center border-red-100 bg-red-50/70">
            <h2 id="error-title" class="text-xl font-extrabold text-slate-900">Data rekomendasi belum dapat ditampilkan.</h2>
            <p id="error-message" class="mt-2 text-sm text-slate-600">Silakan coba beberapa saat lagi.</p>
        </div>

        <div id="success-state" class="hidden space-y-5">
            <!-- Baris 2: Pemanfaatan Anggaran -->
            <section class="sp-card rounded-2xl p-5 md:p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
                    <div>
                        <p class="sp-label text-emerald-600">Pemanfaatan Anggaran</p>
                        <h2 id="utilization-main" class="mt-1 text-2xl md:text-3xl font-extrabold text-slate-950">0% Anggaran Digunakan</h2>
                    </div>
                    <div class="rounded-xl bg-emerald-50 border border-emerald-100 px-4 py-3 md:text-right">
                        <p class="sp-label">Sisa Anggaran</p>
                        <p id="utilization-remaining" class="mt-1 text-xl font-extrabold text-emerald-700">Rp0</p>
                    </div>
                </div>
                <div class="mb-2 flex items-center justify-between text-xs font-extrabold text-slate-600">
                    <span>Digunakan <strong id="used-percent-label" class="text-emerald-700">0%</strong></span>
                    <span>Sisa <strong id="remaining-percent-label" class="text-slate-900">100%</strong></span>
                </div>
                <div class="h-4 rounded-full bg-slate-100 overflow-hidden border border-slate-200">
                    <div id="budget-progress" class="h-full rounded-full bg-emerald-600 transition-all duration-700" style="width: 0%;"></div>
                </div>
                <div class="mt-6 pt-5 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="sp-label text-slate-500">Subtotal Belanja</p>
                        <p id="breakdown-subtotal" class="text-base font-extrabold text-slate-900 mt-1">Rp0</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="sp-label text-slate-500">Biaya Layanan (<span id="breakdown-fee-percent">3%</span>)</p>
                        <p id="breakdown-fee" class="text-base font-extrabold text-slate-900 mt-1">Rp0</p>
                    </div>
                    <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-100">
                        <p class="sp-label text-emerald-600">Total Pengeluaran</p>
                        <p id="breakdown-grandtotal" class="text-base font-extrabold text-emerald-700 mt-1">Rp0</p>
                    </div>
                </div>
            </section>

            <!-- Baris 3: Hasil Rekomendasi -->
            <section class="sp-card rounded-2xl overflow-hidden">
                <div class="p-5 md:p-6 border-b border-slate-200 bg-white flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <p class="sp-label text-emerald-600">Material Direkomendasikan</p>
                        <h2 class="mt-1 text-2xl md:text-3xl font-extrabold text-slate-950">Rencana Pembelian yang Direkomendasikan</h2>
                    </div>
                    <span id="table-count-badge" class="inline-flex w-fit rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-extrabold text-emerald-700 border border-emerald-100">0 Direkomendasikan</span>
                </div>

                <div id="recommendation-list" class="hidden p-5 md:p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4"></div>

                <div id="recommendation-table-wrapper" class="overflow-x-auto min-h-[420px]">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                             <tr>
                                 <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-500 uppercase tracking-wider w-32">Prioritas</th>
                                 <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-500 uppercase tracking-wider min-w-[280px]">Material</th>
                                 <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-500 uppercase tracking-wider">Harga Satuan</th>
                                 <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-500 uppercase tracking-wider">Jumlah</th>
                                 <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-500 uppercase tracking-wider">Total</th>
                                 <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-500 uppercase tracking-wider">Skor Manfaat</th>
                                 <th class="px-6 py-4 text-left text-xs font-extrabold text-slate-500 uppercase tracking-wider">Status</th>
                             </tr>
                        </thead>
                        <tbody id="recommendation-table" class="divide-y divide-slate-100 bg-white"></tbody>
                    </table>
                </div>
            </section>

            <section class="sp-card rounded-2xl p-5 md:p-6">
                <p class="sp-label text-emerald-600">Ringkasan Rekomendasi</p>
                <h2 class="mt-1 text-2xl font-extrabold text-slate-950 mb-4">Ringkasan Rekomendasi</h2>
                <ul id="recommendation-insight" class="space-y-3 text-sm text-slate-700"></ul>
            </section>

            <section class="sp-card rounded-2xl p-5 md:p-6">
                <p class="sp-label text-emerald-600">Dampak Bisnis</p>
                <h2 class="mt-1 text-2xl font-extrabold text-slate-950 mb-4">Dampak Bisnis</h2>
                <ul id="business-impact" class="space-y-3 text-sm text-slate-700"></ul>
            </section>
        </div>
    </div>
</section>

<script>
(() => {
    const API_ENDPOINT = '<?= $apiBase ?>/rest-api/api/v1/orders/smart-bundle';
    const form = document.getElementById('smart-procurement-form');
    const budgetInput = document.getElementById('budget-allocation');
    const targetInput = document.getElementById('target-material-count');
    const analyzeButton = document.getElementById('analyze-budget-btn');

    const states = {
        empty: document.getElementById('empty-state'),
        loading: document.getElementById('loading-state'),
        error: document.getElementById('error-state'),
        success: document.getElementById('success-state')
    };

    const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(Math.max(0, Number(value) || 0));
    const formatCurrency = (value) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(Math.max(0, Number(value) || 0));

    const formatShortCurrency = (value) => {
        const amount = Math.max(0, Number(value) || 0);
        if (amount >= 1000000000) {
            return `Rp ${(amount / 1000000000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} M`;
        }
        if (amount >= 1000000) {
            return `Rp ${(amount / 1000000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} Jt`;
        }
        if (amount >= 1000) {
            return `Rp ${(amount / 1000).toLocaleString('id-ID', { maximumFractionDigits: 0 })} Rb`;
        }
        return formatCurrency(amount);
    };

    const parseCurrency = (value) => Number(String(value).replace(/[^0-9]/g, '')) || 0;
    const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

    function showState(activeState) {
        Object.entries(states).forEach(([name, element]) => {
            element.classList.toggle('hidden', name !== activeState);
        });
    }

    function showError(title, message) {
        document.getElementById('error-title').textContent = title;
        document.getElementById('error-message').textContent = message;
        document.getElementById('summary-status').textContent = 'Gagal Dianalisis';
        document.getElementById('summary-status').className = 'inline-flex w-fit rounded-full bg-red-50 px-3 py-1 text-xs font-extrabold text-red-700 border border-red-100';
        showState('error');
    }

    function setButtonLoading(isLoading) {
        analyzeButton.disabled = isLoading;
        analyzeButton.classList.toggle('opacity-75', isLoading);
        analyzeButton.textContent = isLoading ? 'Menganalisis...' : 'Analisis Anggaran';
    }

    budgetInput.addEventListener('input', () => {
        const value = parseCurrency(budgetInput.value);
        budgetInput.value = value ? formatNumber(value) : '';
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const budget = parseCurrency(budgetInput.value);
        const maxItems = Number(targetInput.value) || 0;

        if (budget <= 0) {
            showError('Data rekomendasi belum dapat ditampilkan.', 'Masukkan alokasi anggaran yang valid terlebih dahulu.');
            return;
        }

        if (maxItems <= 0) {
            showError('Data rekomendasi belum dapat ditampilkan.', 'Masukkan jumlah material yang valid terlebih dahulu.');
            return;
        }

        const token = localStorage.getItem('jwt_token');
        if (!token) {
            showError('Sesi login diperlukan.', 'Silakan login kembali sebelum menjalankan analisis pengadaan.');
            return;
        }

        showState('loading');
        setButtonLoading(true);

        try {
            const response = await fetch(API_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    budget: budget,
                    max_items: maxItems
                })
            });

            const payload = await response.json();

            if (!response.ok || payload.status !== 'success') {
                if (response.status === 401) {
                    showError('Sesi login diperlukan.', payload.message || 'Silakan login kembali sebelum menjalankan analisis pengadaan.');
                    return;
                }
                showError('Data rekomendasi belum dapat ditampilkan.', payload.message || 'Silakan coba beberapa saat lagi.');
                return;
            }

            renderSuccess(payload.data);
            showState('success');
            if (typeof showToast === 'function') {
                showToast('Analisis Smart Procurement berhasil dibuat.', 'success');
            }
        } catch (error) {
            console.error(error);
            showError('Data rekomendasi belum dapat ditampilkan.', 'Silakan coba beberapa saat lagi.');
        } finally {
            setButtonLoading(false);
        }
    });

    function renderSuccess(data) {
        const summary = data.summary || {};
        const metadata = data.metadata || {};
        const items = Array.isArray(data.items) ? data.items : [];
        const totalBudget = Number(summary.budget_input) || 0;
        const usedBudget = Number(summary.grand_total) || 0;
        const remainingBudget = Number(summary.budget_remaining) || 0;
        const efficiency = Number(summary.efficiency_score) || 0;
        const usedPercentage = totalBudget > 0 ? clamp((usedBudget / totalBudget) * 100, 0, 100) : 0;
        const topMaterial = items[0]?.name || 'Belum ada material';

         renderStats(totalBudget, usedBudget, remainingBudget, efficiency);
         renderUtilization(usedPercentage, remainingBudget, summary);
         renderRecommendations(items);
         renderInsight(items.length, usedPercentage, remainingBudget, topMaterial);
         renderBusinessImpact(usedPercentage);
         renderAnalysisStatus(metadata.timestamp);
     }

    function renderStats(totalBudget, usedBudget, remainingBudget, efficiency) {
        setStat('stat-total-budget', totalBudget);
        setStat('stat-used-budget', usedBudget);
        setStat('stat-remaining-budget', remainingBudget);
        document.getElementById('stat-efficiency').textContent = `${efficiency}%`;
        document.getElementById('summary-status').textContent = 'Berhasil Dianalisis';
        document.getElementById('summary-status').className = 'inline-flex w-fit rounded-full bg-emerald-50 px-3 py-1 text-xs font-extrabold text-emerald-700 border border-emerald-100';
    }

    function setStat(id, value) {
        const element = document.getElementById(id);
        element.textContent = formatShortCurrency(value);
        element.setAttribute('title', formatCurrency(value));
        element.closest('[title]')?.setAttribute('title', formatCurrency(value));
    }

     function renderUtilization(usedPercentage, remainingBudget, summary) {
         const roundedUsed = Math.round(usedPercentage);
         const remainingPercentage = clamp(100 - usedPercentage, 0, 100);
         document.getElementById('utilization-main').textContent = `${roundedUsed}% Anggaran Digunakan`;
         document.getElementById('utilization-remaining').textContent = formatCurrency(remainingBudget);
         document.getElementById('used-percent-label').textContent = `${roundedUsed}%`;
         document.getElementById('remaining-percent-label').textContent = `${Math.round(remainingPercentage)}%`;
         requestAnimationFrame(() => {
             document.getElementById('budget-progress').style.width = `${usedPercentage}%`;
         });

         // Render breakdown
         document.getElementById('breakdown-subtotal').textContent = formatCurrency(summary.subtotal || 0);
         document.getElementById('breakdown-fee-percent').textContent = summary.fee_percentage || '3%';
         document.getElementById('breakdown-fee').textContent = formatCurrency(summary.fee_supplier || 0);
         document.getElementById('breakdown-grandtotal').textContent = formatCurrency(summary.grand_total || 0);
     }

    function renderAnalysisStatus(timestamp) {
        const now = timestamp ? new Date(String(timestamp).replace(' ', 'T')) : new Date();
        const isValidDate = !Number.isNaN(now.getTime());
        document.getElementById('last-analyzed').textContent = isValidDate
            ? now.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
            : new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    }

    function renderRecommendations(items) {
        document.getElementById('table-count-badge').textContent = `${items.length} Direkomendasikan`;
        const list = document.getElementById('recommendation-list');
        const tableWrapper = document.getElementById('recommendation-table-wrapper');
        const table = document.getElementById('recommendation-table');

        if (items.length > 0 && items.length < 4) {
            tableWrapper.classList.add('hidden');
            list.classList.remove('hidden');
            list.innerHTML = items.map((item, index) => renderRecommendationCard(item, index)).join('');
            table.innerHTML = '';
            return;
        }

         list.classList.add('hidden');
         tableWrapper.classList.remove('hidden');
         table.innerHTML = items.length
             ? items.map((item, index) => renderRecommendationRow(item, index)).join('')
             : `<tr><td colspan="7" class="px-6 py-12 text-center text-sm font-semibold text-slate-500">Belum ada material yang sesuai dengan anggaran.</td></tr>`;
     }

     function renderRecommendationCard(item, index) {
         const isTop = index === 0;
         return `
             <article class="rounded-2xl border ${isTop ? 'border-emerald-300 bg-emerald-50/70 shadow-sm' : 'border-slate-200 bg-white'} p-5">
                 <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                     <span class="rounded-full ${isTop ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700'} px-3 py-1 text-xs font-extrabold">Prioritas #${index + 1}</span>
                     ${isTop ? '<span class="rounded-full bg-white px-3 py-1 text-xs font-extrabold text-emerald-700 border border-emerald-200">Prioritas Utama</span>' : ''}
                 </div>
                 <h3 class="text-lg font-extrabold text-slate-950">${escapeHtml(item.name || 'Material')}</h3>
                 <p class="mt-1 text-sm font-semibold text-slate-500">${escapeHtml(item.category || 'Material Umum')}</p>
                 <div class="mt-5 grid grid-cols-2 gap-x-3 gap-y-4">
                     <div>
                         <p class="sp-label">Harga Satuan</p>
                         <p class="mt-1 text-base font-extrabold text-slate-900">${formatCurrency(item.unit_price || 0)}</p>
                     </div>
                     <div>
                         <p class="sp-label">Jumlah</p>
                         <p class="mt-1 text-base font-extrabold text-slate-900">${item.qty || 0} ${escapeHtml(item.unit || '')}</p>
                     </div>
                     <div>
                         <p class="sp-label">Total</p>
                         <p class="mt-1 text-base font-extrabold text-slate-900">${formatCurrency(item.line_total || 0)}</p>
                     </div>
                     <div>
                         <p class="sp-label" title="Skor berasal dari utility_score backend berdasarkan kategori, stok, dan harga material.">Skor Manfaat</p>
                         <p class="mt-1 text-base font-extrabold text-slate-900">${getBenefitScore(item)} / 100</p>
                     </div>
                 </div>
                 <div class="mt-5">
                     <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-extrabold text-emerald-700 border border-emerald-100">Direkomendasikan</span>
                 </div>
             </article>
         `;
     }

     function renderRecommendationRow(item, index) {
         const isTop = index === 0;
         return `
             <tr class="${isTop ? 'bg-emerald-50/60 ring-1 ring-inset ring-emerald-100' : 'hover:bg-slate-50'} transition-colors">
                 <td class="px-6 py-5 whitespace-nowrap text-sm font-extrabold text-slate-900">
                     <div class="flex flex-col gap-1">
                         <span>#${index + 1}</span>
                         ${isTop ? '<span class="w-fit rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-extrabold text-white">Prioritas Utama</span>' : ''}
                     </div>
                 </td>
                 <td class="px-6 py-5 min-w-[300px]">
                     <p class="text-sm font-extrabold text-slate-950">${escapeHtml(item.name || 'Material')}</p>
                     <p class="text-xs font-semibold text-slate-500">${escapeHtml(item.category || 'Material Umum')}</p>
                 </td>
                 <td class="px-6 py-5 whitespace-nowrap text-sm font-extrabold text-slate-900">${formatCurrency(item.unit_price || 0)}</td>
                 <td class="px-6 py-5 whitespace-nowrap text-sm font-extrabold text-slate-900">${item.qty || 0} ${escapeHtml(item.unit || '')}</td>
                 <td class="px-6 py-5 whitespace-nowrap text-sm font-extrabold text-slate-900">${formatCurrency(item.line_total || 0)}</td>
                 <td class="px-6 py-5 whitespace-nowrap text-sm font-extrabold text-slate-900" title="Skor berasal dari utility_score backend berdasarkan kategori, stok, dan harga material.">${getBenefitScore(item)} / 100</td>
                 <td class="px-6 py-5 whitespace-nowrap">
                     <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-extrabold text-emerald-700 border border-emerald-100">Direkomendasikan</span>
                 </td>
             </tr>
         `;
     }

    function renderInsight(itemCount, usedPercentage, remainingBudget, topMaterial) {
        const insights = [
            `${itemCount} material berhasil dipilih.`,
            `${Math.round(usedPercentage)}% anggaran berhasil dimanfaatkan.`,
            `Sisa anggaran ${formatCurrency(remainingBudget)}.`,
            `Material prioritas: ${topMaterial}.`
        ];

        document.getElementById('recommendation-insight').innerHTML = insights.map(text => `
            <li class="flex gap-3"><span class="mt-0.5 font-extrabold text-emerald-600">✓</span><span class="font-semibold">${escapeHtml(text)}</span></li>
        `).join('');
    }

    function renderBusinessImpact(usedPercentage) {
        const impacts = [
            `Pemanfaatan anggaran mencapai ${Math.round(usedPercentage)}%.`,
            'Material dipilih berdasarkan nilai manfaat tertinggi.',
            'Membantu proses pengambilan keputusan pengadaan.',
            'Mengurangi proses seleksi manual.'
        ];

        document.getElementById('business-impact').innerHTML = impacts.map(text => `
            <li class="flex gap-3"><span class="mt-0.5 font-extrabold text-emerald-600">✓</span><span class="font-semibold">${escapeHtml(text)}</span></li>
        `).join('');
    }

    function getBenefitScore(item) {
        return Number(item.utility_score ?? item.benefit_score ?? item.procurement_score ?? 0);
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
})();
</script>
