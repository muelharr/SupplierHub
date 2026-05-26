<?php
$orders = Order::getCompleted($userId);
$totalSubtotal = 0;
$totalFee = 0;
$totalGrand = 0;
foreach ($orders as $o) {
    $totalSubtotal += $o['subtotal'];
    $totalFee += $o['fee_supplier'];
    $totalGrand += $o['total'];
}
?>
<div class="mb-6"><h1 class="text-2xl font-bold text-slate-800">Laporan Keuangan</h1><p class="text-slate-500 text-sm mt-1">Riwayat payment request dan total pendapatan yang dilunasi.</p></div>

<!-- Financial Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm flex items-center">
        <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-2xl mr-4"><i class="ph-fill ph-package"></i></div>
        <div>
            <p class="text-sm text-slate-500 font-medium mb-1">Total Nilai Barang</p>
            <h3 class="text-xl font-bold text-slate-800">Rp <?= number_format($totalSubtotal, 0, ',', '.') ?></h3>
        </div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm flex items-center">
        <div class="w-12 h-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl mr-4"><i class="ph-fill ph-percent"></i></div>
        <div>
            <p class="text-sm text-slate-500 font-medium mb-1">Total Pendapatan Fee (3%)</p>
            <h3 class="text-xl font-bold text-emerald-600">Rp <?= number_format($totalFee, 0, ',', '.') ?></h3>
        </div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-slate-100 shadow-sm flex items-center bg-gradient-to-br from-indigo-50 to-blue-50/50">
        <div class="w-12 h-12 rounded-lg bg-indigo-500 text-white flex items-center justify-center text-2xl mr-4"><i class="ph-fill ph-money"></i></div>
        <div>
            <p class="text-sm text-indigo-700 font-bold mb-1">Total Pendapatan (Bersih)</p>
            <h3 class="text-2xl font-black text-indigo-900">Rp <?= number_format($totalGrand, 0, ',', '.') ?></h3>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[750px]">
            <thead><tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-semibold tracking-wider"><th class="py-3 px-4">Waktu & ID</th><th class="py-3 px-4">Klien (UMKM)</th><th class="py-3 px-4">Nilai Barang</th><th class="py-3 px-4">Pendapatan Fee</th><th class="py-3 px-4">Total Pendapatan</th><th class="py-3 px-4">Status</th></tr></thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="6" class="py-8 text-center text-slate-500">Belum ada laporan tagihan.</td></tr>
            <?php else: foreach ($orders as $o): ?>
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="py-3 px-4"><div class="text-xs text-slate-500"><?= $o['completed_at'] ?></div><div class="font-mono text-sm font-bold text-slate-700"><?= $o['order_code'] ?></div></td>
                    <td class="py-3 px-4 text-sm font-medium text-slate-800"><?= htmlspecialchars($o['umkm_name']) ?></td>
                    <td class="py-3 px-4 text-sm font-bold text-slate-800">Rp <?= number_format($o['subtotal'],0,',','.') ?></td>
                    <td class="py-3 px-4 text-sm font-bold text-green-600">+ Rp <?= number_format($o['fee_supplier'],0,',','.') ?></td>
                    <td class="py-3 px-4 text-sm font-bold text-indigo-700">Rp <?= number_format($o['total'],0,',','.') ?></td>
                    <td class="py-3 px-4"><span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold border border-green-200"><i class="ph-fill ph-check-circle mr-1"></i>Lunas</span></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
            <?php if (!empty($orders)): ?>
                <tfoot class="bg-slate-50/80 font-bold border-t-2 border-slate-200">
                    <tr>
                        <td colspan="2" class="py-4 px-4 text-sm text-slate-700 font-bold">TOTAL LAPORAN</td>
                        <td class="py-4 px-4 text-sm text-slate-900 font-black">Rp <?= number_format($totalSubtotal,0,',','.') ?></td>
                        <td class="py-4 px-4 text-sm text-emerald-700 font-black">+ Rp <?= number_format($totalFee,0,',','.') ?></td>
                        <td class="py-4 px-4 text-sm text-indigo-900 font-black">Rp <?= number_format($totalGrand,0,',','.') ?></td>
                        <td class="py-4 px-4"></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
