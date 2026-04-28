<?php
$orders = Order::getCompleted($userId);
?>
<div class="mb-6"><h1 class="text-2xl font-bold text-slate-800">Laporan Tagihan</h1><p class="text-slate-500 text-sm mt-1">Riwayat payment request yang dilunasi.</p></div>
<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[700px]">
            <thead><tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-semibold tracking-wider"><th class="py-3 px-4">Waktu & ID</th><th class="py-3 px-4">Klien (UMKM)</th><th class="py-3 px-4">Nilai Barang</th><th class="py-3 px-4">Pendapatan Fee</th><th class="py-3 px-4">Status</th></tr></thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="5" class="py-8 text-center text-slate-500">Belum ada laporan tagihan.</td></tr>
            <?php else: foreach ($orders as $o): ?>
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="py-3 px-4"><div class="text-xs text-slate-500"><?= $o['completed_at'] ?></div><div class="font-mono text-sm font-bold text-slate-700"><?= $o['order_code'] ?></div></td>
                    <td class="py-3 px-4 text-sm font-medium text-slate-800"><?= htmlspecialchars($o['umkm_name']) ?></td>
                    <td class="py-3 px-4 text-sm font-bold text-slate-800">Rp <?= number_format($o['subtotal'],0,',','.') ?></td>
                    <td class="py-3 px-4 text-sm font-bold text-green-600">+ Rp <?= number_format($o['fee_supplier'],0,',','.') ?></td>
                    <td class="py-3 px-4"><span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold border border-green-200"><i class="ph-fill ph-check-circle mr-1"></i>Lunas</span></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
