@extends('layouts.app')

@section('title', 'Danh mục sản phẩm - PerfumeShop')

@section('content')
    <div style="display:flex; justify-content: space-between; align-items:center; margin-bottom:24px;">
        <h1 class="page-title">Danh mục sản phẩm</h1>
        <a href="{{ route('categories.create') }}" class="btn btn-primary" style="font-size:13px; padding:8px 16px;">
            <i class="fas fa-plus"></i>
            Thêm danh mục
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle" style="margin-right:8px;"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle" style="margin-right:8px;"></i>
            {{ session('error') }}
        </div>
    @endif

    <form method="GET" action="{{ route('categories.index') }}" id="filterForm">
        <div class="card">
            <div class="search-filter-section" style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
                <div class="search-container" style="flex:1; min-width:300px;">
                    <div class="search-bar">
                        <i class="fas fa-search" style="color:#6c757d; margin-right:12px;"></i>
                        <input type="text" name="search" placeholder="Tìm kiếm danh mục" value="{{ request('search') }}" style="border:none; outline:none; width:100%; background:none; font-size:14px;">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-outline" style="font-size:13px; padding:8px 16px;">Lọc</button>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:50px;"><input type="checkbox" style="margin:0;" disabled></th>
                        <th>Danh mục</th>
                        <th>Số lượng</th>
                        
                        
                        <th>Trạng thái</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td><input type="checkbox" style="margin:0;" disabled></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:40px; height:40px; background:#f8f9fa; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#6c757d;">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight:600; color:#2c3e50; margin-bottom:4px;">
                                            <a href="{{ route('categories.show', $category) }}" style="text-decoration:none; color:inherit;">{{ $category->name }}</a>
                                        </div>
                                        
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php($count = $categoryCounts[$category->id] ?? 0)
                                <a href="{{ route('products.index', ['category' => $category->id]) }}" class="link" title="Xem sản phẩm thuộc danh mục này">{{ $count }}</a>
                            </td>
                            
                            
                            <td>
                                <span class="px-2 py-1 rounded-md text-xs font-medium {{ $category->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    {{ $category->is_active ? 'Đang dùng' : 'Ngừng' }}
                                </span>
                            </td>
                            
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:40px; color:#6c757d;">
                                <div style="margin-bottom:16px;"><i class="fas fa-list" style="font-size:32px; color:#dee2e6;"></i></div>
                                <div style="font-size:16px; font-weight:500; margin-bottom:8px;">Chưa có danh mục nào</div>
                                <div style="font-size:14px;">Hãy tạo danh mục đầu tiên.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="table-footer" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding-top:20px; border-top:1px solid #e2e8f0;">
            <div class="pagination-info" style="color:#6c757d; font-size:14px;">
                Từ {{ $categories->firstItem() ?? 0 }} đến {{ $categories->lastItem() ?? 0 }} trên tổng {{ $categories->total() }}
            </div>
            <div class="display-options" style="display:flex; align-items:center; gap:16px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:14px; color:#4a5568;">Hiển thị</span>
                    <select name="per_page" form="filterForm" class="per-page-select">
                        @php($pp = (int) request('per_page', 20))
                        <option value="20" {{ $pp===20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $pp===50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $pp===100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span style="font-size:14px; color:#4a5568;">Kết quả</span>
                </div>
                <div class="pagination-controls">
                    {{ $categories->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const filterForm = document.getElementById('filterForm');
        const searchInput = filterForm.querySelector('input[name="search"]');
        const selects = filterForm.querySelectorAll('.filter-select, .per-page-select');

        let timer;
        if (searchInput){
            searchInput.addEventListener('input', function(){
                clearTimeout(timer);
                timer = setTimeout(()=> filterForm.submit(), 500);
            });
        }
        selects.forEach(s => s.addEventListener('change', ()=> filterForm.submit()));
    });
</script>
@endpush
@endsection


