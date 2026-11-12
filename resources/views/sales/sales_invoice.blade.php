@extends('layouts.master2')
<title> معرض أيه - فاتورة المبيعات </title>

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
<link href="{{ URL::asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet">
<style>
    .pos {
        --pos-primary: #4f46e5;
        --pos-primary-dark: #4338ca;
        --pos-primary-soft: #eef2ff;
        --pos-success: #059669;
        --pos-success-soft: #ecfdf5;
        --pos-danger: #dc2626;
        --pos-danger-soft: #fef2f2;
        --pos-warning: #d97706;
        --pos-warning-soft: #fffbeb;
        --pos-text: #111827;
        --pos-muted: #6b7280;
        --pos-border: #e5e7eb;
        --pos-bg: #f3f4f6;
        --pos-card: #ffffff;
        --pos-radius: 14px;
        font-family: 'Cairo', system-ui, -apple-system, 'Segoe UI', sans-serif;
        color: var(--pos-text);
        direction: rtl;
        text-align: right;
    }
    .pos *, .pos *::before, .pos *::after { box-sizing: border-box; }

    /* Custom scrollbar */
    .pos-scrollable { scrollbar-width: thin; scrollbar-color: #c7c9d1 transparent; }
    .pos-scrollable::-webkit-scrollbar { width: 7px; }
    .pos-scrollable::-webkit-scrollbar-track { background: transparent; }
    .pos-scrollable::-webkit-scrollbar-thumb { background: #c7c9d1; border-radius: 10px; }
    .pos-scrollable::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

    /* Alerts */
    .pos-alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 14px; font-weight: 600; border: 1px solid transparent; }
    .pos-alert.error { background: var(--pos-danger-soft); color: var(--pos-danger); border-color: #fecaca; }
    .pos-alert.success { background: var(--pos-success-soft); color: var(--pos-success); border-color: #a7f3d0; }
    .pos-alert ul { margin: 0; padding-inline-start: 18px; }

    /* Layout */
    .pos-grid { display: grid; grid-template-columns: minmax(0, 1fr) 410px; gap: 20px; align-items: start; }
    @media (max-width: 1100px) { .pos-grid { grid-template-columns: 1fr; } }

    .pos-card { background: var(--pos-card); border: 1px solid var(--pos-border); border-radius: var(--pos-radius); box-shadow: 0 1px 3px rgba(17,24,39,.05); }
    .pos-card-head { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--pos-border); }
    .pos-card-head h3 { margin: 0; font-size: 17px; font-weight: 700; }
    .pos-card-body { padding: 20px; }

    /* Products */
    .pos-toolbar { display: flex; flex-direction: column; gap: 14px; margin-bottom: 18px; }
    .pos-search { position: relative; }
    .pos-search input { width: 100%; height: 46px; padding: 0 44px 0 14px; border: 1px solid var(--pos-border); border-radius: 12px; background: var(--pos-bg); font-family: inherit; font-size: 15px; transition: .15s; }
    .pos-search input:focus { outline: none; background: #fff; border-color: var(--pos-primary); box-shadow: 0 0 0 3px rgba(79,70,229,.15); }
    .pos-search svg { position: absolute; top: 50%; right: 14px; transform: translateY(-50%); width: 20px; height: 20px; color: var(--pos-muted); pointer-events: none; }

    .pos-cats { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: thin; }
    .pos-cat { flex: 0 0 auto; padding: 8px 18px; border: 1px solid var(--pos-border); background: #fff; border-radius: 999px; font-family: inherit; font-size: 14px; font-weight: 600; color: var(--pos-muted); cursor: pointer; transition: .15s; }
    .pos-cat:hover { border-color: var(--pos-primary); color: var(--pos-primary); }
    .pos-cat.active { background: var(--pos-primary); border-color: var(--pos-primary); color: #fff; }

    .pos-products { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 16px; }
    .pos-product { display: flex; flex-direction: column; border: 1px solid var(--pos-border); border-radius: 12px; background: #fff; overflow: hidden; cursor: pointer; transition: .18s; position: relative; text-align: center; }
    .pos-product:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(17,24,39,.1); border-color: var(--pos-primary); }
    .pos-product:active { transform: translateY(-1px) scale(.99); }
    .pos-product-img { aspect-ratio: 1 / 1; background: var(--pos-bg); }
    .pos-product-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .pos-stock { position: absolute; top: 10px; right: 10px; padding: 3px 10px; font-size: 12px; font-weight: 700; border-radius: 999px; background: var(--pos-success-soft); color: var(--pos-success); }
    .pos-stock.low { background: var(--pos-warning-soft); color: var(--pos-warning); }
    .pos-product-info { padding: 12px 12px 14px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
    .pos-product-name { font-size: 15px; font-weight: 700; line-height: 1.4; margin: 0; }
    .pos-product-code { font-size: 12px; color: var(--pos-muted); }
    .pos-product-code strong { color: var(--pos-text); }
    .pos-product-price { font-size: 16px; font-weight: 700; color: var(--pos-primary); }
    .pos-product-price small { font-size: 12px; color: var(--pos-muted); font-weight: 600; }
    .pos-add { margin-top: auto; width: 100%; padding: 9px; border: none; border-radius: 9px; background: var(--pos-primary-soft); color: var(--pos-primary); font-family: inherit; font-weight: 700; font-size: 14px; cursor: pointer; transition: .15s; }
    .pos-product:hover .pos-add { background: var(--pos-primary); color: #fff; }
    .pos-empty { grid-column: 1 / -1; text-align: center; padding: 50px 10px; color: var(--pos-muted); }

    /* ===== Cart panel: fixed (sticky) with internal scrollbars ===== */
    .pos-cart { position: sticky; top: 70px; }
    .pos-cart-form {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 90px);   /* fixed height = always fits the screen */
        min-height: 480px;
        overflow: hidden;
    }
    .pos-cart-form .pos-card-head { flex: 0 0 auto; }

    /* Cart items: own scrollbar */
    .pos-cart-list {
        flex: 1 1 auto;
        min-height: 120px;
        max-height: 38%;
        overflow-y: auto;
        padding: 6px 20px;
        border-bottom: 1px solid var(--pos-border);
    }

    /* Totals: always visible under the cart items */
    .pos-sum { flex: 0 0 auto; margin: 0; padding: 12px 20px; background: var(--pos-bg); border-bottom: 1px solid var(--pos-border); }

    /* Customer & payment fields: own scrollbar */
    .pos-fields { flex: 1 1 auto; min-height: 0; overflow-y: auto; padding: 4px 20px 16px; }

    /* Confirm area: always visible at the bottom */
    .pos-foot { flex: 0 0 auto; padding: 12px 20px 14px; border-top: 1px solid var(--pos-border); background: #fff; }

    @media (max-width: 1100px) {
        .pos-cart { position: static; }
        .pos-cart-form { height: auto; overflow: visible; }
        .pos-cart-list { max-height: 320px; flex: none; }
        .pos-fields { overflow: visible; }
    }

    .pos-badge { background: var(--pos-primary-soft); color: var(--pos-primary); padding: 3px 12px; border-radius: 999px; font-weight: 700; font-size: 13px; }
    .pos-cart-empty { text-align: center; padding: 26px 10px; color: var(--pos-muted); }
    .pos-cart-empty svg { width: 44px; height: 44px; opacity: .45; margin-bottom: 6px; }
    .pos-line { display: grid; grid-template-columns: 1fr auto; gap: 6px 10px; padding: 12px 0; border-bottom: 1px dashed var(--pos-border); }
    .pos-line:last-child { border-bottom: none; }
    .pos-line-name { font-weight: 700; font-size: 14px; }
    .pos-line-unit { font-size: 12px; color: var(--pos-muted); }
    .pos-line-total { font-weight: 700; font-size: 15px; text-align: left; }
    .pos-line-actions { grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; }
    .pos-qty { display: inline-flex; align-items: center; border: 1px solid var(--pos-border); border-radius: 10px; overflow: hidden; }
    .pos-qty button { width: 32px; height: 32px; border: none; background: var(--pos-bg); font-size: 18px; font-weight: 700; cursor: pointer; line-height: 1; font-family: inherit; }
    .pos-qty button:hover { background: var(--pos-primary-soft); color: var(--pos-primary); }
    .pos-qty input { width: 48px; height: 32px; border: none; text-align: center; font-weight: 700; font-family: inherit; -moz-appearance: textfield; background: #fff; }
    .pos-qty input::-webkit-outer-spin-button, .pos-qty input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .pos-remove { border: none; background: none; color: var(--pos-danger); font-family: inherit; font-weight: 600; font-size: 13px; cursor: pointer; padding: 6px 8px; border-radius: 8px; }
    .pos-remove:hover { background: var(--pos-danger-soft); }

    .pos-sum-row { display: flex; justify-content: space-between; padding: 3px 0; font-size: 14px; color: var(--pos-muted); font-weight: 600; }
    .pos-sum-row.grand { margin-top: 6px; padding-top: 8px; border-top: 1px solid var(--pos-border); font-size: 19px; color: var(--pos-text); font-weight: 700; }
    .pos-sum-row.grand span:last-child { color: var(--pos-primary); }

    .pos-section-title { font-size: 14px; font-weight: 700; margin: 16px 0 10px; color: var(--pos-muted); }
    .pos-field { margin-bottom: 12px; }
    .pos-field label.lbl { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; color: var(--pos-text); }
    .pos-input { width: 100%; height: 42px; padding: 0 12px; border: 1px solid var(--pos-border); border-radius: 10px; font-family: inherit; font-size: 14px; background: #fff; transition: .15s; }
    .pos-input:focus { outline: none; border-color: var(--pos-primary); box-shadow: 0 0 0 3px rgba(79,70,229,.15); }
    .pos-input:disabled { background: var(--pos-bg); color: var(--pos-muted); }
    .pos-input.invalid { border-color: var(--pos-danger); }
    .pos-error { display: block; color: var(--pos-danger); font-size: 12px; margin-top: 4px; }

    .pos-segment { display: grid; gap: 8px; }
    .pos-segment.two { grid-template-columns: 1fr 1fr; }
    .pos-segment.three { grid-template-columns: repeat(3, 1fr); }
    .pos-segment input { position: absolute; opacity: 0; pointer-events: none; }
    .pos-segment label { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 6px; border: 1.5px solid var(--pos-border); border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; transition: .15s; background: #fff; text-align: center; margin: 0; }
    .pos-segment label:hover { border-color: var(--pos-primary); }
    .pos-segment input:checked + label { border-color: var(--pos-primary); background: var(--pos-primary-soft); color: var(--pos-primary); }
    .pos-segment input:focus-visible + label { box-shadow: 0 0 0 3px rgba(79,70,229,.25); }

    .pos-remaining { display: flex; justify-content: space-between; padding: 10px 14px; background: var(--pos-warning-soft); color: var(--pos-warning); border-radius: 10px; font-weight: 700; margin-bottom: 4px; }

    .pos-submit { width: 100%; height: 48px; border: none; border-radius: 12px; background: var(--pos-success); color: #fff; font-family: inherit; font-size: 16px; font-weight: 700; cursor: pointer; transition: .15s; }
    .pos-submit:hover { background: #047857; }
    .pos-submit:disabled { background: #9ca3af; cursor: not-allowed; }
    .pos-clear { width: 100%; margin-top: 6px; padding: 6px; border: none; background: none; color: var(--pos-muted); font-family: inherit; font-weight: 600; cursor: pointer; border-radius: 10px; }
    .pos-clear:hover { background: var(--pos-bg); color: var(--pos-danger); }
    .d-none-pos { display: none !important; }
</style>
@endsection

@section('page-header')

@endsection

@section('content')
<div class="pos">

    @if(session('error'))
        <div class="pos-alert error">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="pos-alert success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="pos-alert error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="pos-grid">

        <!-- ===================== Products ===================== -->
        <section class="pos-card">
            <div class="pos-card-head">
                <h3>المنتجات</h3>
                <span class="pos-badge" id="visible-count">0 منتج</span>
            </div>
            <div class="pos-card-body">
                <div class="pos-toolbar">
                    <div class="pos-search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" id="product-search" placeholder="بحث عن منتج بالاسم أو الكود..." autocomplete="off">
                    </div>
                    <div class="pos-cats">
                        <button type="button" class="pos-cat category-btn active" data-category="all">الكل</button>
                        @foreach($categories as $category)
                            <button type="button" class="pos-cat category-btn" data-category="{{ $category->category_id }}">
                                {{ $category->category_name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="pos-products" id="product-list">
                    @foreach($products as $product)
                        @if($product->quantity > 0)
                            <article class="pos-product product-item"
                                data-category="{{ $product->category }}"
                                data-name="{{ $product->name }}"
                                data-id="{{ $product->id }}"
                                data-price="{{ $product->price }}"
                                data-stock="{{ $product->quantity }}"
                                tabindex="0" role="button" aria-label="إضافة {{ $product->name }}">
                                <span class="pos-stock {{ $product->quantity <= 5 ? 'low' : '' }}">متوفر: {{ $product->quantity }}</span>
                                <div class="pos-product-img">
                                    <img src="{{ asset('images/products/' . ($product->image ?: 'item.png')) }}" alt="{{ $product->name }}" loading="lazy">
                                </div>
                                <div class="pos-product-info">
                                    <h4 class="pos-product-name">{{ $product->name }}</h4>
                                    <div class="pos-product-code">كود المنتج: <strong>{{ $product->id }}</strong></div>
                                    <div class="pos-product-price">{{ $product->price }} <small>جنيه</small></div>
                                    <button type="button" class="pos-add add-to-order" tabindex="-1">+ إضافة إلى العربة</button>
                                </div>
                            </article>
                        @endif
                    @endforeach
                    <div class="pos-empty d-none-pos" id="no-results">لا توجد منتجات مطابقة للبحث</div>
                </div>
            </div>
        </section>

        <!-- ===================== Cart & Checkout (fixed panel) ===================== -->
        <aside class="pos-cart">
            <form id="checkout-form" method="post" action="{{ route('submit_order') }}" class="pos-card pos-cart-form" novalidate>
                @csrf
                <input type="hidden" id="totalField" name="total" value="">
                <input type="hidden" id="finalTotalField" name="finalTotal" value="">
                <input type="hidden" name="cart" value="">

                <!-- 1) Header (fixed) -->
                <div class="pos-card-head">
                    <h3>عربة الشراء</h3>
                    <span class="pos-badge" id="cart-count">0 عنصر</span>
                </div>

                <!-- 2) Cart items (own scrollbar) -->
                <div class="pos-cart-list pos-scrollable" id="cart-items"></div>

                <!-- 3) Totals (fixed) -->
                <div class="pos-sum">
                    <div class="pos-sum-row"><span>المجموع</span><span id="total">0.00 ج.م</span></div>
                    <div class="pos-sum-row"><span>الخصم</span><span id="deduction-display">0.00 ج.م</span></div>
                    <div class="pos-sum-row grand"><span>الإجمالي بعد الخصم</span><span><span id="final-total">0.00</span> ج.م</span></div>
                </div>

                <!-- 4) Customer & payment (own scrollbar) -->
                <div class="pos-fields pos-scrollable">
                    <div class="pos-section-title">بيانات العميل</div>

                    <div class="pos-field">
                        <label class="lbl" for="datetime">التاريخ والوقت</label>
                        <input class="pos-input" type="datetime-local" id="datetime" name="datetime">
                    </div>

                    <div class="pos-field">
                        <div class="pos-segment three">
                            <input type="radio" id="no-customer" name="customer_type" value="none" checked>
                            <label for="no-customer">بدون بيانات</label>
                            <input type="radio" id="new-customer" name="customer_type" value="new">
                            <label for="new-customer">عميل جديد</label>
                            <input type="radio" id="existing-customer" name="customer_type" value="existing">
                            <label for="existing-customer">عميل دائم</label>
                        </div>
                        <small class="text-muted">بيانات العميل اختيارية للبيع النقدي والفيزا. تسجيل عميل جديد ينشئ له صفحة دائمة، والبيع الآجل يتطلب اختيار عميل.</small>
                    </div>

                    <div class="pos-field d-none-pos" id="existing-customer-container">
                        <label class="lbl" for="existing-phone">اختر العميل</label>
                        <select id="existing-phone" class="pos-input select2" style="width:100%">
                            <option value="">-- اختر العميل --</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" data-name="{{ $customer->name }}" data-phone="{{ $customer->contact_info }}">
                                    {{ $customer->contact_info ?: 'بدون هاتف' }} - {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pos-field" id="name-container">
                        <label class="lbl" for="name">اسم العميل</label>
                        <input type="text" id="name" name="customer[name]" class="pos-input" placeholder="ادخل اسم العميل" disabled>
                        <span id="name-validation" class="pos-error"></span>
                    </div>

                    <div class="pos-field" id="phone-container">
                        <label class="lbl" for="phone">الموبايل</label>
                        <input type="tel" id="phone" name="customer[phone]" class="pos-input" placeholder="01xxxxxxxxx" dir="rtl" inputmode="numeric" disabled>
                        <span id="phone-validation" class="pos-error"></span>
                    </div>

                    <div class="pos-section-title">الدفع</div>

                    <div class="pos-field">
                        <label class="lbl" for="deduction">الخصم (اختياري)</label>
                        <input type="number" id="deduction" name="deduction" class="pos-input" placeholder="0.00" min="0" step="any">
                        <span id="deduction-validation" class="pos-error"></span>
                    </div>

                    <div class="pos-field">
                        <label class="lbl">طريقة الدفع</label>
                        <div class="pos-segment three">
                            <input type="radio" name="paymentMethod" value="cash" id="cash" required checked>
                            <label for="cash">نقدي</label>
                            <input type="radio" name="paymentMethod" value="visa" id="visa">
                            <label for="visa">فيزا</label>
                            <input type="radio" name="paymentMethod" value="credit" id="credit">
                            <label for="credit">آجل</label>
                        </div>
                    </div>

                    <div class="pos-field d-none-pos" id="credit-input">
                        <label class="lbl" for="pay-amount">المبلغ المدفوع</label>
                        <input type="number" id="pay-amount" name="payAmount" class="pos-input" placeholder="0.00" min="0" step="any" disabled>
                        <span id="pay-amount-validation" class="pos-error"></span>
                    </div>

                    <div class="pos-remaining d-none-pos" id="remaining-box">
                        <span>المبلغ المتبقي</span>
                        <span><span id="remaining-amount-display">0.00</span> ج.م</span>
                    </div>
                </div>

                <!-- 5) Confirm (fixed at the bottom) -->
                <div class="pos-foot">
                    <span id="required-fields-validation" class="pos-error" style="margin:0 0 8px"></span>
                    <button type="submit" class="pos-submit" id="submit-btn">تأكيد عملية البيع</button>
                    <button type="button" class="pos-clear" id="clear-cart">تفريغ العربة</button>
                </div>
            </form>
        </aside>
    </div>
</div>
@endsection

@section('js')
<script src="{{ URL::asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
(function () {
    'use strict';

    const $ = (id) => document.getElementById(id);
    const money = (n) => (Number(n) || 0).toFixed(2);
    const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    let cart = [];
    let activeCategory = 'all';

    /* ---------- Date default ---------- */
    (function setNow() {
        const d = new Date();
        d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
        $('datetime').value = d.toISOString().slice(0, 16);
    })();

    /* ---------- Product filtering ---------- */
    const products = Array.from(document.querySelectorAll('.product-item'));

    function applyFilters() {
        const q = $('product-search').value.trim().toLowerCase();
        let visible = 0;
        products.forEach((p) => {
            const matchCat = activeCategory === 'all' || p.dataset.category === activeCategory;
            const matchQ = !q || p.dataset.name.toLowerCase().includes(q) || p.dataset.id.toLowerCase().includes(q);
            const show = matchCat && matchQ;
            p.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        $('visible-count').textContent = visible + ' منتج';
        $('no-results').classList.toggle('d-none-pos', visible !== 0);
    }

    document.querySelectorAll('.category-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.category-btn').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            activeCategory = btn.dataset.category;
            applyFilters();
        });
    });
    $('product-search').addEventListener('input', applyFilters);
    applyFilters();

    /* ---------- Cart ---------- */
    function addToCart(el) {
        const id = el.dataset.id;
        const stock = parseInt(el.dataset.stock, 10) || 0;
        const price = parseFloat(el.dataset.price) || 0;
        const existing = cart.find((i) => i.id === id);

        if (existing) {
            if (existing.quantity >= stock) return flashError('الكمية المتاحة من "' + el.dataset.name + '" هي ' + stock + ' فقط.');
            existing.quantity++;
        } else {
            cart.push({ id, product: el.dataset.name, price, quantity: 1, stock, total: price });
        }
        render(true);
    }

    function flashError(msg) {
        const box = $('required-fields-validation');
        box.textContent = msg;
        setTimeout(() => { if (box.textContent === msg) box.textContent = ''; }, 3500);
    }

    products.forEach((p) => {
        p.addEventListener('click', () => addToCart(p));
        p.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); addToCart(p); }
        });
    });

    function setQty(index, qty) {
        const item = cart[index];
        if (!item) return;
        qty = parseInt(qty, 10);
        if (isNaN(qty) || qty < 1) qty = 1;
        if (qty > item.stock) { flashError('الكمية المتاحة من "' + item.product + '" هي ' + item.stock + ' فقط.'); qty = item.stock; }
        item.quantity = qty;
        render();
    }

    $('cart-items').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-act]');
        if (!btn) return;
        const i = parseInt(btn.dataset.index, 10);
        if (btn.dataset.act === 'inc') setQty(i, cart[i].quantity + 1);
        if (btn.dataset.act === 'dec') setQty(i, cart[i].quantity - 1);
        if (btn.dataset.act === 'remove') { cart.splice(i, 1); render(); }
    });
    $('cart-items').addEventListener('change', (e) => {
        if (e.target.classList.contains('quantity-input')) setQty(parseInt(e.target.dataset.index, 10), e.target.value);
    });

    $('clear-cart').addEventListener('click', () => {
        if (cart.length && confirm('هل تريد تفريغ العربة؟')) { cart = []; render(); }
    });

    const cartTotal = () => cart.reduce((s, i) => s + i.total, 0);
    const deductionValue = () => parseFloat($('deduction').value) || 0;
    const finalTotalValue = () => Math.max(cartTotal() - deductionValue(), 0);

    // scrollToEnd = true -> jump to the newest item after adding a product
    function render(scrollToEnd) {
        cart.forEach((i) => { i.total = i.price * i.quantity; });

        const list = $('cart-items');
        const prevScroll = list.scrollTop;

        if (!cart.length) {
            list.innerHTML = '<div class="pos-cart-empty">' +
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>' +
                '<div>عربة الشراء فارغة</div></div>';
        } else {
            list.innerHTML = cart.map((item, i) => `
                <div class="pos-line">
                    <div>
                        <div class="pos-line-name">${esc(item.product)}</div>
                        <div class="pos-line-unit">${money(item.price)} ج.م للوحدة</div>
                    </div>
                    <div class="pos-line-total">${money(item.total)} ج.م</div>
                    <div class="pos-line-actions">
                        <div class="pos-qty">
                            <button type="button" data-act="dec" data-index="${i}" aria-label="إنقاص">−</button>
                            <input type="number" class="quantity-input" data-index="${i}" value="${item.quantity}" min="1" max="${item.stock}">
                            <button type="button" data-act="inc" data-index="${i}" aria-label="زيادة">+</button>
                        </div>
                        <button type="button" class="pos-remove" data-act="remove" data-index="${i}">حذف</button>
                    </div>
                </div>`).join('');
        }

        list.scrollTop = scrollToEnd ? list.scrollHeight : prevScroll;

        const count = cart.reduce((s, i) => s + i.quantity, 0);
        $('cart-count').textContent = count + ' عنصر';
        syncTotals();
    }

    function syncTotals() {
        const total = cartTotal();
        const ded = deductionValue();
        const final = finalTotalValue();

        $('total').textContent = money(total) + ' ج.م';
        $('deduction-display').textContent = money(ded) + ' ج.م';
        $('final-total').textContent = money(final);

        $('totalField').value = money(total);
        $('finalTotalField').value = money(final);
        document.querySelector('input[name="cart"]').value = JSON.stringify(
            cart.map(({ id, product, price, quantity, total }) => ({ id, product, price, quantity, total }))
        );
        calculateRemaining();
    }

    /* ---------- Deduction / payment ---------- */
    $('deduction').addEventListener('input', syncTotals);
    $('pay-amount').addEventListener('input', calculateRemaining);

    function isCredit() { return $('credit').checked; }

    function toggleCreditInput() {
        const credit = isCredit();
        $('credit-input').classList.toggle('d-none-pos', !credit);
        $('remaining-box').classList.toggle('d-none-pos', !credit);
        $('pay-amount').disabled = !credit;
        $('pay-amount').required = credit;
        if (!credit) $('pay-amount').value = '';
        calculateRemaining();
    }
    document.querySelectorAll('input[name="paymentMethod"]').forEach((r) => r.addEventListener('change', toggleCreditInput));

    function calculateRemaining() {
        const paid = isCredit() ? (parseFloat($('pay-amount').value) || 0) : 0;
        const remaining = isCredit() ? Math.max(finalTotalValue() - paid, 0) : 0;
        $('remaining-amount-display').textContent = money(remaining);
    }

    /* ---------- Searchable existing customer select ---------- */
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
        window.jQuery('#existing-phone').select2({
            placeholder: '-- اختر العميل --',
            allowClear: true,
            language: {
                noResults: () => 'لا توجد نتائج',
                searching: () => 'جارٍ البحث...'
            },
            width: '100%'
        });
    }

    /* ---------- Customer type ---------- */
    const nameInput = $('name'), phoneInput = $('phone'), existingSelect = $('existing-phone');

    function setCustomerMode(mode, clearSelection = true) {
        const isNew = mode === 'new';
        const isExisting = mode === 'existing';
        $('name-container').classList.toggle('d-none-pos', !isNew);
        $('phone-container').classList.toggle('d-none-pos', !isNew);
        $('existing-customer-container').classList.toggle('d-none-pos', !isExisting);

        nameInput.disabled = !isNew;
        nameInput.required = isNew;
        phoneInput.disabled = !isNew;
        phoneInput.required = false;
        existingSelect.disabled = !isExisting;
        existingSelect.required = isExisting;
        existingSelect.name = isExisting ? 'customer_id' : '';

        if (clearSelection) {
            nameInput.value = '';
            phoneInput.value = '';
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery('#existing-phone').val('').trigger('change');
            } else {
                existingSelect.value = '';
            }
        }
    }

    ['no-customer', 'new-customer', 'existing-customer'].forEach((id) => {
        $(id).addEventListener('change', (event) => setCustomerMode(event.target.value));
    });
    setCustomerMode('none', false);

    existingSelect.addEventListener('change', () => {
        const opt = existingSelect.selectedOptions[0];
        if (opt && opt.value !== '') {
            nameInput.value = opt.dataset.name || '';
            phoneInput.value = opt.dataset.phone || '';
        } else {
            nameInput.value = ''; phoneInput.value = '';
        }
    });

    /* ---------- Validation & submit ---------- */
    const nameRegex = /^[A-Za-z\u0600-\u06FF\s0-9]+$/;
    const phoneRegex = /^01[0-9]{9}$/;

    function setErr(inputId, errId, msg) {
        $(errId).textContent = msg || '';
        const el = $(inputId);
        if (el) el.classList.toggle('invalid', !!msg);
        return !msg;
    }

    $('checkout-form').addEventListener('submit', (e) => {
        const existing = $('existing-customer').checked;
        const newCustomer = $('new-customer').checked;
        let ok = true;

        ['name-validation', 'phone-validation', 'deduction-validation', 'pay-amount-validation', 'required-fields-validation']
            .forEach((id) => ($(id).textContent = ''));

        if (!cart.length) { flashError('أضف منتجًا واحدًا على الأقل إلى العربة.'); ok = false; }

        const nameVal = nameInput.value.trim();
        if (newCustomer && (!nameVal || !nameRegex.test(nameVal))) {
            ok = setErr('name', 'name-validation', 'اسم العميل غير صالح.') && ok;
        } else {
            setErr('name', 'name-validation', '');
        }

        if (isCredit() && !existing && !newCustomer) {
            $('required-fields-validation').textContent = 'البيع الآجل يتطلب تسجيل عميل أو اختيار عميل دائم لحفظ المديونية.';
            ok = false;
        }

        if (existing) {
            if (!existingSelect.value) { $('phone-validation').textContent = 'يرجى اختيار العميل.'; ok = false; }
            else setErr('phone', 'phone-validation', '');
        } else if (newCustomer) {
            const phoneVal = phoneInput.value.trim();
            if (phoneVal && !phoneRegex.test(phoneVal)) {
                ok = setErr('phone', 'phone-validation', 'رقم الموبايل يجب أن يبدأ بـ 01 وطوله 11 رقمًا.') && ok;
            } else setErr('phone', 'phone-validation', '');
        }

        if (deductionValue() > cartTotal()) ok = setErr('deduction', 'deduction-validation', 'الخصم لا يمكن أن يكون أكبر من الإجمالي.') && ok;
        else setErr('deduction', 'deduction-validation', '');

        if (isCredit()) {
            const paid = parseFloat($('pay-amount').value);
            if (isNaN(paid) || paid < 0) ok = setErr('pay-amount', 'pay-amount-validation', 'أدخل المبلغ المدفوع.') && ok;
            else if (paid > finalTotalValue()) ok = setErr('pay-amount', 'pay-amount-validation', 'المبلغ المدفوع أكبر من الإجمالي بعد الخصم.') && ok;
            else setErr('pay-amount', 'pay-amount-validation', '');
        }

        if (!ok) {
            e.preventDefault();
            // bring the first error into view inside the scrollable fields area
            const firstErr = document.querySelector('.pos-fields .pos-error:not(:empty), .pos-fields .invalid');
            if (firstErr) firstErr.scrollIntoView({ block: 'center', behavior: 'smooth' });
            return;
        }

        syncTotals();
        $('submit-btn').disabled = true;
        $('submit-btn').textContent = 'جاري الحفظ...';
    });

    render();
    toggleCreditInput();
})();
</script>

@if (session('success') && isset($invoiceId))
    <script>
        // Open the invoice in a popup after a successful sale
        window.addEventListener('load', function () {
            const url = "{{ route('invoice.show', ['id' => ':id']) }}".replace(':id', {{ $invoiceId }});
            const w = window.innerWidth, h = window.innerHeight;
            const pw = w * 0.5, left = (w - pw) / 2;
            window.open(url, '_blank', `width=${pw},height=${h},left=${left},top=0,scrollbars=yes`);
        });
    </script>
@endif
@endsection
