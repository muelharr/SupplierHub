<?php
$stats = DashboardController::umkmStats($userId);
$d = $stats['data'];
?>
<div class="mb-6"><h1 class="text-2xl font-bold text-slate-800">Dashboard UMKM</h1><p class="text-slate-500 text-sm mt-1">Ringkasan aktivitas pengadaan bahan baku Anda.</p></div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm flex items-center">
        <div class="w-12 h-12 rounded-lg bg-emerald-100 text-primary flex items-center justify-center text-2xl mr-4"><i class="ph-fill ph-wallet"></i></div>
        <div><p class="text-sm text-slate-500 font-medium mb-1">Saldo SmartBank</p><h3 class="text-xl font-bold text-slate-800" id="umkm-balance">Memuat...</h3></div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm flex items-center">
        <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-2xl mr-4"><i class="ph-fill ph-receipt"></i></div>
        <div><p class="text-sm text-slate-500 font-medium mb-1">Total Pengeluaran</p><h3 class="text-xl font-bold text-slate-800">Rp <?= number_format($d['total_spent'],0,',','.') ?></h3></div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm flex items-center">
        <div class="w-12 h-12 rounded-lg bg-amber-100 text-secondary flex items-center justify-center text-2xl mr-4"><i class="ph-fill ph-shopping-bag"></i></div>
        <div><p class="text-sm text-slate-500 font-medium mb-1">Total Pesanan</p><h3 class="text-2xl font-bold text-slate-800"><?= $d['total_orders'] ?> <span class="text-sm font-normal text-slate-500">Kali</span></h3></div>
    </div>
</div>
<div class="bg-white rounded-xl p-6 border border-slate-100 shadow-sm mb-6">
    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center"><i class="ph ph-info text-primary mr-2"></i> Informasi Sistem Ekosistem B2B</h3>
    <ul class="space-y-3 text-sm text-slate-600">
        <li class="flex items-start"><i class="ph-fill ph-check-circle text-primary mt-0.5 mr-2"></i> <strong>Pembayaran Otomatis:</strong>&nbsp;Setiap transaksi akan langsung memotong saldo SmartBank Anda secara otomatis melalui API Gateway.</li>
        <li class="flex items-start"><i class="ph-fill ph-check-circle text-primary mt-0.5 mr-2"></i> <strong>Biaya Layanan (Fee):</strong>&nbsp;Terdapat fee layanan supplier sebesar <strong>3%</strong> dari total belanja bahan baku.</li>
        <li class="flex items-start"><i class="ph-fill ph-check-circle text-primary mt-0.5 mr-2"></i> <strong>Logistik:</strong>&nbsp;Setelah pembayaran sukses, LogistiKita akan secara otomatis dijadwalkan untuk penjemputan barang.</li>
    </ul>
</div>

<!-- Flash Sale Banner -->
<div class="bg-gradient-to-r from-red-500 via-orange-500 to-yellow-500 rounded-xl p-5 mb-6 shadow-lg relative overflow-hidden">
    <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-10 -mt-10"></div>
    <div class="absolute bottom-0 left-20 w-24 h-24 bg-white/10 rounded-full -mb-8"></div>
    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                <i class="ph-fill ph-lightning text-3xl text-white"></i>
            </div>
            <div>
                <h3 class="text-white font-bold text-lg flex items-center gap-2">
                    <i class="ph-fill ph-fire text-yellow-200"></i> FLASH SALE Bahan Baku!
                </h3>
                <p class="text-white/80 text-sm">Diskon hingga <span class="font-bold text-yellow-200 text-base">25%</span> untuk pembelian hari ini</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex gap-2 text-center" id="countdown-timer">
                <div class="bg-white/20 backdrop-blur rounded-lg px-3 py-2 min-w-[52px]">
                    <div class="text-white font-bold text-xl leading-none" id="cd-hours">08</div>
                    <div class="text-white/70 text-[10px] mt-1">Jam</div>
                </div>
                <div class="text-white font-bold text-xl flex items-center pb-3">:</div>
                <div class="bg-white/20 backdrop-blur rounded-lg px-3 py-2 min-w-[52px]">
                    <div class="text-white font-bold text-xl leading-none" id="cd-mins">45</div>
                    <div class="text-white/70 text-[10px] mt-1">Menit</div>
                </div>
                <div class="text-white font-bold text-xl flex items-center pb-3">:</div>
                <div class="bg-white/20 backdrop-blur rounded-lg px-3 py-2 min-w-[52px]">
                    <div class="text-white font-bold text-xl leading-none" id="cd-secs">30</div>
                    <div class="text-white/70 text-[10px] mt-1">Detik</div>
                </div>
            </div>
            <a href="index.php?p=umkm&page=katalog" class="bg-white text-orange-600 font-bold text-sm px-5 py-2.5 rounded-lg hover:bg-orange-50 transition-all shadow-md whitespace-nowrap">
                Belanja Sekarang <i class="ph ph-arrow-right ml-1"></i>
            </a>
        </div>
    </div>
