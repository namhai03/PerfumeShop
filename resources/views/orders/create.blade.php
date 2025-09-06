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
                    <label class="form-label">Loại đơn hàng <span style="color: #e53e3e;">*</span></label>
                    <select name="type" id="type" class="form-control" required>
                        <option value="">-- Chọn loại --</option>
                        <option value="sale" {{ old('type') == 'sale' ? 'selected' : '' }}>Bán hàng</option>
                        <option value="return" {{ old('type') == 'return' ? 'selected' : '' }}>Trả hàng</option>
                        <option value="draft" {{ old('type') == 'draft' ? 'selected' : '' }}>Đơn nháp</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng thái <span style="color: #e53e3e;">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="unpaid" {{ old('status', 'unpaid') == 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                        <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Ngày đơn hàng <span style="color: #e53e3e;">*</span></label>
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
                <div class="product-item" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap: 12px; align-items: end; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 12px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Sản phẩm <span style="color: #e53e3e;">*</span></label>
                        <select name="items[0][product_id]" class="form-control product-select" required>
                            <option value="">-- Chọn sản phẩm --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->selling_price ?? 0 }}">
                                    {{ $product->name }} - {{ number_format($product->selling_price ?? 0, 0, ',', '.') }} ₫
                                </option>
                            @endforeach
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
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Ghi chú</label>
                        <input type="text" name="items[0][custom_notes]" class="form-control" 
                               placeholder="Ghi chú riêng">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
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
                        <label class="form-label">Giảm giá</label>
                        <input type="number" name="discount_amount" id="discount_amount" class="form-control" 
                               min="0" step="0.01" value="{{ old('discount_amount', 0) }}" 
                               onchange="calculateTotal()">
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
        newProduct.querySelector('input[name*="custom_notes"]').value = '';
        
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
        
        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price') || 0;
            priceInput.value = price;
            calculateItemTotal(productItem);
        });
        
        quantityInput.addEventListener('input', function() {
            calculateItemTotal(productItem);
        });
        
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
        
        const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const finalAmount = totalAmount - discountAmount;
        
        document.getElementById('total_amount').value = new Intl.NumberFormat('vi-VN').format(totalAmount);
        document.getElementById('final_amount').value = new Intl.NumberFormat('vi-VN').format(finalAmount);
    }

    // Thêm event listeners cho sản phẩm đầu tiên
    document.addEventListener('DOMContentLoaded', function() {
        addProductEventListeners(document.querySelector('.product-item'));
        calculateTotal();
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
</style>
@endpush