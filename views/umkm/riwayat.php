<?php
$orders = Order::getByUmkm($userId);
?>
<div class="mb-6"><h1 class="text-2xl font-bold text-slate-800">Riwayat Pesanan</h1><p class="text-slate-500 text-sm mt-1">Daftar transaksi B2B yang telah dibayar lunas.</p></div>
<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead><tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-semibold tracking-wider"><th class="py-3 px-4">ID Transaksi & Waktu</th><th class="py-3 px-4">Total Nilai</th><th class="py-3 px-4">Status</th><th class="py-3 px-4">Keterangan</th></tr></thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="4" class="py-8 text-center text-slate-500">Belum ada riwayat belanja.</td></tr>
            <?php else: foreach ($orders as $o): ?>
                <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                    <td class="py-3 px-4"><div class="font-mono text-sm font-bold text-slate-700"><?= $o['order_code'] ?></div><div class="text-xs text-slate-500"><?= $o['created_at'] ?></div></td>
                    <td class="py-3 px-4 text-sm font-bold text-slate-800">Rp <?= number_format($o['total'],0,',','.') ?></td>
                    <td class="py-3 px-4">
                        <?php if ($o['status'] === 'completed'): ?>
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold border border-green-200"><i class="ph-fill ph-check-circle mr-1"></i>Lunas</span>
                        <?php elseif ($o['status'] === 'pending'): ?>
                        <span class="px-2 py-1 bg-amber-100 text-amber-700 rounded text-xs font-bold border border-amber-200"><i class="ph-fill ph-clock mr-1"></i>Menunggu</span>
                        <?php else: ?>
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold border border-red-200"><?= ucfirst($o['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3 px-4 text-sm text-slate-600">
                        <?php if ($o['status'] === 'completed'): ?><i class="ph ph-truck mr-1 text-slate-400"></i>Menunggu Kurir
                        <?php elseif ($o['status'] === 'pending'): ?>Menunggu konfirmasi supplier
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
