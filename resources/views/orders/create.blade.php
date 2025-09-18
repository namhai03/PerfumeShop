@extends('layouts.app')

@section('title', 'Tạo đơn hàng mới - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title">Tạo đơn hàng mới</h1>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('orders.index') }}" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
            <strong>Có lỗi xảy ra:</strong>
            <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $productsData = $products->map(function($p){
            return [
                'id' => $p->id,
                'variants' => $p->variants->map(function($v){
                    return [
                        'id' => $v->id,
                        'sku' => $v->sku,
                        'volume_ml' => $v->volume_ml,
                        'selling_price' => $v->selling_price,
                        'stock' => $v->stock,
                    ];
                })->toArray(),
            ];
        })->values();
    @endphp

    <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
        @csrf
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
            <!-- Thông tin cơ bản -->
            <div class="card">
                <div class="card-header">
                    <h3>Thông tin cơ bản</h3>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tên khách hàng <span style="color: #e53e3e;">*</span></label>
                    <input type="text" name="customer_name" id="customer_name" class="form-control" 
                           value="{{ old('customer_name') }}" placeholder="Nhập tên khách hàng" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nhóm khách hàng</label>
                    <select name="customer_group_id" id="customer_group_id" class="form-control">
                        <option value="">-- Không --</option>
                        @isset($groups)
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" data-rate="{{ $g->discount_rate ?? 0 }}" data-min="{{ $g->min_order_amount ?? 0 }}" data-max="{{ $g->max_discount_amount ?? '' }}">{{ $g->name }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>


                <div class="form-group">
                    <label class="form-label">Trạng thái <span style="color: #e53e3e;">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Đơn nháp</option>
                        <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Đơn hàng đã xác nhận</option>
                        <option value="processing" {{ old('status') == 'processing' ? 'selected' : '' }}>Đơn hàng đang xử lý</option>
                        <option value="shipping" {{ old('status') == 'shipping' ? 'selected' : '' }}>Đơn hàng đang giao</option>
                        <option value="delivered" {{ old('status') == 'delivered' ? 'selected' : '' }}>Đơn hàng đã giao thành công</option>
                        <option value="failed" {{ old('status') == 'failed' ? 'selected' : '' }}>Đơn hàng thất bại</option>
                        <option value="returned" {{ old('status') == 'returned' ? 'selected' : '' }}>Đơn hàng hoàn trả</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Ngày tạo đơn <span style="color: #e53e3e;">*</span></label>
                    <input type="date" name="order_date" id="order_date" class="form-control" 
                           value="{{ old('order_date', date('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Ngày giao hàng</label>
                    <input type="date" name="delivery_date" id="delivery_date" class="form-control" 
                           value="{{ old('delivery_date') }}">
                </div>
            </div>

            <!-- Thông tin giao hàng -->
            <div class="card">
                <div class="card-header">
                    <h3>Thông tin giao hàng</h3>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" id="phone" class="form-control" 
                           value="{{ old('phone') }}" placeholder="Nhập số điện thoại">
                </div>

                <div class="form-group">
                    <label class="form-label">Địa chỉ giao hàng</label>
                    <textarea name="delivery_address" id="delivery_address" class="form-control" 
                              rows="3" placeholder="Nhập địa chỉ giao hàng">{{ old('delivery_address') }}</textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label class="form-label">Phường/Xã</label>
                        <input type="text" name="ward" id="ward" class="form-control" value="{{ old('ward') }}" placeholder="VD: Phường 1">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Thành phố/Tỉnh</label>
                        <input type="text" name="city" id="city" class="form-control" value="{{ old('city') }}" placeholder="VD: TP.HCM">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Phương thức thanh toán</label>
                    <select name="payment_method" id="payment_method" class="form-control">
                        <option value="">-- Chọn phương thức --</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản</option>
                        <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Thẻ tín dụng</option>
                        <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Khác</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="notes" id="notes" class="form-control" 
                              rows="3" placeholder="Nhập ghi chú">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Danh sách sản phẩm -->
        <div class="card">
            <div class="card-header">
                <h3>Sản phẩm</h3>
            </div>
            
            <div id="products-container">
                <div class="product-item" style="display: grid; grid-template-columns: 2fr 2fr 1fr 1fr 1fr auto; gap: 12px; align-items: end; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 12px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Sản phẩm <span style="color: #e53e3e;">*</span></label>
                        <select name="items[0][product_id]" class="form-control product-select" required>
                            <option value="">-- Chọn sản phẩm --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->selling_price ?? 0 }}" data-stock="{{ $product->stock ?? 0 }}" data-sku="{{ $product->sku ?? '' }}">
                                    {{ $product->name }} - {{ $product->sku ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Phiên bản</label>
                        <select name="items[0][product_variant_id]" class="form-control variant-select">
                            <option value="">-- Chọn phiên bản --</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Số lượng <span style="color: #e53e3e;">*</span></label>
                        <input type="number" name="items[0][quantity]" class="form-control quantity-input" 
                               min="1" value="1" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Đơn giá <span style="color: #e53e3e;">*</span></label>
                        <input type="number" name="items[0][unit_price]" class="form-control price-input" 
                               min="0" step="0.01" required readonly style="background-color: #f7fafc;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Thành tiền</label>
                        <input type="text" class="form-control total-price" readonly 
                               style="background-color: #f7fafc;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; align-self: end;">
                        <button type="button" class="btn btn-danger remove-product" style="padding: 8px 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 16px;">
                <button type="button" id="add-product" class="btn btn-outline">
                    <i class="fas fa-plus"></i>
                    Thêm sản phẩm
                </button>
            </div>
        </div>

        <!-- Khuyến mại -->
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-gift" style="margin-right: 8px; color: #4299e1;"></i>
                    Khuyến mại
                </h3>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <div>
                    <div class="form-group">
                        <label class="form-label">Mã khuyến mại</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" id="promotion_code" class="form-control" 
                                   placeholder="Nhập mã khuyến mại" style="flex: 1;">
                            <button type="button" id="apply_promotion" class="btn btn-outline">
                                <i class="fas fa-check"></i>
                                Áp dụng
                            </button>
                        </div>
                        <div id="promotion_message" style="margin-top: 8px; font-size: 13px;"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Khuyến mại đang hoạt động</label>
                        <div id="active_promotions" style="max-height: 200px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; background: #f7fafc;">
                            <div style="text-align: center; color: #718096; font-size: 13px;">
                                <i class="fas fa-spinner fa-spin"></i> Đang tải...
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="form-group">
                        <label class="form-label">Khuyến mại đã áp dụng</label>
                        <div id="applied_promotions" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; background: #f0fff4; min-height: 100px;">
                            <div style="text-align: center; color: #718096; font-size: 13px;">
                                Chưa có khuyến mại nào được áp dụng
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Tổng giảm giá từ khuyến mại</label>
                        <input type="text" id="promotion_discount_total" class="form-control" readonly 
                               style="background-color: #f0fff4; font-weight: 600; color: #059669;" value="0 VNĐ">
                    </div>
                </div>
            </div>
        </div>

        <!-- Tổng kết -->
        <div class="card">
            <div class="card-header">
                <h3>Tổng kết</h3>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <div>
                    <div class="form-group">
                        <label class="form-label">Tổng tiền hàng</label>
                        <input type="text" id="total_amount" class="form-control" readonly 
                               style="background-color: #f7fafc; font-weight: 600;">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Giảm giá thủ công</label>
                        <input type="text" inputmode="numeric" name="discount_amount" id="discount_amount" data-money class="form-control" 
                               value="{{ old('discount_amount', 0) }}" onchange="calculateTotal()">
                    </div>

                    <div class="form-group" style="margin-top:12px;">
                        <div style="border:1px dashed #cbd5e0; border-radius:8px; padding:12px; background:#f9fafb;">
                            <div style="font-weight:600; margin-bottom:8px; color:#2d3748;">Chi tiết chiết khấu</div>
                            <div style="font-size:13px; color:#4a5568; display:grid; grid-template-columns: 1fr 1fr; gap:8px;">
                                <div>Nhóm KH:</div>
                                <div id="group_summary_name">-</div>
                                <div>Tỷ lệ (%):</div>
                                <div id="group_summary_rate">-</div>
                                <div>Ngưỡng tối thiểu:</div>
                                <div id="group_summary_min">-</div>
                                <div>Giảm tối đa:</div>
                                <div id="group_summary_cap">-</div>
                                <div>Chiết khấu nhóm:</div>
                                <div id="group_discount_value">0</div>
                                <div>Giảm thủ công:</div>
                                <div id="manual_discount_value">0</div>
                                <div>Giảm khuyến mại:</div>
                                <div id="promotion_discount_value">0</div>
                                <div style="font-weight:600;">Tổng giảm:</div>
                                <div id="total_discount_value" style="font-weight:600;">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="form-group">
                        <label class="form-label">Thành tiền</label>
                        <input type="text" id="final_amount" class="form-control" readonly 
                               style="background-color: #e6fffa; font-weight: 600; font-size: 16px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Nút hành động -->
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
            <a href="{{ route('orders.index') }}" class="btn btn-outline">
                <i class="fas fa-times"></i>
                Hủy
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Tạo đơn hàng
            </button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    let productIndex = 1;

    // Thêm sản phẩm mới
    document.getElementById('add-product').addEventListener('click', function() {
        const container = document.getElementById('products-container');
        const newProduct = container.firstElementChild.cloneNode(true);
        
        // Cập nhật name attributes
        const inputs = newProduct.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.name) {
                input.name = input.name.replace('[0]', `[${productIndex}]`);
            }
        });
        
        // Reset values
        newProduct.querySelector('.product-select').value = '';
        newProduct.querySelector('.quantity-input').value = 1;
        newProduct.querySelector('.price-input').value = '';
        newProduct.querySelector('.price-input').readOnly = true;
        newProduct.querySelector('.price-input').style.backgroundColor = '#f7fafc';
        newProduct.querySelector('.total-price').value = '';
        const variantSelectNew = newProduct.querySelector('.variant-select');
        if (variantSelectNew) {
            variantSelectNew.innerHTML = '<option value="">-- Chọn phiên bản --</option>';
        }
        const qtyInputNew = newProduct.querySelector('.quantity-input');
        if (qtyInputNew) {
            qtyInputNew.removeAttribute('max');
        }
        
        container.appendChild(newProduct);
        productIndex++;
        
        // Thêm event listeners cho sản phẩm mới
        addProductEventListeners(newProduct);
    });

    // Xóa sản phẩm
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-product') || e.target.parentElement.classList.contains('remove-product')) {
            const productItem = e.target.closest('.product-item');
            if (document.querySelectorAll('.product-item').length > 1) {
                productItem.remove();
                calculateTotal();
            } else {
                alert('Phải có ít nhất 1 sản phẩm trong đơn hàng');
            }
        }
    });

    // Thêm event listeners cho sản phẩm
    function addProductEventListeners(productItem) {
        const productSelect = productItem.querySelector('.product-select');
        const quantityInput = productItem.querySelector('.quantity-input');
        const priceInput = productItem.querySelector('.price-input');
        
        productSelect.addEventListener('change', async function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price') || 0;
            const stock = parseInt(selectedOption.getAttribute('data-stock') || '0', 10);
            priceInput.value = price;
            if (stock > 0) {
                const qty = parseInt(quantityInput.value || '1', 10);
                if (qty > stock) {
                    quantityInput.value = stock;
                }
                quantityInput.setAttribute('max', String(stock));
            } else {
                quantityInput.removeAttribute('max');
            }
            // Load and populate versions (full + available decants) from embedded data to ensure reliability
            const variantSelect = productItem.querySelector('.variant-select');
            if (variantSelect) {
                variantSelect.innerHTML = '';
                // Full version option (label by product SKU only)
                const fullOpt = document.createElement('option');
                fullOpt.value = '';
                const productSku = selectedOption.getAttribute('data-sku') || 'Bản đầy đủ';
                fullOpt.textContent = productSku;
                fullOpt.setAttribute('data-price', price);
                fullOpt.setAttribute('data-stock', String(stock));
                variantSelect.appendChild(fullOpt);

                // Decant variants with stock > 0 from embedded $products
                const productId = parseInt(this.value, 10);
                const productsData = {!! json_encode($productsData) !!};
                const productData = productsData.find(p => p.id === productId);
                if (productData && productData.variants) {
                    productData.variants.filter(v => ((v.stock !== undefined && v.stock !== null) ? v.stock : 0) > 0).forEach(v => {
                        const opt = document.createElement('option');
                        opt.value = v.id;
                        opt.textContent = (v.sku !== undefined && v.sku !== null) ? String(v.sku) : '';
                        opt.setAttribute('data-price', (v.selling_price !== undefined && v.selling_price !== null) ? String(v.selling_price) : '0');
                        opt.setAttribute('data-stock', (v.stock !== undefined && v.stock !== null) ? String(v.stock) : '0');
                        variantSelect.appendChild(opt);
                    });
                }
            }
            calculateItemTotal(productItem);
        });
        
        quantityInput.addEventListener('input', function() {
            const variantSelect = productItem.querySelector('.variant-select');
            const selected = variantSelect ? variantSelect.options[variantSelect.selectedIndex] : null;
            const maxStock = selected ? parseInt(selected.getAttribute('data-stock') || '0', 10) : null;
            if (maxStock && parseInt(this.value||'0',10) > maxStock) {
                this.value = maxStock;
            }
            calculateItemTotal(productItem);
        });

        // Handle version selection change
        const variantSelect = productItem.querySelector('.variant-select');
        if (variantSelect) {
            variantSelect.addEventListener('change', function(){
                const opt = this.options[this.selectedIndex];
                const vPrice = opt.getAttribute('data-price');
                const vStock = parseInt(opt.getAttribute('data-stock') || '0', 10);
                if (vPrice) {
                    priceInput.value = vPrice;
                }
                if (vStock > 0) {
                    const qty = parseInt(quantityInput.value || '1', 10);
                    if (qty > vStock) {
                        quantityInput.value = vStock;
                    }
                    quantityInput.setAttribute('max', String(vStock));
                } else {
                    quantityInput.removeAttribute('max');
                }
                calculateItemTotal(productItem);
            });
        }
        
        priceInput.addEventListener('input', function() {
            calculateItemTotal(productItem);
        });
    }

    // Tính tổng tiền cho từng sản phẩm
    function calculateItemTotal(productItem) {
        const quantity = parseFloat(productItem.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(productItem.querySelector('.price-input').value) || 0;
        const total = quantity * price;
        
        productItem.querySelector('.total-price').value = new Intl.NumberFormat('vi-VN').format(total);
        calculateTotal();
    }

    // Tính tổng tiền đơn hàng
    function calculateTotal() {
        let totalAmount = 0;
        
        document.querySelectorAll('.product-item').forEach(item => {
            const quantity = parseFloat(item.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(item.querySelector('.price-input').value) || 0;
            totalAmount += quantity * price;
        });
        
        // Tính chiết khấu nhóm
        let groupDiscount = 0;
        const groupSelect = document.getElementById('customer_group_id');
        let groupName = '-';
        let groupRate = '-';
        let groupMinText = '-';
        let groupCapText = '-';
        if (groupSelect && groupSelect.value) {
            const selected = groupSelect.options[groupSelect.selectedIndex];
            groupName = selected.textContent || '-';
            const rate = parseFloat(selected.getAttribute('data-rate') || '0');
            const minOrder = parseFloat(selected.getAttribute('data-min') || '0');
            const maxDiscount = parseFloat(selected.getAttribute('data-max') || '0');
            groupRate = isNaN(rate) ? '-' : new Intl.NumberFormat('vi-VN').format(rate);
            groupMinText = isNaN(minOrder) || !minOrder ? '-' : new Intl.NumberFormat('vi-VN').format(minOrder);
            groupCapText = isNaN(maxDiscount) || !maxDiscount ? '-' : new Intl.NumberFormat('vi-VN').format(maxDiscount);
            if (totalAmount >= (minOrder || 0) && rate > 0) {
                groupDiscount = totalAmount * rate / 100;
                if (!isNaN(maxDiscount) && maxDiscount > 0) {
                    groupDiscount = Math.min(groupDiscount, maxDiscount);
                }
            }
        }

        const manualDiscount = (function(){
            const el = document.getElementById('discount_amount');
            if(!el) return 0;
            const digits = String(el.value||'').replace(/\D+/g,'');
            return digits ? parseInt(digits,10) : 0;
        })();
        
        // Tính giảm giá từ khuyến mại
        const promotionDiscount = window.promotionDiscountTotal || 0;
        
        const discountAmount = groupDiscount + manualDiscount + promotionDiscount;
        const finalAmount = totalAmount - discountAmount;
        
        document.getElementById('total_amount').value = new Intl.NumberFormat('vi-VN').format(totalAmount);
        document.getElementById('final_amount').value = new Intl.NumberFormat('vi-VN').format(finalAmount);
        
        // Cập nhật hiển thị chi tiết
        const nf = new Intl.NumberFormat('vi-VN');
        const setText = (id, text) => { const el = document.getElementById(id); if (el) el.textContent = text; };
        setText('group_summary_name', groupName);
        setText('group_summary_rate', typeof groupRate === 'string' ? groupRate : nf.format(groupRate));
        setText('group_summary_min', groupMinText);
        setText('group_summary_cap', groupCapText);
        setText('group_discount_value', nf.format(groupDiscount));
        setText('manual_discount_value', nf.format(manualDiscount));
        setText('promotion_discount_value', nf.format(promotionDiscount));
        setText('total_discount_value', nf.format(discountAmount));
    }

    // Khuyến mại management
    window.promotionDiscountTotal = 0;
    window.appliedPromotions = [];

    // Load active promotions
    async function loadActivePromotions() {
        try {
            const response = await fetch('/promotions/active');
            const data = await response.json();
            displayActivePromotions(data.promotions);
        } catch (error) {
            console.error('Lỗi tải khuyến mại:', error);
            document.getElementById('active_promotions').innerHTML = 
                '<div style="text-align: center; color: #e53e3e; font-size: 13px;">Lỗi tải khuyến mại</div>';
        }
    }

    // Display active promotions
    function displayActivePromotions(promotions) {
        const container = document.getElementById('active_promotions');
        if (!promotions || promotions.length === 0) {
            container.innerHTML = '<div style="text-align: center; color: #718096; font-size: 13px;">Không có khuyến mại nào</div>';
            return;
        }

        container.innerHTML = promotions.map(promo => `
            <div class="promotion-item" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; margin-bottom: 8px; transition: all 0.2s; position: relative;" 
                 onmouseover="this.style.backgroundColor='#f7fafc'" 
                 onmouseout="this.style.backgroundColor='transparent'">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="flex: 1; cursor: pointer;" onclick="applyPromotionCode('${promo.code}')">
                        <div style="font-weight: 600; color: #2d3748; font-size: 13px;">${promo.code}</div>
                        <div style="color: #4a5568; font-size: 12px; margin-top: 2px;">${promo.name}</div>
                        <div style="color: #718096; font-size: 11px; margin-top: 2px;">${promo.description || ''}</div>
                    </div>
                    <button type="button" onclick="showPromotionRequirements('${promo.code}', '${promo.name}', '${promo.description || ''}', ${promo.discount_value}, ${promo.max_discount_amount || 0}, ${promo.min_order_amount || 0}, '${promo.type}', ${promo.min_items || 0}, '${promo.scope}', ${JSON.stringify(promo.applicable_product_ids || [])}, ${JSON.stringify(promo.applicable_category_ids || [])}, ${JSON.stringify(promo.applicable_customer_group_ids || [])}, ${JSON.stringify(promo.applicable_sales_channels || [])}, ${promo.is_stackable})" 
                            style="background: #4299e1; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 10px; margin-left: 8px; transition: all 0.2s;"
                            onmouseover="this.style.backgroundColor='#3182ce'; this.style.transform='scale(1.1)'"
                            onmouseout="this.style.backgroundColor='#4299e1'; this.style.transform='scale(1)'"
                            title="Xem yêu cầu áp dụng">
                        !
                    </button>
                </div>
            </div>
        `).join('');
    }

    // Apply promotion code
    async function applyPromotionCode(code) {
        document.getElementById('promotion_code').value = code;
        await validateAndApplyPromotion();
    }

    // Validate and apply promotion
    async function validateAndApplyPromotion() {
        const code = document.getElementById('promotion_code').value.trim();
        if (!code) {
            showPromotionMessage('Vui lòng nhập mã khuyến mại', 'error');
            return;
        }

        // Check if already applied
        if (window.appliedPromotions.some(p => p.code === code)) {
            showPromotionMessage('Mã khuyến mại này đã được áp dụng', 'warning');
            return;
        }

        try {
            // Build cart data
            const cart = buildCartData();
            
            const response = await fetch('/promotions/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    code: code,
                    cart: cart
                })
            });

            const result = await response.json();

            if (result.valid) {
                // Add to applied promotions
                window.appliedPromotions.push(result.promotion);
                window.promotionDiscountTotal += result.discount_amount;
                
                updateAppliedPromotionsDisplay();
                updatePromotionDiscountDisplay();
                calculateTotal();
                
                showPromotionMessage(`Áp dụng thành công! Giảm ${result.discount_amount.toLocaleString('vi-VN')} VNĐ`, 'success');
                document.getElementById('promotion_code').value = '';
            } else {
                showPromotionMessage(result.message, 'error');
            }
        } catch (error) {
            console.error('Lỗi validate khuyến mại:', error);
            showPromotionMessage('Có lỗi xảy ra khi áp dụng mã khuyến mại', 'error');
        }
    }

    // Build cart data for validation
    function buildCartData() {
        const items = [];
        document.querySelectorAll('.product-item').forEach(item => {
            const productSelect = item.querySelector('.product-select');
            const quantityInput = item.querySelector('.quantity-input');
            const priceInput = item.querySelector('.price-input');
            
            if (productSelect.value && quantityInput.value && priceInput.value) {
                items.push({
                    product_id: parseInt(productSelect.value),
                    category_ids: [], // TODO: Get from product data
                    price: parseFloat(priceInput.value),
                    qty: parseInt(quantityInput.value)
                });
            }
        });

        return {
            items: items,
            subtotal: calculateSubtotal(),
            sales_channel: 'online', // Default
            customer_group_id: document.getElementById('customer_group_id').value ? parseInt(document.getElementById('customer_group_id').value) : null
        };
    }

    // Calculate subtotal
    function calculateSubtotal() {
        let total = 0;
        document.querySelectorAll('.product-item').forEach(item => {
            const quantity = parseFloat(item.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(item.querySelector('.price-input').value) || 0;
            total += quantity * price;
        });
        return total;
    }

    // Update applied promotions display
    function updateAppliedPromotionsDisplay() {
        const container = document.getElementById('applied_promotions');
        if (window.appliedPromotions.length === 0) {
            container.innerHTML = '<div style="text-align: center; color: #718096; font-size: 13px;">Chưa có khuyến mại nào được áp dụng</div>';
            return;
        }

        container.innerHTML = window.appliedPromotions.map(promo => `
            <div style="border: 1px solid #c6f6d5; border-radius: 6px; padding: 8px; margin-bottom: 8px; background: #f0fff4;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 600; color: #22543d; font-size: 13px;">${promo.code}</div>
                        <div style="color: #2f855a; font-size: 12px;">${promo.name}</div>
                    </div>
                    <button type="button" onclick="removePromotion('${promo.code}')" 
                            style="background: #e53e3e; color: white; border: none; border-radius: 4px; padding: 4px 8px; font-size: 11px; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    // Update promotion discount display
    function updatePromotionDiscountDisplay() {
        const total = window.promotionDiscountTotal;
        document.getElementById('promotion_discount_total').value = total.toLocaleString('vi-VN') + ' VNĐ';
    }

    // Remove promotion
    function removePromotion(code) {
        const index = window.appliedPromotions.findIndex(p => p.code === code);
        if (index !== -1) {
            const removedPromo = window.appliedPromotions[index];
            window.promotionDiscountTotal -= removedPromo.discount_amount || 0;
            window.appliedPromotions.splice(index, 1);
            
            updateAppliedPromotionsDisplay();
            updatePromotionDiscountDisplay();
            calculateTotal();
            
            showPromotionMessage(`Đã bỏ khuyến mại ${code}`, 'info');
        }
    }

    // Show promotion message
    function showPromotionMessage(message, type) {
        const messageEl = document.getElementById('promotion_message');
        const colors = {
            success: '#059669',
            error: '#e53e3e',
            warning: '#d97706',
            info: '#4299e1'
        };
        
        messageEl.innerHTML = `<span style="color: ${colors[type]};">${message}</span>`;
        
        // Auto hide after 3 seconds
        setTimeout(() => {
            messageEl.innerHTML = '';
        }, 3000);
    }

    // Show promotion requirements modal
    function showPromotionRequirements(code, name, description, discountValue, maxDiscount, minOrder, type, minItems, scope, applicableProducts, applicableCategories, applicableCustomerGroups, applicableSalesChannels, isStackable) {
        // Create modal HTML
        const modalHTML = `
            <div id="promotionModal" class="modal" style="display: block;">
                <div class="modal-content" style="max-width: 500px;">
                    <div class="modal-header">
                        <h3>
                            <i class="fas fa-info-circle" style="margin-right: 8px; color: #4299e1;"></i>
                            Yêu cầu áp dụng khuyến mại
                        </h3>
                        <span class="close" onclick="closePromotionModal()">&times;</span>
                    </div>
                    <div style="padding: 20px 0;">
                        <div style="background: #f7fafc; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                            <div style="font-weight: 600; color: #2d3748; font-size: 16px; margin-bottom: 4px;">${code}</div>
                            <div style="color: #4a5568; font-size: 14px; margin-bottom: 8px;">${name}</div>
                            <div style="color: #718096; font-size: 13px;">${description}</div>
                        </div>
                        
                        <div style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; padding: 16px;">
                            <div style="font-weight: 600; color: #742a2a; margin-bottom: 12px; display: flex; align-items: center;">
                                <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                                Điều kiện áp dụng
                            </div>
                            <div style="color: #742a2a; font-size: 14px; line-height: 1.6;">
                                ${generateRequirementsText(type, discountValue, maxDiscount, minOrder, minItems, scope, applicableProducts, applicableCategories, applicableCustomerGroups, applicableSalesChannels, isStackable)}
                            </div>
                        </div>
                        
                        <div style="background: #f0fff4; border: 1px solid #9ae6b4; border-radius: 8px; padding: 16px; margin-top: 16px;">
                            <div style="font-weight: 600; color: #22543d; margin-bottom: 8px; display: flex; align-items: center;">
                                <i class="fas fa-lightbulb" style="margin-right: 8px;"></i>
                                Mẹo sử dụng
                            </div>
                            <div style="color: #22543d; font-size: 13px; line-height: 1.5;">
                                • Click vào tên khuyến mại để áp dụng ngay<br>
                                • Khuyến mại sẽ được kiểm tra tự động khi áp dụng<br>
                                • Có thể kết hợp nhiều khuyến mại nếu được phép
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                        <button onclick="closePromotionModal()" class="btn btn-outline">
                            <i class="fas fa-times"></i>
                            Đóng
                        </button>
                        <button onclick="applyPromotionCode('${code}'); closePromotionModal();" class="btn btn-primary">
                            <i class="fas fa-check"></i>
                            Áp dụng ngay
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Close modal when clicking outside
        document.getElementById('promotionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePromotionModal();
            }
        });
    }

    // Generate requirements text based on promotion type
    function generateRequirementsText(type, discountValue, maxDiscount, minOrder, minItems, scope, applicableProducts, applicableCategories, applicableCustomerGroups, applicableSalesChannels, isStackable) {
        let requirements = [];
        
        // Minimum order amount
        if (minOrder > 0) {
            requirements.push(`• Đơn hàng tối thiểu: ${minOrder.toLocaleString('vi-VN')} VNĐ`);
        }
        
        // Minimum items
        if (minItems > 0) {
            requirements.push(`• Số lượng sản phẩm tối thiểu: ${minItems} sản phẩm`);
        }
        
        // Discount conditions
        if (type === 'percent') {
            requirements.push(`• Giảm giá: ${discountValue}%`);
            if (maxDiscount > 0) {
                requirements.push(`• Giảm tối đa: ${maxDiscount.toLocaleString('vi-VN')} VNĐ`);
            }
        } else if (type === 'fixed_amount') {
            requirements.push(`• Giảm giá: ${discountValue.toLocaleString('vi-VN')} VNĐ`);
        } else if (type === 'free_shipping') {
            requirements.push(`• Miễn phí vận chuyển`);
        } else if (type === 'buy_x_get_y') {
            requirements.push(`• Mua ${discountValue} sản phẩm được tặng ${maxDiscount} sản phẩm`);
        }
        
        // Scope conditions
        if (scope === 'product' && applicableProducts && applicableProducts.length > 0) {
            requirements.push(`• Chỉ áp dụng cho sản phẩm cụ thể (ID: ${applicableProducts.join(', ')})`);
        } else if (scope === 'category' && applicableCategories && applicableCategories.length > 0) {
            requirements.push(`• Chỉ áp dụng cho danh mục cụ thể (ID: ${applicableCategories.join(', ')})`);
        }
        
        // Customer group conditions
        if (applicableCustomerGroups && applicableCustomerGroups.length > 0) {
            requirements.push(`• Chỉ áp dụng cho nhóm khách hàng cụ thể (ID: ${applicableCustomerGroups.join(', ')})`);
        }
        
        // Sales channel conditions
        if (applicableSalesChannels && applicableSalesChannels.length > 0) {
            const channels = applicableSalesChannels.map(ch => ch === 'online' ? 'Online' : ch === 'offline' ? 'Offline' : ch).join(', ');
            requirements.push(`• Chỉ áp dụng cho kênh bán hàng: ${channels}`);
        }
        
        // Stackable condition
        if (isStackable) {
            requirements.push(`• Có thể kết hợp với khuyến mại khác`);
        } else {
            requirements.push(`• Không thể kết hợp với khuyến mại khác`);
        }
        
        // General conditions
        requirements.push(`• Khuyến mại phải đang trong thời gian hiệu lực`);
        requirements.push(`• Chỉ áp dụng cho khách hàng đủ điều kiện`);
        
        return requirements.join('<br>');
    }

    // Close promotion modal
    function closePromotionModal() {
        const modal = document.getElementById('promotionModal');
        if (modal) {
            modal.remove();
        }
    }

    // Thêm event listeners cho sản phẩm đầu tiên
    document.addEventListener('DOMContentLoaded', function() {
        addProductEventListeners(document.querySelector('.product-item'));
        calculateTotal();
        const groupSelect = document.getElementById('customer_group_id');
        if (groupSelect) {
            groupSelect.addEventListener('change', calculateTotal);
        }
        
        // Load active promotions
        loadActivePromotions();
        
        // Apply promotion button
        document.getElementById('apply_promotion').addEventListener('click', validateAndApplyPromotion);
        
        // Enter key for promotion code
        document.getElementById('promotion_code').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                validateAndApplyPromotion();
            }
        });
        
        // init money inputs
        (function(){
            function fmt(v){ const d=String(v||'').replace(/\D+/g,''); return d? new Intl.NumberFormat('vi-VN').format(parseInt(d,10)) : ''; }
            const dm = document.querySelectorAll('input[data-money]');
            dm.forEach(inp=>{ inp.value = fmt(inp.value); inp.addEventListener('input', function(){ const c=inp.selectionStart; const b=inp.value.length; inp.value = fmt(inp.value); const a=inp.value.length; const k=(c||0)+(a-b); try{ inp.setSelectionRange(k,k);}catch(e){}; calculateTotal(); });});
            const form = document.getElementById('orderForm');
            form && form.addEventListener('submit', function(){ dm.forEach(inp=>{ const d=String(inp.value||'').replace(/\D+/g,''); inp.value = d? String(parseInt(d,10)) : ''; }); });
        })();
    });

    // Validation form
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        const productItems = document.querySelectorAll('.product-item');
        let hasValidProduct = false;
        
        productItems.forEach(item => {
            const productId = item.querySelector('.product-select').value;
            const quantity = item.querySelector('.quantity-input').value;
            const price = item.querySelector('.price-input').value;
            
            if (productId && quantity && price) {
                hasValidProduct = true;
            }
        });
        
        if (!hasValidProduct) {
            e.preventDefault();
            alert('Vui lòng thêm ít nhất 1 sản phẩm hợp lệ');
        }
    });
