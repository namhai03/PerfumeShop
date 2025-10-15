<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Khuyến mại PerfumeShop</title>
    <style>
        body { font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif; color:#111827; }
        .container { max-width:640px; margin:0 auto; padding:20px; }
        .card { border:1px solid #e5e7eb; border-radius:12px; padding:20px; }
        .btn { display:inline-block; padding:10px 16px; background:#2563eb; color:#fff !important; text-decoration:none; border-radius:6px; }
        .footer { color:#6b7280; font-size:12px; margin-top:16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <p>Xin chào {{ $name ?? 'Quý khách' }},</p>
            <div>{!! $html !!}</div>
            <p style="margin-top:16px;">
                <a class="btn" href="{{ url('/') }}" target="_blank">Mua ngay tại PerfumeShop</a>
            </p>
        </div>
        <div class="footer">
            Bạn nhận được email này vì đã đăng ký tại PerfumeShop. Nếu không muốn nhận ưu đãi, vui lòng bỏ qua email này.
        </div>
    </div>
</body>
</html>

