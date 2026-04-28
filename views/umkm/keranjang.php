<?php
$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
$cartItems = [];
foreach ($cart as $item) {
    $mat = Material::findById($item['material_id']);
    if ($mat) {
        $itemTotal = $mat['price'] * $item['qty'];
        $subtotal += $itemTotal;
        $cartItems[] = array_merge($mat, ['cart_qty' => $item['qty'], 'item_total' => $itemTotal]);
    }
}
$fee = (int) round($subtotal * FEE_SUPPLIER);
$grandTotal = $subtotal + $fee;
?>
<div class="mb-6"><h1 class="text-2xl font-bold text-slate-800">Keranjang Belanja</h1><p class="text-slate-500 text-sm mt-1">Review pesanan dan proses pembayaran melalui SmartBank.</p></div>

<?php if (empty($cartItems)): ?>
<div class="flex flex-col items-center justify-center py-20 bg-white rounded-xl border border-slate-200 shadow-sm">
    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4"><i class="ph ph-shopping-cart text-4xl text-slate-300"></i></div>
    <h3 class="text-lg font-bold text-slate-700 mb-2">Keranjang Anda Kosong</h3>
    <p class="text-sm text-slate-500 mb-6">Silakan pilih bahan baku di Katalog Supplier terlebih dahulu.</p>
    <a href="index.php?p=umkm&page=katalog" class="bg-primary text-white px-6 py-2 rounded-lg font-medium hover:bg-primaryHover transition-colors">Lihat Katalog</a>
</div>
<?php else: ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100 p-5 self-start">
        <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Daftar Bahan Baku</h3>
        <?php foreach ($cartItems as $idx => $item): ?>
        <div class="flex flex-col sm:flex-row justify-between items-center py-4 border-b border-slate-100 last:border-0 gap-4">
            <div class="flex items-center flex-1">
                <div class="w-12 h-12 bg-slate-50 rounded-lg flex items-center justify-center text-primary mr-4 border border-slate-100"><i class="ph-fill <?= $item['icon'] ?> text-2xl"></i></div>
                <div><h4 class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($item['name']) ?></h4><p class="text-xs text-slate-500">Rp <?= number_format($item['price'],0,',','.') ?> / <?= $item['unit'] ?></p></div>
            </div>
            <div class="flex items-center">
                <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden bg-slate-50 mr-4">
                    <a href="index.php?p=umkm&page=keranjang&cart_action=decrease&idx=<?= $idx ?>" class="px-3 py-1 hover:bg-slate-200 text-slate-600 font-bold transition-colors">-</a>
                    <div class="px-3 py-1 bg-white text-sm font-bold min-w-[2.5rem] text-center border-x border-slate-200"><?= $item['cart_qty'] ?></div>
                    <a href="index.php?p=umkm&page=keranjang&cart_action=increase&idx=<?= $idx ?>" class="px-3 py-1 hover:bg-slate-200 text-slate-600 font-bold transition-colors">+</a>
                </div>
                <div class="w-24 text-right"><div class="font-bold text-slate-800 text-sm">Rp <?= number_format($item['item_total'],0,',','.') ?></div></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 mb-4">
            <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Ringkasan Pembayaran</h3>
            <div class="space-y-2 text-sm text-slate-600 mb-4">
                <div class="flex justify-between"><span>Subtotal</span><span class="font-medium text-slate-800">Rp <?= number_format($subtotal,0,',','.') ?></span></div>
                <div class="flex justify-between items-center text-secondary bg-amber-50 p-2 rounded border border-amber-100"><span class="flex items-center"><i class="ph-fill ph-info mr-1"></i>Fee Supplier (3%)</span><span class="font-bold">+ Rp <?= number_format($fee,0,',','.') ?></span></div>
            </div>
            <div class="border-t border-slate-100 pt-3 flex justify-between items-center mb-6">
                <span class="font-bold text-slate-800 text-base">Total Tagihan</span>
                <span class="font-bold text-primary text-xl">Rp <?= number_format($grandTotal,0,',','.') ?></span>
            </div>
            <div class="bg-blue-50 border border-blue-100 p-3 rounded-lg text-xs text-blue-800 mb-4 flex items-start">
                <i class="ph-fill ph-bank mt-0.5 mr-2 text-lg"></i>
                <p>Pembayaran akan otomatis memotong saldo <strong>SmartBank</strong> Anda melalui API Gateway.</p>
            </div>
            <button onclick="processCheckout()" class="w-full bg-primary hover:bg-primaryHover text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center shadow-md">
                <i class="ph ph-wallet mr-2 text-lg"></i> Bayar via SmartBank
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
const BASE='<?= rtrim(dirname($_SERVER["SCRIPT_NAME"]),"/\\") ?>';
async function processCheckout(){
    showToast('Memproses pembayaran melalui API Gateway...','info');
    const r=await apiCall(BASE+'/api/orders.php?action=checkout','POST',{supplier_id:1,from_cart:true});
    if(r.status==='success'){showToast('Pesanan berhasil dibuat!');setTimeout(()=>window.location.href=BASE+'/index.php?p=umkm&page=riwayat',1500);}
    else showToast(r.message,'error');
}
</script>