</script>
@endpush

@push('styles')
<style>
    .product-item {
        background-color: #f8f9fa;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 500;
        color: #4a5568;
        margin-bottom: 6px;
    }

    .form-control {
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #4299e1;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    .btn {
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: #4299e1;
        color: white;
    }

    .btn-primary:hover {
        background: #3182ce;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(66, 153, 225, 0.3);
    }

    .btn-outline {
        background: white;
        color: #4299e1;
        border: 1px solid #4299e1;
    }

    .btn-outline:hover {
        background: #4299e1;
        color: white;
    }

    .btn-danger {
        background: #e53e3e;
        color: white;
    }

    .btn-danger:hover {
        background: #c53030;
    }

    .card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        padding: 24px;
        margin-bottom: 24px;
        border: 1px solid #e2e8f0;
    }

    .card-header {
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .card-header h3 {
        font-size: 18px;
        font-weight: 600;
        color: #2d3748;
        margin: 0;
    }

    /* Promotion Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
        backdrop-filter: blur(3px);
    }

    .modal-content {
        background-color: white;
        margin: 8% auto;
        padding: 24px;
        border-radius: 12px;
        width: 90%;
        max-width: 480px;
        max-height: 80vh;
        overflow-y: auto;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        animation: modalSlideIn 0.2s ease;
    }

    @keyframes modalSlideIn {
        from { opacity: 0; transform: translateY(-50px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modal-header { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 20px; 
        padding-bottom: 16px; 
        border-bottom: 1px solid #e2e8f0; 
    }

    .modal-header h3 { 
        font-size: 18px; 
        font-weight: 600; 
        color: #2d3748; 
        margin: 0;
    }

    .close { 
        font-size: 24px; 
        font-weight: 300; 
        color: #718096; 
        cursor: pointer; 
        transition: color 0.2s ease; 
    }

    .close:hover { 
        color: #e53e3e; 
    }

    /* Promotion item hover effects */
    .promotion-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    /* Info button styles */
    .promotion-info-btn {
        background: #4299e1;
        color: white;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 10px;
        margin-left: 8px;
        transition: all 0.2s;
        font-weight: bold;
    }

    .promotion-info-btn:hover {
        background: #3182ce;
        transform: scale(1.1);
    }
</style>
@endpush