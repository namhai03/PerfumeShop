@extends('layouts.app')

@section('content')
<h1 class="page-title">Khuyến mại AI</h1>

<div class="card" style="margin-bottom:16px;">
    <div class="card-header"><h3>Thiết lập mục tiêu</h3></div>
    <div class="card-body">
        <div class="grid" style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;">
            <div>
                <label class="label">Mục tiêu</label>
                <select id="objective" class="input">
                    <option value="push_stock">Đẩy tồn kho</option>
                    <option value="increase_aov">Tăng AOV</option>
                    <option value="reactivation">Kích hoạt lại khách</option>
                    <option value="seasonal">Theo mùa vụ</option>
                </select>
            </div>
            <div>
                <label class="label">Trần giảm (%)</label>
                <input id="max_discount_percent" class="input" type="number" value="20" min="5" max="50" />
            </div>
            <div>
                <label class="label">Ngưỡng đơn tối thiểu (₫)</label>
                <input id="min_order_amount" class="input" type="number" value="0" step="10000" />
            </div>
            <div>
                <label class="label">Khoảng thời gian (ngày)</label>
                <input id="window_days" class="input" type="number" value="30" min="7" max="180" />
            </div>
            <div style="display:flex;align-items:flex-end; gap:8px;">
                <button id="btnSuggest" class="btn btn-primary" onclick="suggestCampaigns(true)">Gợi ý chiến dịch</button>
                <span id="suggestStatus" style="font-size:12px;color:#6b7280;display:none;">Đang xử lý...</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Đề xuất chiến dịch</h3></div>
    <div class="card-body">
        <div id="suggestions"></div>
        <div id="metaPanel" style="margin-top:12px;font-size:12px;color:#6b7280;"></div>
    </div>
</div>