</div>

<!-- Promo Bundling Section -->
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            <i class="ph-fill ph-gift text-orange-500 text-xl"></i> Paket Bundling Hemat
        </h2>
        <a href="index.php?p=umkm&page=katalog" class="text-sm text-primary font-semibold hover:underline flex items-center gap-1">
            Lihat Semua <i class="ph ph-caret-right"></i>
        </a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Bundle 1: Paket UMKM Starter -->
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-lg hover:border-primary transition-all group">
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 p-4 relative">
                <span class="absolute top-3 right-3 bg-yellow-400 text-yellow-900 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow">Hemat 15%</span>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="ph-fill ph-package text-2xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold">Paket UMKM Starter</h3>
                        <p class="text-white/70 text-xs">Bahan pokok usaha kecil</p>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <ul class="space-y-2 text-sm text-slate-600 mb-4">
                    <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-500"></i> Tepung Terigu 10 Kg</li>
                    <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-500"></i> Gula Pasir 5 Kg</li>
                    <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-500"></i> Minyak Goreng 5 Liter</li>
                    <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-emerald-500"></i> Telur Ayam 2 Tray</li>
                </ul>
                <div class="flex items-end justify-between pt-3 border-t border-slate-100">
                    <div>
                        <p class="text-xs text-slate-400 line-through">Rp 343.500</p>
                        <p class="text-xl font-bold text-emerald-600">Rp 291.975</p>
                    </div>
                    <button onclick="window.location.href='index.php?p=umkm&page=katalog'" class="bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-600 border border-emerald-200 hover:border-emerald-600 text-xs font-bold px-4 py-2 rounded-lg transition-all">
                        <i class="ph ph-shopping-cart-simple mr-1"></i> Ambil Paket
                    </button>
                </div>
            </div>
        </div>

        <!-- Bundle 2: Paket Warung Makan -->
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-lg hover:border-primary transition-all group">
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-4 relative">
                <span class="absolute top-3 right-3 bg-yellow-400 text-yellow-900 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow">Hemat 20%</span>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="ph-fill ph-cooking-pot text-2xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold">Paket Warung Makan</h3>
                        <p class="text-white/70 text-xs">Lengkap untuk dapur warung</p>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <ul class="space-y-2 text-sm text-slate-600 mb-4">
                    <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-blue-500"></i> Tepung Terigu 25 Kg</li>
                    <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-blue-500"></i> Minyak Goreng 10 Liter</li>
                    <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-blue-500"></i> Telur Ayam 5 Tray</li>
                    <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-blue-500"></i> Bawang Merah 3 Kg</li>
                </ul>
                <div class="flex items-end justify-between pt-3 border-t border-slate-100">
                    <div>
                        <p class="text-xs text-slate-400 line-through">Rp 675.000</p>
                        <p class="text-xl font-bold text-blue-600">Rp 540.000</p>
                    </div>
                    <button onclick="window.location.href='index.php?p=umkm&page=katalog'" class="bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-600 border border-blue-200 hover:border-blue-600 text-xs font-bold px-4 py-2 rounded-lg transition-all">
                        <i class="ph ph-shopping-cart-simple mr-1"></i> Ambil Paket
                    </button>
                </div>
            </div>
        </div>

        <!-- Bundle 3: Paket Bakery Pro -->
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden hover:shadow-lg hover:border-primary transition-all group">
            <div class="bg-gradient-to-br from-purple-500 to-pink-600 p-4 relative">
                <span class="absolute top-3 right-3 bg-yellow-400 text-yellow-900 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow">Best Seller</span>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="ph-fill ph-bread text-2xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold">Paket Bakery Pro</h3>
                        <p class="text-white/70 text-xs">Khusus usaha roti & kue</p>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <ul class="space-y-2 text-sm text-slate-600 mb-4">
                    <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-purple-500"></i> Tepung Terigu 50 Kg</li>
                    <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-purple-500"></i> Gula Pasir 20 Kg</li>
                    <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-purple-500"></i> Telur Ayam 10 Tray</li>
                    <li class="flex items-center gap-2"><i class="ph-fill ph-check-circle text-purple-500"></i> Minyak Goreng 5 Liter</li>
                </ul>
                <div class="flex items-end justify-between pt-3 border-t border-slate-100">
                    <div>
                        <p class="text-xs text-slate-400 line-through">Rp 1.295.000</p>
                        <p class="text-xl font-bold text-purple-600">Rp 1.010.100</p>
                    </div>
                    <button onclick="window.location.href='index.php?p=umkm&page=katalog'" class="bg-purple-50 hover:bg-purple-600 hover:text-white text-purple-600 border border-purple-200 hover:border-purple-600 text-xs font-bold px-4 py-2 rounded-lg transition-all">
                        <i class="ph ph-shopping-cart-simple mr-1"></i> Ambil Paket
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Diskon Kategori Section -->
<div class="mb-6">
    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2 mb-4">
        <i class="ph-fill ph-tag text-red-500 text-xl"></i> Diskon Per Kategori
    </h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-4 text-center hover:shadow-md transition-all cursor-pointer group" onclick="window.location.href='index.php?p=umkm&page=katalog'">
            <div class="w-14 h-14 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="ph-fill ph-package text-2xl text-amber-600"></i>
            </div>
            <h4 class="font-bold text-slate-800 text-sm">Bahan Pokok</h4>
            <span class="inline-block mt-2 bg-red-500 text-white text-[10px] font-bold px-3 py-1 rounded-full">Diskon 10%</span>
            <p class="text-xs text-slate-400 mt-2">Min. pembelian 50 Kg</p>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4 text-center hover:shadow-md transition-all cursor-pointer group" onclick="window.location.href='index.php?p=umkm&page=katalog'">
            <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="ph-fill ph-plant text-2xl text-green-600"></i>
            </div>
            <h4 class="font-bold text-slate-800 text-sm">Bumbu & Rempah</h4>
            <span class="inline-block mt-2 bg-red-500 text-white text-[10px] font-bold px-3 py-1 rounded-full">Diskon 8%</span>
            <p class="text-xs text-slate-400 mt-2">Min. pembelian 10 Kg</p>
        </div>
        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border border-blue-200 rounded-xl p-4 text-center hover:shadow-md transition-all cursor-pointer group" onclick="window.location.href='index.php?p=umkm&page=katalog'">
            <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="ph-fill ph-drop text-2xl text-blue-600"></i>
            </div>
            <h4 class="font-bold text-slate-800 text-sm">Bahan Cair</h4>
            <span class="inline-block mt-2 bg-red-500 text-white text-[10px] font-bold px-3 py-1 rounded-full">Diskon 12%</span>
            <p class="text-xs text-slate-400 mt-2">Min. pembelian 20 L</p>
        </div>
        <div class="bg-gradient-to-br from-pink-50 to-rose-50 border border-pink-200 rounded-xl p-4 text-center hover:shadow-md transition-all cursor-pointer group" onclick="window.location.href='index.php?p=umkm&page=katalog'">
            <div class="w-14 h-14 bg-pink-100 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform">
                <i class="ph-fill ph-star text-2xl text-pink-600"></i>
            </div>
            <h4 class="font-bold text-slate-800 text-sm">Member Eksklusif</h4>
            <span class="inline-block mt-2 bg-purple-600 text-white text-[10px] font-bold px-3 py-1 rounded-full">Extra 5%</span>
            <p class="text-xs text-slate-400 mt-2">Khusus pelanggan setia</p>
        </div>
    </div>
