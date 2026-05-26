<?php
$statsResponse = DashboardController::financeStats($userId);
$data = $statsResponse['data'];
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Keuangan & Neraca (Supplier)</h1>
    <p class="text-slate-500 text-sm mt-1">Pantau sisa saldo rekening, total pendapatan, dan riwayat mutasi.</p>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-10 -mt-10"></div>
        <div class="relative z-10 flex justify-between items-center">
            <div>
                <p class="text-sm text-blue-100 font-medium mb-1">Saldo Rekening (SmartBank)</p>
                <h3 class="text-3xl font-extrabold">Rp <?= number_format($data['balance'], 0, ',', '.') ?></h3>
            </div>
            <div class="w-14 h-14 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center text-3xl">
                <i class="ph-fill ph-bank text-white"></i>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl p-6 border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500 font-medium mb-1">Total Pendapatan Bersih (Setelah Fee)</p>
            <h3 class="text-2xl font-bold text-slate-800">Rp <?= number_format($data['income'], 0, ',', '.') ?></h3>
        </div>
        <div class="w-14 h-14 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center text-3xl">
            <i class="ph-fill ph-trend-up"></i>
        </div>
    </div>
</div>

<!-- Payment History Table -->
<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
        <h3 class="text-base font-bold text-slate-800"><i class="ph-fill ph-list-numbers mr-2 text-primary"></i>Riwayat Penerimaan Dana</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-white border-b border-slate-100 text-xs text-slate-400 uppercase tracking-wider">
                    <th class="py-4 px-6 font-semibold">Tanggal</th>
                    <th class="py-4 px-6 font-semibold">Deskripsi</th>
                    <th class="py-4 px-6 font-semibold">Referensi Gateway</th>
                    <th class="py-4 px-6 font-semibold text-right">Nominal</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php if (empty($data['history'])): ?>
                <tr>
                    <td colspan="4" class="py-8 px-6 text-center text-slate-500">Belum ada riwayat penerimaan dana.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($data['history'] as $tx): ?>
                    <tr class="border-b border-slate-50 hover:bg-slate-50 transition-colors">
                        <td class="py-4 px-6 text-slate-600"><?= date('d M Y, H:i', strtotime($tx['created_at'])) ?></td>
                        <td class="py-4 px-6 font-medium text-slate-800"><?= htmlspecialchars($tx['description']) ?></td>
                        <td class="py-4 px-6 text-slate-500 font-mono text-xs"><?= htmlspecialchars($tx['reference_id'] ?: '-') ?></td>
                        <td class="py-4 px-6 text-right font-bold text-emerald-600">+ Rp <?= number_format($tx['amount'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