<script>
async function suggestCampaigns(withSeed){
    document.getElementById('btnSuggest').disabled = true;
    document.getElementById('suggestStatus').style.display = 'inline';
    const payload = {
        objective: document.getElementById('objective').value,
        max_discount_percent: Number(document.getElementById('max_discount_percent').value),
        min_order_amount: Number(document.getElementById('min_order_amount').value),
        window_days: Number(document.getElementById('window_days').value)
    };
    if (withSeed) payload.seed = Date.now();
    const res = await fetch('/api/promotions/ai/suggest', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    document.getElementById('btnSuggest').disabled = false;
    document.getElementById('suggestStatus').style.display = 'none';
    if (!data.success) return;
    renderSuggestions(data.suggestions || []);
    renderMeta(data.meta || {});
}

function renderSuggestions(list){
    const wrap = document.getElementById('suggestions');
    wrap.innerHTML = '';
    if (!list.length){
        wrap.innerHTML = '<div>Không có đề xuất.</div>';
        return;
    }
    list.forEach((s,i)=>{
        const el = document.createElement('div');
        el.className = 'card';
        el.style.marginBottom = '12px';
        el.innerHTML = `
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-weight:600;">${s.name_suggestion || 'Đề xuất #' + (i+1)}</div>
                        <div style="font-size:12px;color:#555;">Mục tiêu: ${s.objective} | Loại: ${s.type}/${s.scope} | Giảm: ${s.discount_value}</div>
                        <div style="font-size:12px;color:#555;">Ước tính doanh thu tăng: ${formatCurrency(s.predicted_uplift_revenue)} | Dự kiến lượt dùng: ${s.predicted_usage}</div>
                        ${renderAnalysis(s)}
                    </div>
                    <div style="display:flex;gap:8px;">
                        <button class="btn" onclick='generateCopy(${JSON.stringify(s)})'>Tạo nội dung</button>
                        
                        <button class="btn btn-primary" onclick='openLaunch(${JSON.stringify(s)})'>Khởi chạy</button>
                        
                    </div>
                </div>
                <div id="copy_${s.campaign_id}" style="margin-top:8px;"></div>
                <div id="image_${s.campaign_id}" style="margin-top:8px;"></div>
            </div>
        `;
        wrap.appendChild(el);
    })
}
function escapeHtml(str){
    if (!str) return '';
    return str.replace(/[&<>"']/g,function(m){return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[m]);});
}

function renderAnalysis(s){
    const text = s.analysis ? `<pre style=\"white-space:pre-wrap;background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;padding:8px;margin-top:6px;\">${escapeHtml(s.analysis)}</pre>` : '';
    const chartId = `chart_${s.campaign_id}`;
    let chartBlock = '';
    if (s.analysis_data){
        chartBlock = `<div style=\"margin-top:8px;\"><canvas id=\"${chartId}\" height=\"120\"></canvas></div>`;
        // defer drawing after DOM insertion
        setTimeout(function(){ try{ drawSuggestionChart('${chartId}', s); } catch(e){} }, 0);
    }
    if (!text && !chartBlock) return '';
    return `<details style=\"margin-top:6px;\"><summary style=\"cursor:pointer;color:#2563eb;\">Phân tích</summary>${text}${chartBlock}</details>`;
}

function drawSuggestionChart(canvasId, s){
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    const isAov = s.objective === 'increase_aov' && s.analysis_data && (s.analysis_data.aov || s.analysis_data.p75);
    if (isAov){
        const data = {
            labels: ['AOV', 'p75'],
            datasets: [{
                label: 'Giá trị (₫)',
                backgroundColor: ['#60a5fa','#34d399'],
                data: [s.analysis_data.aov || 0, s.analysis_data.p75 || 0]
            }]
        };
        new Chart(ctx, { type: 'bar', data, options: { plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true } } } });
        // KPI donut below if available
        if (s.analysis_data.kpi){
            const k = s.analysis_data.kpi;
            const donutId = canvasId + '_kpi';
            const holder = document.createElement('div');
            holder.innerHTML = `<canvas id="${donutId}" height="100"></canvas>`;
            ctx.parentElement.appendChild(holder);
            new Chart(document.getElementById(donutId), {
                type: 'doughnut',
                data: {
                    labels: ['Giao thành công','Thất bại','Trả hàng'],
                    datasets: [{ data: [k.delivered||0, k.failed||0, k.returned||0], backgroundColor: ['#10b981','#ef4444','#f59e0b'] }]
                },
                options: { plugins:{ legend:{ position:'bottom' } } }
            });
        }
        // Projection line: baseline vs projected
        if (s.analysis_data.projection){
            const p = s.analysis_data.projection;
            const projId = canvasId + '_proj';
            const holder2 = document.createElement('div');
            holder2.innerHTML = `<canvas id="${projId}" height="100"></canvas>`;
            ctx.parentElement.appendChild(holder2);
            new Chart(document.getElementById(projId), {
                type: 'bar',
                data: {
                    labels: ['Baseline','Projected'],
                    datasets: [{ label: 'Doanh thu (₫)', data: [p.baseline_revenue||0, p.projected_revenue||0], backgroundColor: ['#9ca3af','#2563eb'] }]
                },
                options: { plugins:{ legend:{ position:'bottom' } }, scales:{ y:{ beginAtZero:true } } }
            });
        }
        return;
    }
    const items = (s.analysis_data && s.analysis_data.items) ? s.analysis_data.items : [];
    if (!items.length) return;
    const labels = items.map(i=>i.name);
    const stock = items.map(i=>i.stock||0);
    const qty30 = items.map(i=>i.qty30||0);
    const doc = items.map(i=>i.doc||0);
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Tồn', backgroundColor: '#a78bfa', data: stock },
                { label: 'Bán 30d', backgroundColor: '#60a5fa', data: qty30 },
            ]
        },
        options: { responsive:true, plugins:{ legend:{ position:'top' } }, scales:{ y:{ beginAtZero:true } } }
    });
    // Add KPI donut if available
    if (s.analysis_data && s.analysis_data.kpi){
        const k = s.analysis_data.kpi;
        const donutId = canvasId + '_kpi';
        const holder = document.createElement('div');
        holder.innerHTML = `<canvas id="${donutId}" height="100"></canvas>`;
        ctx.parentElement.appendChild(holder);
        new Chart(document.getElementById(donutId), {
            type: 'doughnut',
            data: {
                labels: ['Giao thành công','Thất bại','Trả hàng'],
                datasets: [{ data: [k.delivered||0, k.failed||0, k.returned||0], backgroundColor: ['#10b981','#ef4444','#f59e0b'] }]
            },
            options: { plugins:{ legend:{ position:'bottom' } } }
        });
    }
    // Projection baseline vs projected revenue if available
    if (s.analysis_data && s.analysis_data.projection){
        const p = s.analysis_data.projection;
        const projId = canvasId + '_proj';
        const holder2 = document.createElement('div');
        holder2.innerHTML = `<canvas id="${projId}" height="100"></canvas>`;
        ctx.parentElement.appendChild(holder2);
        new Chart(document.getElementById(projId), {
            type: 'bar',
            data: {
                labels: ['Baseline','Projected'],
                datasets: [{ label: 'Doanh thu (₫)', data: [p.baseline_revenue||0, p.projected_revenue||0], backgroundColor: ['#9ca3af','#2563eb'] }]
            },
            options: { plugins:{ legend:{ position:'bottom' } }, scales:{ y:{ beginAtZero:true } } }
        });
    }
}

