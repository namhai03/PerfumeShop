# Demo API Khuyến Mại - PerfumeShop

## 🚀 Cách Sử Dụng API Khuyến Mại

### 1. 📋 **Lấy Danh Sách Khuyến Mại Đang Hoạt Động**

```javascript
// GET /promotions/active
fetch('/promotions/active')
  .then(response => response.json())
  .then(data => {
    console.log('Khuyến mại đang hoạt động:', data.promotions);
  });
```

**Response:**
```json
{
  "promotions": [
    {
      "id": 1,
      "code": "SUMMER2024",
      "name": "Giảm giá mùa hè",
      "description": "Giảm 20% cho đơn hàng từ 500K",
      "type": "percent",
      "discount_value": 20,
      "max_discount_amount": 100000,
      "min_order_amount": 500000
    }
  ]
}
```

### 2. 🔍 **Validate Mã Khuyến Mại**

```javascript
// POST /promotions/validate
const validatePromotion = async (code, cart) => {
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
  
  return await response.json();
};

// Sử dụng
const cart = {
  items: [
    { product_id: 1, category_ids: [1, 2], price: 300000, qty: 2 },
    { product_id: 2, category_ids: [1], price: 200000, qty: 1 }
  ],
  subtotal: 800000,
  sales_channel: 'online',
  customer_group_id: 1
};

validatePromotion('SUMMER2024', cart)
  .then(result => {
    if (result.valid) {
      console.log('Mã hợp lệ! Giảm giá:', result.discount_amount);
    } else {
      console.log('Lỗi:', result.message);
    }
  });
```

**Response khi hợp lệ:**
```json
{
  "valid": true,
  "promotion": {
    "id": 1,
    "code": "SUMMER2024",
    "name": "Giảm giá mùa hè",
    "type": "percent",
    "discount_value": 20
  },
  "discount_amount": 160000,
  "shipping_discount": 0,
  "message": "Mã khuyến mại hợp lệ"
}
```

**Response khi không hợp lệ:**
```json
{
  "valid": false,
  "message": "Không đủ điều kiện áp dụng mã khuyến mại"
}
```

### 3. 🧮 **Tính Toán Khuyến Mại Tự Động**

```javascript
// POST /promotions/calculate
const calculatePromotions = async (cart) => {
  const response = await fetch('/promotions/calculate', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ cart: cart })
  });
  
  return await response.json();
};

// Sử dụng
const cart = {
  items: [
    { product_id: 1, category_ids: [1], price: 500000, qty: 1 },
    { product_id: 2, category_ids: [2], price: 300000, qty: 2 }
  ],
  subtotal: 1100000,
  sales_channel: 'online',
  customer_group_id: 1,
  shipping_fee: 30000
};

calculatePromotions(cart)
  .then(result => {
    console.log('Khuyến mại được áp dụng:', result.applied_promotions);
    console.log('Tổng giảm giá:', result.discount_total);
    console.log('Giảm phí ship:', result.shipping_discount);
  });
```

**Response:**
```json
{
  "applied_promotions": [
    {
      "id": 1,
      "code": "SUMMER2024",
      "name": "Giảm giá mùa hè",
      "type": "percent",
      "discount_value": 20
    },
    {
      "id": 2,
      "code": "FREESHIP",
      "name": "Miễn phí ship",
      "type": "free_shipping"
    }
  ],
  "discount_total": 220000,
  "shipping_discount": 30000
}
```

---

## 🛒 **Tích Hợp Vào Trang Đơn Hàng**

### HTML Form
```html
<div class="promotion-section">
  <h3>Mã khuyến mại</h3>
  <div class="input-group">
    <input type="text" id="promotionCode" placeholder="Nhập mã khuyến mại">
    <button type="button" id="applyPromotion">Áp dụng</button>
  </div>
  <div id="promotionResult"></div>
</div>

<div class="order-summary">
  <div class="subtotal">Tạm tính: <span id="subtotal">0</span> VNĐ</div>
  <div class="discount">Giảm giá: <span id="discount">0</span> VNĐ</div>
  <div class="shipping">Phí ship: <span id="shipping">0</span> VNĐ</div>
  <div class="total">Tổng cộng: <span id="total">0</span> VNĐ</div>
</div>
```