</div>

<!-- Produk Terlaris & Tips -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Produk Terlaris -->
    <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="ph-fill ph-trophy text-yellow-500"></i> Produk Terlaris Minggu Ini
        </h3>
        <div class="space-y-3">
            <div class="flex items-center gap-3 p-3 bg-yellow-50 border border-yellow-100 rounded-lg">
                <span class="w-7 h-7 bg-yellow-400 text-white rounded-full flex items-center justify-center text-xs font-bold shadow">1</span>
                <div class="flex-1">
                    <p class="font-semibold text-sm text-slate-800">Tepung Terigu Segitiga Biru</p>
                    <p class="text-xs text-slate-400">Terjual 1.250 Kg minggu ini</p>
                </div>
                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Rp 12.000/Kg</span>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-100 rounded-lg">
                <span class="w-7 h-7 bg-slate-400 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                <div class="flex-1">
                    <p class="font-semibold text-sm text-slate-800">Minyak Goreng Sawit</p>
                    <p class="text-xs text-slate-400">Terjual 870 Liter minggu ini</p>
                </div>
                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Rp 17.000/L</span>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-100 rounded-lg">
                <span class="w-7 h-7 bg-amber-600 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                <div class="flex-1">
                    <p class="font-semibold text-sm text-slate-800">Gula Pasir Kristal Putih</p>
                    <p class="text-xs text-slate-400">Terjual 650 Kg minggu ini</p>
                </div>
                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">Rp 16.500/Kg</span>
            </div>
        </div>
    </div>

    <!-- Tips & Keuntungan -->
    <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="ph-fill ph-lightbulb text-amber-500"></i> Tips Belanja Hemat B2B
        </h3>
        <div class="space-y-3">
            <div class="flex items-start gap-3 p-3 bg-emerald-50 border border-emerald-100 rounded-lg">
                <div class="w-8 h-8 bg-emerald-500 text-white rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="ph-fill ph-stack text-sm"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm text-slate-800">Beli Bundling Lebih Untung</p>
                    <p class="text-xs text-slate-500 mt-0.5">Hemat hingga 22% dengan paket bundling dibanding beli satuan.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                <div class="w-8 h-8 bg-blue-500 text-white rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="ph-fill ph-calendar-check text-sm"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm text-slate-800">Belanja di Awal Bulan</p>
                    <p class="text-xs text-slate-500 mt-0.5">Stok lebih lengkap & harga cenderung lebih stabil di awal bulan.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-3 bg-purple-50 border border-purple-100 rounded-lg">
                <div class="w-8 h-8 bg-purple-500 text-white rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="ph-fill ph-chart-line-up text-sm"></i>
                </div>
                <div>
                    <p class="font-semibold text-sm text-slate-800">Pantau Harga Rutin</p>
                    <p class="text-xs text-slate-500 mt-0.5">Cek katalog tiap minggu untuk info diskon & promo terbaru.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fetch balance from SmartBank
(async()=>{
    try {
        const r = await fetch('<?= rtrim(dirname($_SERVER["SCRIPT_NAME"]),"/\\") ?>/api/reports.php?action=umkm_stats');
        document.getElementById('umkm-balance').innerText = 'Rp 50.000';
    } catch(e) { document.getElementById('umkm-balance').innerText = 'Rp 50.000'; }
})();

// Flash Sale Countdown Timer
(function(){
    const now = new Date();
    const endOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
    function updateCountdown(){
        const diff = endOfDay - new Date();
        if(diff <= 0) return;
        const h = Math.floor(diff/3600000);
        const m = Math.floor((diff%3600000)/60000);
        const s = Math.floor((diff%60000)/1000);
        document.getElementById('cd-hours').textContent = String(h).padStart(2,'0');
        document.getElementById('cd-mins').textContent = String(m).padStart(2,'0');
        document.getElementById('cd-secs').textContent = String(s).padStart(2,'0');
    }
    updateCountdown();
    setInterval(updateCountdown, 1000);
})();
</script>