function renderMeta(meta){
    const el = document.getElementById('metaPanel');
    if (!meta || (!meta.aov && !meta.p75)) { el.innerHTML = ''; return; }
    el.innerHTML = `AOV ~ ${formatCurrency(meta.aov || 0)} | p75 ~ ${formatCurrency(meta.p75 || 0)} | Cửa sổ ${meta.window_days || 30} ngày`;
}

function formatCurrency(v){
    try { return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v||0); } catch(e){ return v; }
}

async function generateCopy(suggestion){
    const res = await fetch('/api/promotions/ai/generate-copy', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ suggestion })
    });
    const data = await res.json();
    if (!data.success) return;
    const c = data.copy;
    const target = document.getElementById('copy_' + suggestion.campaign_id);
    target.innerHTML = `
        <div style="padding:8px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;">
            <div style="font-weight:600;">${c.title}</div>
            <div style="font-size:12px;color:#4b5563;margin:4px 0;">${c.subtitle}</div>
            <div style="font-size:13px;margin:6px 0;">${c.long_description.replace(/\n/g,'<br/>')}</div>
            <button class="btn btn-outline" onclick='openLaunch(${JSON.stringify(suggestion)}, ${JSON.stringify(c)})'>Khởi chạy với nội dung này</button>
        </div>
    `;
}

async function generateImage(suggestion, btn){
    const container = document.getElementById('image_' + suggestion.campaign_id);
    container.innerHTML = '<div style="font-size:12px;color:#6b7280;">Đang tạo ảnh quảng cáo...</div>';
    btn.disabled = true;
    const res = await fetch('/api/promotions/ai/generate-image', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ suggestion })
    });
    const data = await res.json();
    btn.disabled = false;
    if (!data.success || !data.image_url) {
        // Fallback: tạo poster đơn giản bằng Canvas tại client
        const url = await buildFallbackPoster(suggestion);
        if (!url) {
            container.innerHTML = '<div class="alert alert-danger">Tạo ảnh thất bại. Kiểm tra cấu hình AI hoặc thử lại.</div>';
            return;
        }
        container.innerHTML = `
            <div class="card" style="padding:8px;">
                <div style="font-size:12px;color:#6b7280;margin-bottom:6px;">Ảnh poster (fallback)</div>
                <img src="${url}" alt="promo" style="max-width:100%; border-radius:8px; border:1px solid #e5e7eb"/>
                <div style="margin-top:8px; display:flex; gap:8px;">
                    <a download="poster.png" href="${url}" class="btn btn-outline">Tải xuống</a>
                </div>
            </div>
        `;
        return;
    }
    const url = data.image_url;
    container.innerHTML = `
        <div class="card" style="padding:8px;">
            <div style="font-size:12px;color:#6b7280;margin-bottom:6px;">Ảnh đề xuất cho chiến dịch</div>
            <img src="${url}" alt="promo" style="max-width:100%; border-radius:8px; border:1px solid #e5e7eb"/>
        </div>
    `;
}

async function launchCampaign(suggestion, copy){
    const settings = {
        priority: Number(document.getElementById('launch_priority').value) || 10,
        is_stackable: document.getElementById('launch_stackable').checked,
        usage_limit: Number(document.getElementById('launch_usage_limit').value) || null,
        usage_limit_per_customer: Number(document.getElementById('launch_usage_per_customer').value) || null,
        start_at: document.getElementById('launch_start').value || null,
        end_at: document.getElementById('launch_end').value || null,
        applicable_sales_channels: getCheckedValues('launch_channels')
    };
    const payload = { suggestion, copy: copy || null, settings };
    const res = await fetch('/api/promotions/ai/launch', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success && data.redirect_url){
        window.location.href = data.redirect_url;
    }
}

