@extends('layouts.app')

@section('title', 'Khách hàng - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title">Khách hàng</h1>
        </div>
        <div style="display: flex; gap: 12px;">
            <button onclick="openExportModal()" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-upload"></i>
                Xuất file
            </button>
            <button onclick="openImportModal()" class="btn btn-outline" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-download"></i>
                Nhập file
            </button>
            <a href="{{ route('customers.create') }}" class="btn btn-primary" style="font-size: 13px; padding: 8px 16px;">
                <i class="fas fa-plus"></i>
                Thêm khách hàng
            </a>
            
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
            {{ session('error') }}
        </div>
    @endif

    <form method="GET" action="{{ route('customers.index') }}" id="filterForm">
        <div class="card">
            <div class="search-filter-section" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap;">
                <div class="search-container" style="flex: 1; min-width: 300px;">
                    <div class="search-bar">
                        <i class="fas fa-search" style="color: #6c757d; margin-right: 12px;"></i>
                        <input type="text" name="search" placeholder="Tìm theo tên, sđt, email" value="{{ request('search') }}" style="border: none; outline: none; width: 100%; background: none; font-size: 14px;">
                    </div>
                </div>

                <div class="filters-container" style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <select name="group_id" class="filter-select">
                        <option value="">Nhóm KH</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ request('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                    <select name="customer_type" class="filter-select">
                        <option value="">Loại KH</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" {{ request('customer_type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                    <select name="is_active" class="filter-select">
                        <option value="">Trạng thái</option>
                        <option value="1" {{ request('is_active')==='1' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="0" {{ request('is_active')==='0' ? 'selected' : '' }}>Ngừng</option>
                    </select>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-container">
            <form id="bulkDeleteForm" action="{{ route('customers.bulkDestroy') }}" method="POST">
                @csrf
                @method('DELETE')
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;"><input type="checkbox" id="select-all" style="margin: 0;"></th>
                            <th>Khách hàng</th>
                            <th>Liên hệ</th>
                            <th>Loại</th>
                            <th>Nhóm</th>
                            <th>Đơn/Chi tiêu</th>
                            <th>Trạng thái</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $c)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $c->id }}" style="margin: 0;"></td>
                                <td>
                                    <div style="font-weight:600; color:#2c3e50;">{{ $c->name }}</div>
                                    <div style="font-size:12px; color:#718096;">ID: {{ $c->id }}</div>
                                </td>
                                <td>
                                    <div style="font-size:14px; color:#4a5568;">{{ $c->phone ?? '-' }}</div>
                                    <div style="font-size:12px; color:#718096;">{{ $c->email ?? '-' }}</div>
                                </td>
                                <td><span style="padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; background-color: #e3f2fd; color: #1976d2;">{{ $c->customer_type ?? '-' }}</span></td>
                                <td>{{ $c->group?->name ?? '-' }}</td>
                                <td>
                                    <div style="font-size:14px; color:#4a5568;">{{ $c->total_orders }} đơn</div>
                                    <div style="font-size:12px; color:#718096;">{{ number_format((float)$c->total_spent, 0, ',', '.') }} đ</div>
                                </td>
                                <td>
                                    <span class="px-2 py-1 rounded-md text-xs font-medium {{ $c->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">{{ $c->is_active ? 'Hoạt động' : 'Ngừng' }}</span>
                                </td>
                                <td class="actions">
                                    <a href="{{ route('customers.show', $c->id) }}" class="btn btn-outline" style="padding:6px 10px; font-size:12px;">Xem</a>
                                    <a href="{{ route('customers.edit', $c->id) }}" class="btn btn-outline" style="padding:6px 10px; font-size:12px;">Sửa</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align:center; padding:40px; color:#6c757d;">
                                    <div style="margin-bottom: 16px;"><i class="fas fa-user" style="font-size: 32px; color: #dee2e6;"></i></div>
                                    <div style="font-size: 16px; font-weight: 500; margin-bottom: 8px;">Chưa có khách hàng nào</div>
                                    <div style="font-size: 14px;">Thêm mới hoặc nhập từ CSV.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </form>
        </div>

        <div class="table-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <div class="pagination-info" style="color: #6c757d; font-size: 14px;">
                Từ {{ $customers->firstItem() ?? 0 }} đến {{ $customers->lastItem() ?? 0 }} trên tổng {{ $customers->total() }}
            </div>
            <div class="display-options" style="display: flex; align-items: center; gap: 16px;">
                <button type="button" id="bulkDeleteBtn" class="btn btn-danger" onclick="submitBulkDelete()" style="font-size:13px; padding:8px 16px;" disabled>Xóa đã chọn</button>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 14px; color: #4a5568;">Hiển thị</span>
                    <select form="filterForm" name="per_page" class="per-page-select" onchange="document.getElementById('filterForm').submit()">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span style="font-size: 14px; color: #4a5568;">Kết quả</span>
                </div>
                <div class="pagination-controls">
                    {{ $customers->links('vendor.pagination.perfume') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal" id="importModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Nhập file khách hàng (CSV)</h3>
                <span class="close" onclick="closeImportModal()">&times;</span>
            </div>
            <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="file" class="form-label">Chọn file CSV (.csv)</label>
                    <input type="file" id="file" name="file" class="form-control" accept=".csv,.txt" required>
                </div>
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeImportModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Nhập file</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal" id="exportModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Xuất file khách hàng (CSV)</h3>
                <span class="close" onclick="closeExportModal()">&times;</span>
            </div>
            <form action="{{ route('customers.export') }}" method="GET">
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeExportModal()">Đóng</button>
                    <button type="submit" class="btn btn-primary">Xuất file</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        const filterSelects = document.querySelectorAll('.filter-select');
        const filterForm = document.getElementById('filterForm');
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', function(){
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => { filterForm.submit(); }, 500);
            });
        }
        filterSelects.forEach(select => select.addEventListener('change', function(){ filterForm.submit(); }));
    });

    const selectAllEl = document.getElementById('select-all');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    function updateBulkDeleteState(){
        const checked = document.querySelectorAll('input[name="ids[]"]:checked');
        const count = checked.length;
        bulkDeleteBtn.disabled = count === 0;
        bulkDeleteBtn.textContent = count > 0 ? `Xóa đã chọn (${count})` : 'Xóa đã chọn';
    }
    if (selectAllEl) {
        selectAllEl.addEventListener('change', function(){
            const checkboxes = document.querySelectorAll('input[name="ids[]"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkDeleteState();
        });
    }
    document.addEventListener('change', function(e){ if (e.target && e.target.name === 'ids[]') { updateBulkDeleteState(); } });
    function submitBulkDelete(){
        const checked = document.querySelectorAll('input[name="ids[]"]:checked');
        if (checked.length === 0) { alert('Vui lòng chọn ít nhất 1 khách hàng.'); return; }
        if (confirm('Bạn có chắc muốn xóa ' + checked.length + ' khách hàng đã chọn?')) {
            document.getElementById('bulkDeleteForm').submit();
        }
    }
    function openImportModal(){ document.getElementById('importModal').style.display = 'block'; }
    function closeImportModal(){ document.getElementById('importModal').style.display = 'none'; }
    function openExportModal(){ document.getElementById('exportModal').style.display = 'block'; }
    function closeExportModal(){ document.getElementById('exportModal').style.display = 'none'; }
    window.onclick = function(event) {
        const importModal = document.getElementById('importModal');
        const exportModal = document.getElementById('exportModal');
        if (event.target == importModal) { importModal.style.display = 'none'; }
        if (event.target == exportModal) { exportModal.style.display = 'none'; }
    }
</script>
@endpush


