<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Pendaftaran Dokter</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .content h2 {
            color: #f5576c;
            font-size: 18px;
            margin-bottom: 20px;
        }
        .content p {
            margin: 15px 0;
            font-size: 14px;
        }
        .alert {
            background-color: #fff3cd;
            border-left: 4px solid #f5576c;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .alert-title {
            font-weight: 600;
            color: #f5576c;
            margin-bottom: 10px;
        }
        .reason-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #f5576c;
            margin: 15px 0;
        }
        .reason-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
            display: block;
        }
        .reason-text {
            color: #333;
            font-style: italic;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin: 20px 0;
            transition: transform 0.3s ease;
        }
        .button:hover {
            transform: translateY(-2px);
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-radius: 0 0 8px 8px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #666;
        }
        .rejected-badge {
            display: inline-block;
            background-color: #f5576c;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        ul {
            margin: 15px 0;
            padding-left: 20px;
        }
        li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Perbaruan Pendaftaran</h1>
        </div>
        <div class="content">
            <span class="rejected-badge">PERMOHONAN TIDAK DISETUJUI</span>
            
            <h2>Halo {{ $doctorName }},</h2>
            
            <p>Terima kasih telah mendaftar sebagai dokter di Aplikasi Telemedicine RSUD dr. R. Soedarsono. Kami telah meninjau permohonan pendaftaran Anda dengan cermat.</p>
            
            <div class="alert">
                <div class="alert-title">Sayangnya, permohonan Anda tidak dapat disetujui pada saat ini.</div>
                
                <div class="reason-box">
                    <span class="reason-label">Alasan Penolakan:</span>
                    <div class="reason-text">{{ $reason }}</div>
                </div>
            </div>
            
            <p><strong>Apa yang dapat Anda lakukan?</strong></p>
            <ul>
                <li><strong>Tinjau kembali alasan penolakan</strong> - Pastikan Anda memenuhi semua persyaratan yang diminta</li>
                <li><strong>Perbarui dokumen Anda</strong> - Jika tersedia informasi tambahan atau pembaruan dokumen</li>
                <li><strong>Hubungi tim support</strong> - Untuk pertanyaan atau klarifikasi lebih lanjut</li>
                <li><strong>Coba daftar ulang</strong> - Dengan dokumen dan informasi yang telah diperbaharui</li>
            </ul>
            
            <p>Kami sangat menghargai minat Anda untuk menjadi bagian dari layanan telemedicine kami. Jika Anda memiliki pertanyaan atau ingin mendiskusikan hasil ini lebih lanjut, jangan ragu untuk menghubungi kami di <strong>{{ $contactEmail }}</strong>.</p>
            
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}/login" class="button">Kembali ke Halaman Login</a>
            </p>
        </div>
        <div class="footer">
            <p style="margin: 0; padding: 10px 0;">© 2025 Aplikasi Telemedicine RSUD dr. R. Soedarsono</p>
            <p style="margin: 0; color: #999;">Email ini dikirim secara otomatis, harap tidak balas.</p>
        </div>
    </div>
</body>
</html>
