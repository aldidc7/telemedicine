<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Dokter Disetujui</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #667eea;
            font-size: 18px;
            margin-bottom: 20px;
        }
        .content p {
            margin: 15px 0;
            font-size: 14px;
        }
        .details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .details-row:last-child {
            border-bottom: none;
        }
        .details-label {
            font-weight: 600;
            color: #666;
            min-width: 150px;
        }
        .details-value {
            color: #333;
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
        .success-badge {
            display: inline-block;
            background-color: #4caf50;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Pendaftaran Disetujui</h1>
        </div>
        <div class="content">
            <span class="success-badge">✓ PERMOHONAN DISETUJUI</span>
            
            <h2>Halo {{ $doctorName }},</h2>
            
            <p>Kami dengan senang hati mengumumkan bahwa pendaftaran Anda sebagai dokter di Aplikasi Telemedicine RSUD dr. R. Soedarsono telah <strong>disetujui</strong> dan <strong>diverifikasi</strong>.</p>
            
            <div class="details">
                <div class="details-row">
                    <span class="details-label">Nama Dokter:</span>
                    <span class="details-value">{{ $doctorName }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Spesialisasi:</span>
                    <span class="details-value">{{ $specialization }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Status:</span>
                    <span class="details-value"><strong style="color: #4caf50;">Terverifikasi</strong></span>
                </div>
                <div class="details-row">
                    <span class="details-label">Tanggal Persetujuan:</span>
                    <span class="details-value">{{ now()->format('d M Y H:i') }}</span>
                </div>
            </div>
            
            <p>Anda sekarang dapat:</p>
            <ul style="margin: 15px 0; padding-left: 20px;">
                <li>Login ke dashboard dokter</li>
                <li>Melihat jadwal konsultasi</li>
                <li>Berkomunikasi dengan pasien</li>
                <li>Mengelola profil Anda</li>
            </ul>
            
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $loginUrl }}" class="button">Masuk ke Aplikasi</a>
            </p>
            
            <p>Jika Anda memiliki pertanyaan atau memerlukan bantuan, silakan hubungi tim support kami.</p>
            
            <p>Terima kasih telah menjadi bagian dari Aplikasi Telemedicine RSUD dr. R. Soedarsono!</p>
        </div>
        <div class="footer">
            <p style="margin: 0; padding: 10px 0;">© 2025 Aplikasi Telemedicine RSUD dr. R. Soedarsono</p>
            <p style="margin: 0; color: #999;">Email ini dikirim secara otomatis, harap tidak balas.</p>
        </div>
    </div>
</body>
</html>
