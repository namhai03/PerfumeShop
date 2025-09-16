@extends('layouts.app')

@section('title', 'Khách hàng - PerfumeShop')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h1 class="page-title">Khách hàng</h1>
        </div>
        <div style="display: flex; gap: 12px;">
            
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
                                    <div style="font-weight:600; color:#2c3e50;"><a href="{{ route('customers.show', $c->id) }}" style="color:#2c3e50; text-decoration:none;">{{ $c->name }}</a></div>
                                </td>
                                <td>
                                    <div style="font-size:14px; color:#4a5568;">{{ $c->phone ?? '-' }}</div>
                                    <div style="font-size:12px; color:#718096;">{{ $c->email ?? '-' }}</div>
                                </td>
                                <td>{{ $c->group?->name ?? '-' }}</td>
                                <td>
                                    <div style="font-size:14px; color:#4a5568;">{{ $c->total_orders }} đơn</div>
                                    <div style="font-size:12px; color:#718096;">{{ number_format((float)$c->total_spent, 0, ',', '.') }} đ</div>
                                </td>
                                <td>
                                    <span class="px-2 py-1 rounded-md text-xs font-medium {{ $c->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">{{ $c->is_active ? 'Hoạt động' : 'Ngừng' }}</span>
                                </td>
                                <td class="actions" style="display:flex; gap:8px; align-items:center;">
                                    <a href="{{ route('customers.edit', $c->id) }}" class="btn btn-outline" style="padding:6px 10px; font-size:12px;">Sửa</a>
                                   
                                </td>
                            </tr>
                        @empty
                            <tr style="height: 400px;">
                                <td colspan="7" style="text-align: center; vertical-align: middle; padding: 0; color: #6c757d;">
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 300px;">
                                        <div style="margin-bottom: 16px;"><i class="fas fa-user" style="font-size: 48px; color: #dee2e6;"></i></div>
                                        <div style="font-size: 18px; font-weight: 500; margin-bottom: 8px;">Chưa có khách hàng nào</div>
                                        <div style="font-size: 14px; color: #6c757d;">Thêm mới.</div>
                                    </div>
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

    <!-- Hidden delete form for single delete -->
    <form id="deleteCustomerForm" method="POST" action="" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Delete Confirm Modal -->
    <div class="modal" id="deleteConfirmModal" style="display: none;">
        <div class="modal-content" style="max-width: 520px;">
            <div class="modal-header">
                <h3 id="deleteModalTitle">Xóa khách hàng</h3>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div style="padding: 4px 0 12px 0; color:#4a5568;">
                <div style="background-color:#FFF5F5; color:#C53030; padding:12px; border-radius:8px; border-left:3px solid #F56565; margin-bottom:12px;">
                    <i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>
                    <span id="deleteModalMessage">Hành động này sẽ xóa khách hàng và có thể xóa các dữ liệu liên quan như đơn hàng, giao vận,... Bạn có chắc chắn muốn tiếp tục?</span>
                </div>
                <div style="font-size:13px; color:#718096;">Bạn không thể hoàn tác sau khi xóa.</div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Hủy</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteModal()">Xóa</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div class="modal" id="deleteConfirmModal" style="display: none;">
        <div class="modal-content" style="max-width: 520px;">
            <div class="modal-header">
                <h3>Xóa khách hàng</h3>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div style="padding: 4px 0 12px 0; color:#4a5568;">
                <div style="background-color:#FFF5F5; color:#C53030; padding:12px; border-radius:8px; border-left:3px solid #F56565; margin-bottom:12px;">
                    <i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>
                    Hành động này sẽ xóa khách hàng và có thể xóa các dữ liệu liên quan như đơn hàng, giao vận,... Bạn có chắc chắn muốn tiếp tục?
                </div>
                <div style="font-size:13px; color:#718096;">Bạn không thể hoàn tác sau khi xóa.</div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:8px;">
                <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Hủy</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteModal()">Xóa</button>
            </div>
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
        // mở modal xác nhận xóa nhiều
        pendingBulkDelete = true;
        document.getElementById('deleteModalTitle').textContent = 'Xóa khách hàng đã chọn';
        document.getElementById('deleteModalMessage').textContent = 'Bạn sắp xóa ' + checked.length + ' khách hàng đã chọn. Hành động này có thể xóa dữ liệu liên quan (đơn hàng, giao vận,...). Bạn chắc chắn?';
        document.getElementById('deleteConfirmModal').style.display = 'block';
    }
    function openImportModal(){ document.getElementById('importModal').style.display = 'block'; }
    function closeImportModal(){ document.getElementById('importModal').style.display = 'none'; }
    function openExportModal(){ document.getElementById('exportModal').style.display = 'block'; }
    function closeExportModal(){ document.getElementById('exportModal').style.display = 'none'; }
    window.onclick = function(event) {
        const importModal = document.getElementById('importModal');
        const exportModal = document.getElementById('exportModal');
        const deleteModal = document.getElementById('deleteConfirmModal');
        if (event.target == importModal) { importModal.style.display = 'none'; }
        if (event.target == exportModal) { exportModal.style.display = 'none'; }
        if (event.target == deleteModal) { deleteModal.style.display = 'none'; }
    }

    let pendingDeleteForm = null;
    let pendingBulkDelete = false;
    const deleteUrlBase = "{{ url('customers') }}";
    function openDeleteModalSingle(id, name){
        pendingBulkDelete = false;
        const form = document.getElementById('deleteCustomerForm');
        form.action = deleteUrlBase + '/' + id;
        pendingDeleteForm = form;
        document.getElementById('deleteModalTitle').textContent = 'Xóa khách hàng';
        document.getElementById('deleteModalMessage').textContent = 'Bạn sắp xóa khách hàng \'' + name + '\'. Hành động này có thể xóa các dữ liệu liên quan như đơn hàng, giao vận,... Bạn có chắc chắn muốn tiếp tục?';
        document.getElementById('deleteConfirmModal').style.display = 'block';
    }
    function closeDeleteModal(){
        const modal = document.getElementById('deleteConfirmModal');
        if (modal) modal.style.display = 'none';
        pendingDeleteForm = null;
    }
    function confirmDeleteModal(){
        if (pendingBulkDelete) {
            document.getElementById('bulkDeleteForm').submit();
            pendingBulkDelete = false;
            const modal = document.getElementById('deleteConfirmModal');
            if (modal) modal.style.display = 'none';
            return;
        }
        if (pendingDeleteForm) {
            const modal = document.getElementById('deleteConfirmModal');
            if (modal) modal.style.display = 'none';
            const form = pendingDeleteForm;
            pendingDeleteForm = null;
            form.submit();
        }
    }
</script>
@endpush

@push('styles')
<style>
    .search-filter-section {
        padding: 20px;
    }

    .search-bar {
        display: flex;
        align-items: center;
        background: #f7fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 16px;
        transition: all 0.2s ease;
    }

    .search-bar:focus-within {
        border-color: #4299e1;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        background: white;
    }

    .filter-select {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: white;
        font-size: 13px;
        color: #4a5568;
        min-width: 140px;
    }

    .filter-select:focus {
        outline: none;
        border-color: #4299e1;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }
</style>
@endpush


