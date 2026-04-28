<?php
$orders = Order::getPending($userId);
?>
<div class="mb-6"><h1 class="text-2xl font-bold text-slate-800">Pesanan Masuk (API)</h1><p class="text-slate-500 text-sm mt-1">Verifikasi stok sebelum menerbitkan Payment Request.</p></div>
<?php if (empty($orders)): ?>
<div class="text-center py-20 bg-white rounded-xl border border-slate-200 shadow-sm">
    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4"><i class="ph ph-tray text-3xl text-slate-400"></i></div>
    <h3 class="text-lg font-bold text-slate-700">Belum ada pesanan masuk baru.</h3>
</div>
<?php else: ?>
<div class="space-y-4">
<?php foreach ($orders as $o): ?>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:border-primary transition-colors">
        <div>
            <div class="flex items-center gap-3 mb-1"><span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-bold rounded animate-pulse">New Order</span><span class="text-xs text-slate-400 font-mono"><?= $o['order_code'] ?></span></div>
            <h3 class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($o['umkm_name']) ?></h3>
            <p class="text-sm text-slate-500"><i class="ph ph-clock mr-1"></i> <?= $o['created_at'] ?> | <?= $o['item_count'] ?> macam bahan</p>
        </div>
        <button onclick="openReviewModal(<?= $o['id'] ?>)" class="w-full md:w-auto bg-primary hover:bg-primaryHover text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors flex items-center justify-center shadow-sm"><i class="ph ph-magnifying-glass mr-2 text-lg"></i> Review & Proses</button>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<div id="order-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 transition-opacity opacity-0">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden transform scale-95 transition-transform flex flex-col max-h-[90vh]" id="order-modal-content">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <div><h3 class="text-lg font-bold text-slate-800">Review Pesanan</h3><p class="text-xs text-slate-500" id="modal-order-id"></p></div>
            <button onclick="closeOrderModal()" class="text-slate-400 hover:text-red-500"><i class="ph ph-x text-xl"></i></button>
        </div>
        <div class="p-6 overflow-y-auto flex-1" id="modal-order-body">Loading...</div>
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
            <button onclick="closeOrderModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg">Batal</button>
            <button id="btn-approve" onclick="approveOrder()" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg shadow-sm flex items-center"><i class="ph ph-paper-plane-tilt mr-2"></i> Approve & Kirim Payment Request</button>
        </div>
    </div>
</div>

<script>
const BASE='<?= rtrim(dirname($_SERVER["SCRIPT_NAME"]),"/\\") ?>';
let currentOrderId=null;
async function openReviewModal(id){currentOrderId=id;const r=await apiCall(BASE+'/api/orders.php?action=detail&id='+id);if(r.status!=='success'){showToast(r.message,'error');return;}const o=r.data;document.getElementById('modal-order-id').innerText=o.order_code+' - '+o.umkm_name;let ih=o.items.map(i=>`<div class="flex justify-between items-center py-3 border-b border-slate-100 last:border-0"><div class="flex-1"><h4 class="text-sm font-bold text-slate-800">${i.material_name}</h4><p class="text-xs text-slate-500">${i.qty} ${i.unit} x ${formatRupiah(i.price_at_order)}</p></div><div class="w-28 text-right text-xs">${i.sufficient?'<span class="text-green-600 font-bold">OK</span>':'<span class="text-red-600 font-bold">Kurang</span>'}</div><div class="w-28 text-right font-bold text-sm">${formatRupiah(i.price_at_order*i.qty)}</div></div>`).join('');document.getElementById('modal-order-body').innerHTML=`<div class="bg-slate-50 p-4 rounded-xl border mb-4">${ih}</div><div class="bg-blue-50 p-4 rounded-xl border border-blue-100"><div class="flex justify-between text-sm mb-2"><span>Subtotal:</span><span>${formatRupiah(o.subtotal)}</span></div><div class="flex justify-between text-sm mb-2 text-green-600"><span>Fee 3%:</span><span>+${formatRupiah(o.fee_supplier)}</span></div><div class="flex justify-between font-bold text-lg border-t pt-2"><span>Total:</span><span class="text-primary">${formatRupiah(o.total)}</span></div></div>`;const b=document.getElementById('btn-approve');b.disabled=!o.stock_sufficient;b.classList.toggle('opacity-50',!o.stock_sufficient);const m=document.getElementById('order-modal'),c=document.getElementById('order-modal-content');m.classList.remove('hidden');m.classList.add('flex');setTimeout(()=>{m.classList.remove('opacity-0');m.classList.add('opacity-100');c.classList.remove('scale-95');c.classList.add('scale-100');},10);}
function closeOrderModal(){const m=document.getElementById('order-modal'),c=document.getElementById('order-modal-content');m.classList.remove('opacity-100');m.classList.add('opacity-0');c.classList.remove('scale-100');c.classList.add('scale-95');setTimeout(()=>{m.classList.add('hidden');m.classList.remove('flex');},200);}
async function approveOrder(){if(!currentOrderId)return;showToast('Memproses...','info');closeOrderModal();const r=await apiCall(BASE+'/api/orders.php?action=approve','POST',{order_id:currentOrderId});if(r.status==='success'){showToast(r.message);setTimeout(()=>location.reload(),1000);}else showToast(r.message,'error');}
</script>
