<?php
// Ambil halaman aktif dari parameter URL
$activePage = isset($_GET['p']) ? $_GET['p'] : 'dashboard';
?>

    <!-- Sidebar Admin -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col hidden md:flex transition-all duration-300">
        <div class="h-16 flex items-center px-6 border-b border-slate-700">
            <i class="ph-fill ph-package text-secondary text-2xl mr-2"></i>
            <span class="text-xl font-bold tracking-wide">Supplier<span class="text-blue-400">Hub</span></span>
        </div>

        <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
            <a href="index.php?p=dashboard" id="nav-dashboard"
                class="nav-btn w-full flex items-center px-3 py-2.5 rounded-lg <?php echo ($activePage === 'dashboard') ? 'bg-primary text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> font-medium transition-colors">
                <i class="ph ph-squares-four text-xl mr-3"></i> Dashboard Admin
            </a>
            <a href="index.php?p=manajemen" id="nav-manajemen"
                class="nav-btn w-full flex items-center px-3 py-2.5 rounded-lg <?php echo ($activePage === 'manajemen') ? 'bg-primary text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> font-medium transition-colors">
                <i class="ph ph-stack text-xl mr-3"></i> Manajemen Stok
            </a>
            <a href="index.php?p=pesanan" id="nav-pesanan"
                class="nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-lg <?php echo ($activePage === 'pesanan') ? 'bg-primary text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> font-medium transition-colors">
                <div class="flex items-center">
                    <i class="ph ph-tray-arrow-down text-xl mr-3"></i> Pesanan Masuk
                </div>
                <span id="order-badge"
                    class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full hidden">0</span>
            </a>
            <a href="index.php?p=laporan" id="nav-laporan"
                class="nav-btn w-full flex items-center px-3 py-2.5 rounded-lg <?php echo ($activePage === 'laporan') ? 'bg-primary text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?> font-medium transition-colors">
                <i class="ph ph-chart-line-up text-xl mr-3"></i> Laporan Tagihan
            </a>
        </nav>

        <div class="p-4 border-t border-slate-700">
            <div class="flex items-center">
                <div
                    class="w-8 h-8 rounded-full bg-blue-900 border border-blue-700 flex items-center justify-center text-sm font-bold text-blue-200">
                    PT</div>
                <div class="ml-3">
                    <p class="text-sm font-medium">PT. Sumber Pangan</p>
                    <p class="text-xs text-blue-400">Node Supplier B2B</p>
                </div>
            </div>
        </div>
    </aside>
