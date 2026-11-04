@extends('layouts.master2')
<title> معرض أيه - فاتورة المبيعات </title>
<style>
    input[type="number"] {
        -moz-appearance: textfield;
    }

    td.input {
        border: none;
        width: 50px;
        /* Add a unit for width */
    }
</style>

@section('css')
@endsection
@section('page-header')

    <!-- breadcrumb -->
    <div class="breadcrumb-header justify-content-between">
        <div class="my-auto">
            <div class="d-flex">
                <h4 class="content-title mb-0 my-auto">المبيعات</h4><span class="text-muted mt-1 tx-13 mr-2 mb-0">/ فاتورة
                    المبيعات</span>
            </div>
        </div>

    </div>
    <!-- breadcrumb -->
@endsection
@section('content')

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif



    <div id="error_message" style="display: none; color: red;"></div>
    <!-- Modal for Success Message -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="successModalLabel">عملية بيع ناجحة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <p>تمت عملية البيع بنجاح!</p>
                    <button id="printInvoice" class="btn btn-primary">طباعة الفاتورة</button>
                </div>
            </div>
        </div>
    </div>

    <!-- row -->
    <div class="row">
        <!-- Order Summary Section -->
        <div class="col-md-4">
            <!-- Begin Form -->
            <input type="hidden" id="hidden_order_items" name="order_items" />
            <div id="order_items_container"></div>
            <div class="card">
                <div class="card-header bg-success text-white">
                    <div class="row">
                        <div class="col-md-6">
                            <b>عربة الشراء</b>
                        </div>

                    </div>
                </div>

                <div class="card-body">
                    <!-- Cart Table -->
                    <div class="cart mb-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>اسم المنتج</th>
                                    <th>الكمية</th>
                                    <th>سعر الوحدة</th>
                                    <th>الإجمالي</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items">
                                <tr>
                                    <td colspan="5">عربة الشراء فارغة</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end" dir="rtl"><strong>الإجمالي</strong></td>
                                    <td colspan="2"><span id="total">0</span></td>
                                </tr>
                            </tfoot>
                        </table>

                    </div>

                    <!-- Customer Details & Payment -->
                    <form id="checkout-form" method="post" action="{{ route('submit_order') }}">
                        @csrf
                        <!-- Cart items table will be dynamically filled in JavaScript -->
                        <input type="hidden" id="totalField" name="total" value="">
                        <input type="hidden" id="finalTotalField" name="finalTotal" value="">
                        <input type="hidden" name="cart" value="">

                        <h2>بيانات العميل</h2>

                        <label for="datetime">Choose date & time:</label>
                        <input class="form-control" type="datetime-local" id="datetime" name="datetime">

                        <!-- اختيار نوع العميل -->
                        <div class="mb-3">
                            <label class="form-label">نوع العميل</label>
                            <div>
                                <input type="radio" id="new-customer" name="customer_type" value="new" checked>
                                <label for="new-customer">عميل جديد</label>

                                <input type="radio" id="existing-customer" name="customer_type" value="existing">
                                <label for="existing-customer">عميل دائم</label>
                            </div>
                        </div>

                        <!-- اسم العميل -->
                        <div class="mb-3">
                            <label for="name" class="form-label">اسم العميل</label>
                            <input type="text" id="name" name="customer[name]" class="form-control"
                                placeholder="ادخل اسم العميل" required>
                            <span id="name-validation" class="text-danger"></span>
                        </div>

                        <!-- رقم الموبايل -->
                        <div class="mb-3" id="phone-container">
                            <label for="phone" class="form-label">الموبايل</label>
                            <input type="tel" id="phone" name="customer[phone]" class="form-control" placeholder="الموبايل"
                                dir="rtl" >
                            <span id="phone-validation" class="text-danger"></span>
                        </div>

                        <!-- القائمة المنسدلة لرقم الهاتف (لعملاء دائمين) -->
                        <div class="mb-3 d-none" id="existing-customer-container">
                            <label for="existing-phone" class="form-label">اختر رقم العميل</label>
                            <select id="existing-phone" class="form-control">
                                <option value="">-- اختر رقم الهاتف --</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->contact_info }}" data-name="{{ $customer->name }}">
                                        {{ $customer->contact_info }} - {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Deduction Input -->
                        <div class="mb-3">
                            <label for="deduction" class="form-label">الخصم ( اختياري )</label>
                            <input type="number" id="deduction" name="deduction" class="form-control"
                                placeholder="ادخل مبلغ الخصم" min="0" oninput="updateFinalTotal()">
                            <span id="deduction-validation" class="text-danger"></span>
                        </div>

                        <p><strong>إجمالي الفاتورة بعد الخصم : <span id="final-total">0</span></strong> ج.م </p>

                        <!-- Payment Methods -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Payment Method</label>
                            <div class="d-flex gap-4 align-items-center">
                                <!-- Cash Option -->
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="paymentMethod" value="cash" id="cash"
                                        required onclick="toggleCreditInput(false)">
                                    <label class="form-check-label " for="cash">Cash</label>
                                </div>
                                <!-- Visa Option -->
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="paymentMethod" value="visa" id="visa"
                                        onclick="toggleCreditInput(false)">
                                    <label class="form-check-label " for="visa">Visa</label>
                                </div>
                                <!-- Credit Option -->
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="paymentMethod" value="credit"
                                        id="credit" onclick="toggleCreditInput(true)">
                                    <label class="form-check-label " for="credit">Credit</label>
                                </div>
                            </div>
                        </div>


                        <div class="mb-3" id="credit-input" style="display: none;">
                            <label for="pay-amount" class="form-label">المبلغ المدفوع</label>
                            <input type="number" id="pay-amount" name="payAmount" class="form-control"
                                placeholder="Enter payment amount" min="0" oninput="calculateRemaining()">
                            <span id="pay-amount-validation" class="text-danger"></span>
                        </div>
                        <p><strong>المبلغ المتبقي : <span id="remaining-amount-display">0</span></strong> ج.م </p>
                        <span id="required-fields-validation" class="text-danger"></span>
                        <button type="submit" class="btn btn-success w-100">تأكيد</button>

                    </form>


                </div>

            </div>
        </div>


        <!-- Item Section -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <b>المنتجات</b>
                </div>

                <div class="card-body">
                    <!-- Search Input -->
                    <div class="mb-3">
                        <input type="text" id="product-search" class="form-control"
                            placeholder="بحث عن منتج بالاسم أو الكود...">
                    </div>

                    <!-- Category Buttons -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary mb-2 category-btn"
                            data-category="all">All</button>
                        @foreach($categories as $category)
                            <button type="button" class="btn btn-outline-primary mb-2 category-btn"
                                data-category="{{ $category->category_id }}">
                                {{ $category->category_name }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Product Cards -->
                    <div class="row" id="product-list">
                        @foreach($products as $product)
                            @if($product->quantity > 0) <!-- Only display products with a quantity greater than 0 -->
                                <div class="col-lg-3 col-md-4 col-sm-6 col-sm-12 mb-3 product-item"
                                    data-category="{{ $product->category }}" data-name="{{ $product->name }}"
                                    data-id="{{ $product->id }}">
                                    <div class="card" style="align-items: center;">
                                        <img src="{{ asset('images/products/' . ($product->image ?: 'item.png')) }}"
                                            class="card-img-top" alt="{{ $product->name }}"
                                            style="width: 150px; height: 150px; object-fit: cover;">

                                        <div class="card-body" style="align-items: center;">
                                            <h5 class="card-title">{{ $product->name }}</h5>
                                            <p class="card-text">السعر: {{ $product->price }} جنيه</p>
                                            <p class="card-text" style="color: red; background-color: yellow; padding: 5px;">الكمية
                                                المتوفرة من المنتج ({{ $product->quantity }}) </p>

                                            <button class="btn btn-sm btn-primary add-to-order" data-name="{{ $product->name }}"
                                                data-price="{{ $product->price }}" data-id="{{ $product->id }}">
                                                إضافة الي عربة الشراء
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- row closed -->
    </div>
    <!-- Container closed -->
    </div>
    <!-- main-content closed -->

@endsection
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categoryButtons = document.querySelectorAll('.category-btn');
            const searchInput = document.getElementById('product-search');
            const products = document.querySelectorAll('.product-item');

            // Category filtering
            categoryButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const category = this.getAttribute('data-category');

                    // Show/Hide products based on selected category
                    products.forEach(product => {
                        if (category === 'all' || product.getAttribute('data-category') === category) {
                            product.style.display = 'block';
                        } else {
                            product.style.display = 'none';
                        }
                    });
                });
            });

            // Search filtering
            searchInput.addEventListener('input', function () {
                const query = searchInput.value.trim().toLowerCase();

                products.forEach(product => {
                    const productName = product.getAttribute('data-name').toLowerCase();
                    const productId = product.getAttribute('data-id').toLowerCase();

                    // Check if the query matches product name or ID
                    if (productName.includes(query) || productId.includes(query)) {
                        product.style.display = 'block'; // Show matching products
                    } else {
                        product.style.display = 'none'; // Hide non-matching products
                    }
                });
            });
        });
    </script>


    <script>

        let cart = [];
        let total = 0;

        function addToCart(id, product, price) {
            const existingProduct = cart.find(item => item.id === id);
            if (existingProduct) {
                existingProduct.quantity++;
                existingProduct.total = existingProduct.quantity * existingProduct.price;
            } else {
                cart.push({ id, product, price, quantity: 1, total: price });
            }
            total += price;
            updateCart();
        }

        function updateCart() {
            const cartItemsDiv = document.getElementById('cart-items');
            cartItemsDiv.innerHTML = cart.length === 0
                ? '<tr><td colspan="5">Your cart is empty.</td></tr>'
                : cart.map((item, index) => {
                    return `
                        <tr>
                            <td>${item.product}</td>
                            <td><input type="number" class="form-control quantity-input" value="${item.quantity}" min="1" onchange="updateQuantity(${index})" style="border: none; width: 50px; padding: 0px;" required></td>
                            <td><input type="hidden" value="${item.price}" required>${item.price} ج.م</td>
                            <td><input type="hidden" value="${item.total.toFixed(2)}" required>${item.total.toFixed(2)} ج.م</td>
                            <td><input type="hidden" value="${item.id}" required><button class="btn btn-danger btn-sm" onclick="removeFromCart(${index})">Remove</button></td>
                        </tr>
                    `;
                }).join('');

            // Update final total and cart total
            const finalTotal = cart.reduce((sum, item) => sum + item.total, 0);
            document.getElementById("finalTotalField").value = finalTotal.toFixed(2);
            document.getElementById("totalField").value = finalTotal.toFixed(2);
            document.querySelector('input[name="cart"]').value = JSON.stringify(cart);

            updateFinalTotal();
            updateCartTotal();
        }

        function removeFromCart(index) {
            total -= cart[index].total;
            cart.splice(index, 1);
            updateCart();
        }

        function updateQuantity(index) {
            const newQuantity = document.querySelectorAll('.quantity-input')[index].value;
            if (newQuantity >= 1) {
                const product = cart[index];
                const oldTotal = product.total;
                const newTotal = product.price * newQuantity;
                product.quantity = newQuantity;
                product.total = newTotal;
                total += (newTotal - oldTotal);
                updateCart();
            }
        }

        function updateFinalTotal() {
            const deduction = parseFloat(document.getElementById('deduction').value) || 0;
            const finalTotal = total - deduction;
            document.getElementById('final-total').textContent = finalTotal.toFixed(2);
            calculateRemaining();
        }

        function toggleCreditInput(isCredit) {
            const creditInput = document.getElementById('credit-input');
            const payAmount = document.getElementById('pay-amount');
            const remainingAmountDisplay = document.getElementById('remaining-amount-display');

            creditInput.style.display = isCredit ? 'block' : 'none';
            payAmount.disabled = !isCredit;
            payAmount.required = isCredit;
            payAmount.value = isCredit ? payAmount.value : '';
            remainingAmountDisplay.textContent = isCredit ? remainingAmountDisplay.textContent : '0';
        }

        function updateCartTotal() {
            const cartTotal = cart.reduce((acc, item) => acc + item.total, 0);
            document.getElementById('total').textContent = `ج.م ${cartTotal.toFixed(2)}`;
        }

        function calculateRemaining() {
            const finalTotal = parseFloat(document.getElementById('final-total').textContent) || 0;
            const payAmount = parseFloat(document.getElementById('pay-amount').value) || 0;
            const remainingAmount = finalTotal - payAmount;
            document.getElementById('remaining-amount-display').textContent = remainingAmount > 0 ? remainingAmount.toFixed(2) : '0';
        }

        function clearFormAndRefresh() {
            document.getElementById('checkout-form').reset();
            document.getElementById('final-total').textContent = '0';
            document.getElementById('remaining-amount-display').textContent = '0';
            document.getElementById('deduction').value = '';
            document.getElementById('pay-amount').value = '';
            toggleCreditInput(false);
            cart.length = 0;
            location.reload();
        }

        function submitOrder() {
            const name = document.getElementById('name')?.value;
            const phone = document.getElementById('phone')?.value;
            const deduction = parseFloat(document.getElementById('deduction')?.value) || 0;
            const paymentMethod = document.querySelector('input[name="payment-method"]:checked')?.value;
            const payAmount = parseFloat(document.getElementById('pay-amount')?.value) || 0;

            // Validation checks
            const nameRegex = /^[A-Za-z\u0600-\u06FF\s0-9]+$/;
            const phoneRegex = /^01[0-9]{9}$/;
            const nameValidation = document.getElementById('name-validation');
            const phoneValidation = document.getElementById('phone-validation');
            const deductionValidation = document.getElementById('deduction-validation');
            const payAmountValidation = document.getElementById('pay-amount-validation');
            const requiredFieldsValidation = document.getElementById('required-fields-validation');

            if (!name || !nameRegex.test(name)) {
                nameValidation.textContent = 'اسم العميل غير صالح.';
                return;
            }
            if (!phone || !phoneRegex.test(phone)) {
                phoneValidation.textContent = 'رقم الموبايل يجب أن يبدأ بـ 01 وطوله 11 حرف.';
                return;
            }
            if (deduction > total) {
                deductionValidation.textContent = 'الخصم لا يمكن أن يكون أكبر من الإجمالي.';
                return;
            }
            if (paymentMethod === 'credit' && payAmount > (total - deduction)) {
                payAmountValidation.textContent = 'المبلغ المدفوع لا يمكن أن يكون أكبر من الإجمالي بعد الخصم.';
                return;
            }
            if (!name || !phone || !paymentMethod) {
                requiredFieldsValidation.textContent = 'يرجى ملء جميع الحقول المطلوبة.';
                return;
            }

            // Prepare order data
            const finalTotal = total - deduction;
            const remainingAmount = paymentMethod === 'credit' ? finalTotal - payAmount : 0;

            const order = {
                customer: { name, phone },
                cart,
                total,
                deduction,
                finalTotal,
                paymentMethod,
                payAmount,
                remainingAmount
            };

            console.log('Final Order Details:', JSON.stringify(order, null, 2));

            fetch('{{ route("submit_order") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(order)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order submitted successfully!');
                        clearFormAndRefresh();
                    } else {
                        alert('Error submitting order: ' + data.message);
                    }
                })
                .catch(error => console.error('Error:', error));

            clearFormAndRefresh();
        }

        // Event listener for adding products to cart
        document.querySelectorAll('.add-to-order').forEach(button => {
            button.addEventListener('click', (e) => {
                const { id, name, price } = e.target.dataset;
                addToCart(id, name, parseFloat(price));
            });
        });

    </script>

    @if (session('success'))
        <script>
            // Check if order submission was successful
            window.onload = function () {
                const invoiceId = {{ $invoiceId }}; // Assuming you pass the $invoiceId from the controller
                const url = "{{ route('invoice.show', ['id' => ':id']) }}".replace(':id', invoiceId);

                const screenWidth = window.innerWidth;
                const screenHeight = window.innerHeight;
                const popupWidth = screenWidth * 0.5;
                const popupHeight = screenHeight;

                const left = (screenWidth - popupWidth) / 2;

                // Open the invoice in a popup window
                window.open(url, '_blank', `width=${popupWidth},height=${popupHeight},left=${left},top=0,scrollbars=yes`);
            };
        </script>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const newCustomerRadio = document.getElementById("new-customer");
            const existingCustomerRadio = document.getElementById("existing-customer");
            const phoneContainer = document.getElementById("phone-container");
            const existingCustomerContainer = document.getElementById("existing-customer-container");
            const phoneInput = document.getElementById("phone");
            const nameInput = document.getElementById("name");
            const existingPhoneSelect = document.getElementById("existing-phone");
            const form = document.querySelector("form"); // تحديد الفورم

            // عند اختيار "عميل دائم"
            existingCustomerRadio.addEventListener("change", function () {
                phoneContainer.classList.add("d-none");
                existingCustomerContainer.classList.remove("d-none");
                phoneInput.removeAttribute("required");
                existingPhoneSelect.setAttribute("required", "required");

                // تعطيل إدخال اسم العميل
                nameInput.setAttribute("disabled", "disabled");
                nameInput.value = "";
                phoneInput.value = "";
            });

            // عند اختيار "عميل جديد"
            newCustomerRadio.addEventListener("change", function () {
                phoneContainer.classList.remove("d-none");
                existingCustomerContainer.classList.add("d-none");
                phoneInput.setAttribute("required", "required");
                existingPhoneSelect.removeAttribute("required");

                // تفعيل إدخال اسم العميل
                nameInput.removeAttribute("disabled");
                nameInput.setAttribute("required", "required");
                nameInput.value = "";
                phoneInput.value = "";
            });

            // عند اختيار عميل دائم من القائمة المنسدلة
            existingPhoneSelect.addEventListener("change", function () {
                const selectedOption = existingPhoneSelect.selectedOptions[0];
                if (selectedOption.value !== "") {
                    nameInput.value = selectedOption.dataset.name || "";
                    phoneInput.value = selectedOption.value;
                } else {
                    nameInput.value = "";
                    phoneInput.value = "";
                }
            });

            // قبل إرسال النموذج، إزالة `disabled` من اسم العميل إذا كان عميل دائم
            form.addEventListener("submit", function () {
                if (existingCustomerRadio.checked) {
                    nameInput.removeAttribute("disabled");
                }
            });
        });
    </script>


@endsection