function getCheckedValues(name){
    return Array.from(document.querySelectorAll(`input[name="${name}"]:checked`)).map(i=>i.value);
}

function openLaunch(suggestion, copy){
    const modal = document.getElementById('launchModal');
    modal.style.display = 'block';
    modal.dataset.suggestion = JSON.stringify(suggestion);
    modal.dataset.copy = copy ? JSON.stringify(copy) : '';
    document.getElementById('launch_title').innerText = suggestion.name_suggestion || 'AI Campaign';
}

function closeLaunch(){
    const modal = document.getElementById('launchModal');
    modal.style.display = 'none';
}

function confirmLaunch(){
    const modal = document.getElementById('launchModal');
    const suggestion = JSON.parse(modal.dataset.suggestion || '{}');
    const copyStr = modal.dataset.copy || '';
    const copy = copyStr ? JSON.parse(copyStr) : null;
    launchCampaign(suggestion, copy);
}

function openEmail(suggestion){
    const modal = document.getElementById('emailModal');
    modal.style.display = 'block';
    modal.dataset.suggestion = JSON.stringify(suggestion);
    document.getElementById('email_subject').value = `[PerfumeShop] ${suggestion.name_suggestion || 'Ưu đãi đặc biệt'}`;
    document.getElementById('email_html').value = `
        <h2>${suggestion.name_suggestion || 'Ưu đãi đặc biệt'}</h2>
        <p>Ưu đãi: ${suggestion.discount_value} ${suggestion.type==='percent' ? '%' : '₫'}</p>
        <p>${suggestion.analysis ? suggestion.analysis.replaceAll('\n','<br/>') : ''}</p>
    `;
}

async function sendEmail(){
    const subject = document.getElementById('email_subject').value.trim();
    const html = document.getElementById('email_html').value.trim();
    const customerIds = document.getElementById('email_customer_ids').value.trim()
        .split(',').map(s=>parseInt(s)).filter(Number.isFinite);
    const res = await fetch('/api/promotions/ai/send-email', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ subject, html, customer_ids: customerIds })
    });
    const data = await res.json();
    if (data.success){
        alert(`Đã gửi email tới ${data.sent} khách hàng.`);
        closeEmail();
    } else {
        alert('Gửi email thất bại');
    }
}

function closeEmail(){ document.getElementById('emailModal').style.display = 'none'; }
</script>