### JavaScript Logic
```javascript
class PromotionManager {
  constructor() {
    this.cart = {
      items: [],
      subtotal: 0,
      sales_channel: 'online',
      customer_group_id: null,
      shipping_fee: 30000
    };
    this.appliedPromotions = [];
    this.discountTotal = 0;
    this.shippingDiscount = 0;
    
    this.init();
  }
  
  init() {
    document.getElementById('applyPromotion').addEventListener('click', () => {
      this.applyPromotion();
    });
    
    // Load active promotions on page load
    this.loadActivePromotions();
  }
  
  async loadActivePromotions() {
    try {
      const response = await fetch('/promotions/active');
      const data = await response.json();
      this.displayActivePromotions(data.promotions);
    } catch (error) {
      console.error('Lỗi tải khuyến mại:', error);
    }
  }
  
  displayActivePromotions(promotions) {
    const container = document.getElementById('activePromotions');
    if (!container) return;
    
    container.innerHTML = promotions.map(promo => `
      <div class="promotion-item" data-code="${promo.code}">
        <strong>${promo.code}</strong> - ${promo.name}
        <div class="promotion-desc">${promo.description}</div>
      </div>
    `).join('');
    
    // Add click handlers
    container.querySelectorAll('.promotion-item').forEach(item => {
      item.addEventListener('click', () => {
        document.getElementById('promotionCode').value = item.dataset.code;
        this.applyPromotion();
      });
    });
  }
  
  async applyPromotion() {
    const code = document.getElementById('promotionCode').value.trim();
    if (!code) {
      this.showError('Vui lòng nhập mã khuyến mại');
      return;
    }
    
    try {
      const result = await this.validatePromotion(code);
      
      if (result.valid) {
        this.appliedPromotions.push(result.promotion);
        this.discountTotal += result.discount_amount;
        this.shippingDiscount += result.shipping_discount;
        
        this.showSuccess(`Áp dụng thành công! Giảm ${result.discount_amount.toLocaleString('vi-VN')} VNĐ`);
        this.updateOrderSummary();
      } else {
        this.showError(result.message);
      }
    } catch (error) {
      this.showError('Có lỗi xảy ra khi áp dụng mã khuyến mại');
      console.error(error);
    }
  }
  
  async validatePromotion(code) {
    const response = await fetch('/promotions/validate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({
        code: code,
        cart: this.cart
      })
    });
    
    return await response.json();
  }
  
  updateOrderSummary() {
    const subtotal = this.cart.subtotal;
    const discount = this.discountTotal;
    const shipping = Math.max(0, this.cart.shipping_fee - this.shippingDiscount);
    const total = subtotal - discount + shipping;
    
    document.getElementById('subtotal').textContent = subtotal.toLocaleString('vi-VN');
    document.getElementById('discount').textContent = discount.toLocaleString('vi-VN');
    document.getElementById('shipping').textContent = shipping.toLocaleString('vi-VN');
    document.getElementById('total').textContent = total.toLocaleString('vi-VN');
  }
  
  showSuccess(message) {
    this.showMessage(message, 'success');
  }
  
  showError(message) {
    this.showMessage(message, 'error');
  }
  
  showMessage(message, type) {
    const resultDiv = document.getElementById('promotionResult');
    resultDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    
    // Auto hide after 3 seconds
    setTimeout(() => {
      resultDiv.innerHTML = '';
    }, 3000);
  }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
  new PromotionManager();
});
```

---

## 🎯 **Các Trường Hợp Sử Dụng Thực Tế**

### 1. **Trang Giỏ Hàng**
- Hiển thị danh sách khuyến mại có thể áp dụng
- Validate mã khuyến mại khi người dùng nhập
- Tính toán và hiển thị tổng tiền sau giảm giá

### 2. **Trang Thanh Toán**
- Áp dụng khuyến mại cuối cùng
- Ghi lại lịch sử sử dụng khuyến mại
- Cập nhật số lượt sử dụng còn lại

### 3. **Trang Quản Lý Đơn Hàng**
- Hiển thị khuyến mại đã áp dụng trong đơn hàng
- Cho phép hủy/hiệu chỉnh khuyến mại
- Tính toán lại tổng tiền khi thay đổi

### 4. **Mobile App**
- API có thể được sử dụng trong mobile app
- Real-time validation khi người dùng nhập mã
- Offline caching cho danh sách khuyến mại

---

## 🔧 **Cấu Hình Nâng Cao**

### Environment Variables
```env
PROMOTION_CACHE_TTL=300
PROMOTION_MAX_STACKABLE=3
PROMOTION_AUTO_EXPIRE_CHECK=true
```

### Middleware
```php
// app/Http/Middleware/PromotionRateLimit.php
class PromotionRateLimit
{
    public function handle($request, Closure $next)
    {
        $key = 'promotion_validate_' . $request->ip();
        $attempts = Cache::get($key, 0);
        
        if ($attempts > 100) { // 100 requests per hour
            return response()->json(['error' => 'Rate limit exceeded'], 429);
        }
        
        Cache::put($key, $attempts + 1, 3600);
        return $next($request);
    }
}
```

### Queue Jobs
```php
// app/Jobs/ProcessPromotionUsage.php
class ProcessPromotionUsage implements ShouldQueue
{
    public function handle()
    {
        // Process promotion usage in background
        // Update usage statistics
        // Send notifications if needed
    }
}
```

---

*Tài liệu này cung cấp hướng dẫn chi tiết về cách sử dụng API khuyến mại trong hệ thống PerfumeShop.*
