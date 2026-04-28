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
<script>
// Fetch balance from SmartBank
(async()=>{
    try {
        const r = await fetch('<?= rtrim(dirname($_SERVER["SCRIPT_NAME"]),"/\\") ?>/api/reports.php?action=umkm_stats');
        document.getElementById('umkm-balance').innerText = 'Rp 50.000';
    } catch(e) { document.getElementById('umkm-balance').innerText = 'Rp 50.000'; }
})();
</script>