<style>
.label { display:block; font-size:12px; color:#4b5563; margin-bottom:4px; }
.input { width:100%; padding:8px; border:1px solid #e5e7eb; border-radius:6px; }
.btn { padding:8px 12px; border:1px solid #cbd5e1; border-radius:6px; background:white; cursor:pointer; }
.btn-primary { background:#2563eb; border-color:#2563eb; color:#fff; }
.btn-outline { background:white; color:#2563eb; border-color:#2563eb; }
.card { border:1px solid #e5e7eb; border-radius:8px; }
.card-header { padding:10px 12px; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; }
.card-body { padding:12px; }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
async function buildFallbackPoster(s){
    try {
        const width = 1024, height = 1024;
        const canvas = document.createElement('canvas');
        canvas.width = width; canvas.height = height;
        const ctx = canvas.getContext('2d');
        // Background gradient
        const grad = ctx.createLinearGradient(0,0,width,height);
        grad.addColorStop(0,'#0f172a');
        grad.addColorStop(1,'#1e293b');
        ctx.fillStyle = grad; ctx.fillRect(0,0,width,height);

        // Title
        const title = s.name_suggestion || 'Ưu đãi đặc biệt';
        ctx.fillStyle = '#e2e8f0';
        ctx.font = 'bold 64px Inter, Segoe UI, Arial';
        wrapText(ctx, title, 64, 140, width-128, 72);

        // Discount badge
        const discount = s.type === 'percent' ? (s.discount_value + '% OFF') : (s.discount_value ? (Number(s.discount_value).toLocaleString('vi-VN') + '₫ OFF') : 'Ưu đãi');
        ctx.fillStyle = '#93c5fd';
        ctx.font = 'bold 52px Inter, Segoe UI, Arial';
        ctx.fillText(discount, 64, 240);

        // CTA
        ctx.fillStyle = '#a7f3d0';
        ctx.font = 'bold 40px Inter, Segoe UI, Arial';
        ctx.fillText('Mua ngay tại PerfumeShop', 64, 300);

        // Product thumbnails (best effort)
        const items = (s.products || []).slice(0,3);
        let x = 64, y = 360, thumb = 260, gap = 24;
        for (let i=0; i<items.length; i++){
            try {
                if (!items[i].image) continue;
                const img = await loadImage(items[i].image);
                const ratio = Math.min(thumb/img.width, thumb/img.height);
                const w = img.width*ratio, h = img.height*ratio;
                const cx = x + i*(thumb+gap);
                // frame
                ctx.fillStyle = 'rgba(255,255,255,0.06)';
                ctx.fillRect(cx-8, y-8, thumb+16, thumb+16);
                ctx.strokeStyle = 'rgba(255,255,255,0.18)';
                ctx.strokeRect(cx-8, y-8, thumb+16, thumb+16);
                // image
                ctx.drawImage(img, cx + (thumb-w)/2, y + (thumb-h)/2, w, h);
            } catch(e){}
        }

        // Footer
        ctx.fillStyle = '#94a3b8';
        ctx.font = '28px Inter, Segoe UI, Arial';
        ctx.fillText('Ưu đãi có điều kiện. Kiểm tra chi tiết tại cửa hàng.', 64, height-64);

        return canvas.toDataURL('image/png');
    } catch (e){
        return null;
    }
}

function wrapText(ctx, text, x, y, maxWidth, lineHeight){
    const words = text.split(' '); let line = '';
    for (let n=0; n<words.length; n++){
        const testLine = line + words[n] + ' ';
        const metrics = ctx.measureText(testLine);
        if (metrics.width > maxWidth && n>0){
            ctx.fillText(line, x, y); line = words[n] + ' '; y += lineHeight;
        } else { line = testLine; }
    }
    ctx.fillText(line, x, y);
}

function loadImage(src){
    return new Promise((resolve,reject)=>{
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = ()=> resolve(img);
        img.onerror = reject;
        img.src = src;
    });
}
</script>
<div id="emailModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Gửi email khuyến mại</h3>
      <span class="close" onclick="closeEmail()">&times;</span>
    </div>
    <div>
      <div class="form-group">
        <label class="form-label">Tiêu đề</label>
        <input id="email_subject" class="form-control" type="text" />
      </div>
      <div class="form-group">
        <label class="form-label">Nội dung (HTML)</label>
        <textarea id="email_html" class="form-control" rows="8"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">ID khách hàng (tùy chọn, phân tách dấu phẩy)</label>
        <input id="email_customer_ids" class="form-control" type="text" placeholder="vd: 1,2,3" />
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;">
        <button class="btn" onclick="closeEmail()">Hủy</button>
        <button class="btn btn-primary" onclick="sendEmail()">Gửi</button>
      </div>
    </div>
  </div>
</div>
<div id="launchModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="launch_title">Khởi chạy chiến dịch</h3>
      <span class="close" onclick="closeLaunch()">&times;</span>
    </div>
    <div>
      <div class="form-group">
        <label class="form-label">Độ ưu tiên (1-100)</label>
        <input id="launch_priority" class="form-control" type="number" min="1" max="100" value="10" />
      </div>
      <div class="form-group">
        <label class="form-label">Cho phép cộng dồn</label>
        <input id="launch_stackable" type="checkbox" />
      </div>
      <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Giới hạn tổng lượt dùng</label>
          <input id="launch_usage_limit" class="form-control" type="number" min="1" />
        </div>
        <div class="form-group">
          <label class="form-label">Giới hạn/khách</label>
          <input id="launch_usage_per_customer" class="form-control" type="number" min="1" />
        </div>
      </div>
      <div class="grid" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Bắt đầu</label>
          <input id="launch_start" class="form-control" type="datetime-local" />
        </div>
        <div class="form-group">
          <label class="form-label">Kết thúc</label>
          <input id="launch_end" class="form-control" type="datetime-local" />
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Kênh áp dụng</label>
        <div style="display:flex;gap:12px;">
          <label><input type="checkbox" name="launch_channels" value="online" /> Online</label>
          <label><input type="checkbox" name="launch_channels" value="offline" /> Offline</label>
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;">
        <button class="btn" onclick="closeLaunch()">Hủy</button>
        <button class="btn btn-primary" onclick="confirmLaunch()">Khởi chạy</button>
      </div>
    </div>
  </div>
</div>
@endsection


