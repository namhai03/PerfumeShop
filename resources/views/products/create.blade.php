@extends('layouts.app')

@section('title', 'Thêm sản phẩm mới - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
        <h1 class="page-title">Thêm sản phẩm mới</h1>
        <a href="{{ route('products.index') }}" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
    </div>

    <div class="card">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 28px;">
                <!-- Left Column -->
                <div>
                    <div class="form-group">
                        <label for="name" class="form-label">Tên sản phẩm *</label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="sku" class="form-label">Mã SKU *</label>
                        <input type="text" id="sku" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}" required>
                        @error('sku')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description') }}</textarea>
                        @error('description')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Danh mục</label>
                        <div id="category-multi" class="form-control" style="height:auto; padding:8px; display:flex; flex-wrap:wrap; gap:8px; cursor:text;">
                            @foreach(($categories ?? []) as $cat)
                                <button type="button" class="tag-option" data-id="{{ $cat->id }}" style="border:1px solid #e2e8f0; background:#fff; padding:6px 10px; border-radius:999px; font-size:12px;">{{ $cat->name }}</button>
                            @endforeach
                        </div>
                        <input type="hidden" name="categories" id="categories-hidden" value="">

                        @error('categories')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="brand" class="form-label">Thương hiệu</label>
                        <input type="text" id="brand" name="brand" class="form-control @error('brand') is-invalid @enderror" value="{{ old('brand') }}">
                        @error('brand')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    
                </div>

                <!-- Right Column -->
                <div>
                    <div class="form-group">
                        <label for="import_price" class="form-label">Giá nhập (VNĐ) *</label>
                        <input type="number" id="import_price" name="import_price" class="form-control @error('import_price') is-invalid @enderror" value="{{ old('import_price') }}" min="0" step="1000" required>
                        @error('import_price')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="selling_price" class="form-label">Giá bán (VNĐ) *</label>
                        <input type="number" id="selling_price" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price') }}" min="0" step="1000" required>
                        @error('selling_price')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="stock" class="form-label">Số lượng tồn kho *</label>
                        <input type="number" id="stock" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', 0) }}" min="0" required>
                        @error('stock')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="volume" class="form-label">Dung tích (ml)</label>
                        <input type="text" id="volume" name="volume" class="form-control @error('volume') is-invalid @enderror" value="{{ old('volume') }}" placeholder="VD: 50ml, 100ml">
                        @error('volume')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="form-group">
                        <label for="low_stock_threshold" class="form-label">Ngưỡng cảnh báo sắp hết</label>
                        <input type="number" id="low_stock_threshold" name="low_stock_threshold" class="form-control @error('low_stock_threshold') is-invalid @enderror" value="{{ old('low_stock_threshold', 5) }}" min="0">
                        @error('low_stock_threshold')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="concentration" class="form-label">Nồng độ</label>
                        <select id="concentration" name="concentration" class="form-control @error('concentration') is-invalid @enderror">
                            <option value="">-- Chọn nồng độ --</option>
                            @foreach(['Parfum','EDP','EDT','EDC','Mist'] as $opt)
                                <option value="{{ $opt }}" {{ old('concentration')===$opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('concentration')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="fragrance_family" class="form-label">Nhóm hương chính</label>
                        <select id="fragrance_family" name="fragrance_family" class="form-control @error('fragrance_family') is-invalid @enderror">
                            <option value="">-- Chọn nhóm hương --</option>
                            @foreach(['Floral','Fruity','Woody','Oriental'] as $opt)
                                <option value="{{ $opt }}" {{ old('fragrance_family')===$opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('fragrance_family')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    

                

                    <div class="form-group">
                        <label for="gender" class="form-label">Giới tính</label>
                        <select id="gender" name="gender" class="form-control @error('gender') is-invalid @enderror">
                            <option value="">-- Chọn --</option>
                            @foreach(['Nam','Nữ','Unisex'] as $opt)
                                <option value="{{ $opt }}" {{ old('gender')===$opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                        @error('gender')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

            

                    

                    <div class="form-group">
                        <label class="form-label">Tags</label>
                        <div id="tag-multi" class="form-control" style="height:auto; padding:8px; display:flex; flex-wrap:wrap; gap:8px; cursor:text;">
                            @foreach(($allTags ?? []) as $tag)
                                <button type="button" class="tag-option" data-tag="{{ $tag }}" style="border:1px solid #e2e8f0; background:#fff; padding:6px 10px; border-radius:999px; font-size:12px;">{{ $tag }}</button>
                            @endforeach
                            <input type="text" id="tag-input" placeholder="Thêm tag..." style="border:none; outline:none; flex:1; min-width:120px; font-size:12px;" />
                        </div>
                        <input type="hidden" name="tags" id="tags-hidden" value="{{ old('tags') }}">
                        <small style="color:#6c757d;">Bấm để chọn/bỏ tag. Gõ để thêm tag mới, Enter để xác nhận.</small>
                        @error('tags')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="import_date" class="form-label">Ngày nhập hàng</label>
                        <input type="date" id="import_date" name="import_date" class="form-control @error('import_date') is-invalid @enderror" value="{{ old('import_date') }}">
                        @error('import_date')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="origin" class="form-label">Xuất xứ</label>
                        <input type="text" id="origin" name="origin" class="form-control @error('origin') is-invalid @enderror" value="{{ old('origin') }}" placeholder="VD: Pháp, Ý, Mỹ">
                        @error('origin')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="image" class="form-label">Hình ảnh sản phẩm</label>
                        <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                        @error('image')
                            <div style="color: #dc3545; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Trạng thái</label>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                Đang bán
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="is_active" value="0" {{ old('is_active') == '0' ? 'checked' : '' }}>
                                Không bán
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Variants Section -->
            <div class="card" style="margin-top: 20px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 12px;">
                    <h3 style="font-size:16px; margin:0;">Sản phẩm chiết</h3>
                    <div>
                        <button type="button" id="add-variant" class="btn btn-outline">Thêm dung tích</button>
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background:#f8fafc; text-align:left;">
                                <th style="padding:10px; border-bottom:1px solid #e2e8f0;">Dung tích (ml)</th>
                                <th style="padding:10px; border-bottom:1px solid #e2e8f0;">SKU</th>
                                <th style="padding:10px; border-bottom:1px solid #e2e8f0;">Giá bán</th>
                                <th style="padding:10px; border-bottom:1px solid #e2e8f0;">Tồn kho</th>
                                <th style="padding:10px; border-bottom:1px solid #e2e8f0; width: 40px;"></th>
                            </tr>
                        </thead>
                        <tbody id="variants-body"></tbody>
                    </table>
                </div>
                <small style="color:#6c757d; display:block; margin-top:8px;">SKU biến thể sẽ tự sinh dựa theo SKU gốc + dung tích (ví dụ: ABC123-50ml). Bạn có thể sửa thủ công.</small>
            </div>

            <div style="border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px; display: flex; gap: 12px; justify-content: flex-end;">
                <a href="{{ route('products.index') }}" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">Hủy</a>
                <button type="submit" class="btn btn-primary" style="font-size: 13px; padding: 8px 16px;">
                    <i class="fas fa-save"></i>
                    Lưu sản phẩm
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    // Auto-generate professional SKU from Name (+ Brand) when typing
    (function(){
        const nameInput = document.getElementById('name');
        const brandInput = document.getElementById('brand');
        const skuField = document.getElementById('sku');

        function latinize(str) {
            return (str || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/gi, 'd');
        }

        function cleanTokenize(str) {
            return latinize(str)
                .toUpperCase()
                .replace(/[^A-Z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .replace(/-+/g, '-')
                .split('-')
                .filter(Boolean);
        }

        function shortCodeFromTokens(tokens, eachLen = 4, maxTokens = 4) {
            return tokens.slice(0, maxTokens).map(t => t.slice(0, eachLen)).join('-');
        }

        function generateSku() {
            const brandTokens = cleanTokenize(brandInput.value);
            const nameTokens = cleanTokenize(nameInput.value);
            if (nameTokens.length === 0 && brandTokens.length === 0) return '';
            const brandCode = brandTokens.length ? brandTokens[0].slice(0, 5) : '';
            const nameCode = shortCodeFromTokens(nameTokens, 4, 4);
            let base = [brandCode, nameCode].filter(Boolean).join('-');
            base = base.replace(/-+/g, '-').replace(/^-+|-+$/g, '');
            base = base.slice(0, 28);
            const uniq = (Date.now().toString(36)).toUpperCase().slice(-4);
            return base ? `${base}-${uniq}` : uniq;
        }

        function maybeSetSku() {
            // Only set when empty or previously auto-generated by us
            if (!skuField.value || skuField.dataset.autoset === '1') {
                const newSku = generateSku();
                if (newSku) {
                    skuField.value = newSku;
                    skuField.dataset.autoset = '1';
                }
            }
        }

        nameInput.addEventListener('input', maybeSetSku);
        brandInput.addEventListener('input', maybeSetSku);
        // If user edits SKU manually, stop auto-setting
        skuField.addEventListener('input', function(){ delete skuField.dataset.autoset; });
    })();

    // Multi-select Categories (toggle by click)
    (function(){
        const container = document.getElementById('category-multi');
        const hidden = document.getElementById('categories-hidden');
        const selected = new Set((@json(old('categories', [])) || []).map(Number));
        function syncHidden(){ hidden.value = Array.from(selected).join(','); }
        container.addEventListener('click', function(e){
            const btn = e.target.closest('button.tag-option');
            if (!btn) return;
            const id = Number(btn.dataset.id);
            if (selected.has(id)) { selected.delete(id); btn.style.background = '#fff'; }
            else { selected.add(id); btn.style.background = '#e6f4ff'; }
            syncHidden();
        });
        // init from old values
        Array.from(container.querySelectorAll('button.tag-option')).forEach(btn=>{
            const id = Number(btn.dataset.id);
            if (selected.has(id)) btn.style.background = '#e6f4ff';
        });
        syncHidden();
    })();

    // Multi-select Tags with input and toggle
    (function(){
        const wrapper = document.getElementById('tag-multi');
        const hidden = document.getElementById('tags-hidden');
        const input = document.getElementById('tag-input');
        const selected = new Set();
        function renderHidden(){ hidden.value = Array.from(selected).join(','); }
        function toggleTag(tag){
            const t = tag.trim(); if (!t) return;
            if (selected.has(t)) selected.delete(t); else selected.add(t);
            updateVisual(); renderHidden();
        }
        function ensureTagButton(tag){
            const t = tag.trim(); if (!t) return;
            let btn = wrapper.querySelector(`button.tag-option[data-tag="${CSS.escape(t)}"]`);
            if (!btn) {
                btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'tag-option';
                btn.dataset.tag = t;
                btn.textContent = t;
                btn.style.border = '1px solid #e2e8f0';
                btn.style.background = '#fff';
                btn.style.padding = '6px 10px';
                btn.style.borderRadius = '999px';
                btn.style.fontSize = '12px';
                btn.style.display = 'inline-flex';
                btn.style.alignItems = 'center';
                addRemoveIcon(btn);
                // mark as created dynamically
                btn.dataset.created = '1';
                wrapper.insertBefore(btn, input);
            }
            return btn;
        }
        function addRemoveIcon(btn){
            if (btn.querySelector('.remove-tag')) return;
            const x = document.createElement('span');
            x.textContent = '×';
            x.className = 'remove-tag';
            x.style.marginLeft = '8px';
            x.style.fontWeight = 'bold';
            x.style.cursor = 'pointer';
            btn.appendChild(x);
        }
        function updateVisual(){
            Array.from(wrapper.querySelectorAll('button.tag-option')).forEach(btn=>{
                const t = btn.dataset.tag;
                btn.style.background = selected.has(t) ? '#e6f4ff' : '#fff';
            });
        }
        // add remove icon to all existing options
        Array.from(wrapper.querySelectorAll('button.tag-option')).forEach(addRemoveIcon);
        // init from hidden old value
        (hidden.value || '').split(',').map(t=>t.trim()).filter(Boolean).forEach(t=>selected.add(t));
        updateVisual(); renderHidden();

        wrapper.addEventListener('click', function(e){
            const remove = e.target.closest('.remove-tag');
            if (remove) {
                e.preventDefault();
                e.stopPropagation();
                const btn = remove.closest('button.tag-option');
                const t = btn && btn.dataset.tag;
                if (!t) return;
                // remove from selected
                if (selected.has(t)) selected.delete(t);
                // remove chip from list (xóa khỏi danh sách sẵn có)
                btn.remove();
                renderHidden();
                return;
            }
            const btn = e.target.closest('button.tag-option');
            if (btn) { toggleTag(btn.dataset.tag); }
        });
        input.addEventListener('keydown', function(e){
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const val = input.value.replace(/,/g,'').trim();
                if (val) {
                    ensureTagButton(val);
                    selected.add(val);
                    input.value='';
                    updateVisual(); renderHidden();
                }
            }
        });
        input.addEventListener('blur', function(){
            const val = input.value.replace(/,/g,'').trim();
            if (val) {
                ensureTagButton(val);
                selected.add(val);
                input.value='';
                updateVisual(); renderHidden();
            }
        });
    })();

    // Variants dynamic rows
    const variantsBody = document.getElementById('variants-body');
    const baseSkuInput = document.getElementById('sku');
    const addBtn = document.getElementById('add-variant');

    function currentVariantIndex() {
        return variantsBody.querySelectorAll('tr').length;
    }

    function buildSku(volume) {
        const base = (baseSkuInput.value || '').trim();
        if (!base) return '';
        return base + '-' + volume + 'ml';
    }

    function addVariantRow(volume) {
        // avoid duplicate volume
        const exists = Array.from(variantsBody.querySelectorAll('input[name$="[volume_ml]"]'))
            .some(inp => parseInt(inp.value, 10) === parseInt(volume, 10));
        if (volume && exists) return;

        const idx = currentVariantIndex();
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="padding:8px; border-bottom:1px solid #eef2f7;">
                <input type="number" min="1" class="form-control" name="variants[${idx}][volume_ml]" value="${volume || ''}" style="width:120px;" />
            </td>
            <td style="padding:8px; border-bottom:1px solid #eef2f7;">
                <input type="text" class="form-control" name="variants[${idx}][sku]" value="${volume ? buildSku(volume) : ''}" />
            </td>
            <td style="padding:8px; border-bottom:1px solid #eef2f7;">
                <input type="number" min="0" step="1000" class="form-control" name="variants[${idx}][selling_price]" />
            </td>
            <td style="padding:8px; border-bottom:1px solid #eef2f7;">
                <input type="number" min="0" class="form-control" name="variants[${idx}][stock]" value="0" style="width:100px;" />
            </td>
            <td style="padding:8px; border-bottom:1px solid #eef2f7; text-align:right;">
                <button type="button" class="btn btn-outline btn-sm remove-variant">Xóa</button>
            </td>
        `;
        variantsBody.appendChild(tr);
    }

    addBtn.addEventListener('click', function() {
        addVariantRow('');
    });

    variantsBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-variant')) {
            const tr = e.target.closest('tr');
            if (tr) tr.remove();
        }
    });

    // when base SKU changes, refresh empty or auto-generated variant SKUs
    baseSkuInput.addEventListener('input', function() {
        Array.from(variantsBody.querySelectorAll('tr')).forEach((tr) => {
            const vol = tr.querySelector('input[name$="[volume_ml]"]').value;
            const skuInput = tr.querySelector('input[name$="[sku]"]');
            // overwrite only if looks like auto pattern or empty
            if (!skuInput.dataset.manual || skuInput.value === '' || /-\d+ml$/.test(skuInput.value)) {
                skuInput.value = buildSku(vol);
            }
        });
    });

    // when volume changes, auto-fill SKU if appropriate
    variantsBody.addEventListener('input', function(e) {
        if (e.target && e.target.name && e.target.name.endsWith('[volume_ml]')) {
            const tr = e.target.closest('tr');
            const vol = e.target.value;
            const skuInput = tr.querySelector('input[name$="[sku]"]');
            if (!skuInput.dataset.manual || skuInput.value === '' || /-\d+ml$/.test(skuInput.value)) {
                skuInput.value = buildSku(vol);
            }
        }
    });

    // mark manual edits on SKU
    variantsBody.addEventListener('input', function(e) {
        if (e.target && e.target.name && e.target.name.endsWith('[sku]')) {
            e.target.dataset.manual = '1';
        }
    });
</script>
<style>
    #tag-multi .remove-tag { display: none; }
    #tag-multi .tag-option:hover .remove-tag { display: inline; }
    #tag-multi .tag-option { transition: background 0.15s ease; }
    #tag-multi .tag-option:hover { background: #eef6ff !important; }
</style>
@endpush
