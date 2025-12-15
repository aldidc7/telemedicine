<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
        .alert {
            background-color: #fef3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .alert-title {
            font-weight: 600;
            color: #856404;
            margin-bottom: 8px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 35px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin: 25px 0;
            transition: transform 0.3s ease;
            font-size: 16px;
        }
        .button:hover {
            transform: translateY(-2px);
        }
        .token-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            margin: 20px 0;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #666;
        }
        .warning {
            background-color: #f8d7da;
            border-left: 4px solid #f5576c;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .warning-title {
            font-weight: 600;
            color: #721c24;
            margin-bottom: 8px;
        }
        .security-tips {
            background-color: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 13px;
        }
        .security-tips strong {
            color: #667eea;
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
            <h1>🔐 Reset Password</h1>
        </div>
        <div class="content">
            <h2>Halo {{ $userName }},</h2>
            
            <p>Kami menerima permintaan untuk mereset password akun Anda di Aplikasi Telemedicine. Jika Anda tidak melakukan permintaan ini, silakan abaikan email ini.</p>
            
            <div class="alert">
                <div class="alert-title">⏰ Informasi Penting</div>
                <p style="margin: 0;">Link reset password ini akan kadaluarsa dalam <strong>{{ $expiryTime }}</strong>. Segera reset password Anda jika Anda memintanya.</p>
            </div>
            
            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetUrl }}" class="button">Reset Password Saya</a>
            </p>
            
            <p style="font-size: 13px; color: #666; text-align: center;">
                Atau copy dan paste link ini di browser Anda:
            </p>
            
            <div class="token-box">
                {{ $resetUrl }}
            </div>
            
            <div class="warning">
                <div class="warning-title">⚠️ Keamanan Akun</div>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Jangan bagikan link ini kepada siapa pun</li>
                    <li>Link ini hanya berlaku untuk akun Anda</li>
                    <li>Selalu gunakan password yang kuat</li>
                    <li>Jangan pernah memberikan password kepada siapa pun</li>
                </ul>
            </div>
            
            <div class="security-tips">
                <strong>💡 Tips Keamanan Password:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Gunakan kombinasi huruf besar, kecil, angka, dan simbol</li>
                    <li>Minimal 8 karakter</li>
                    <li>Hindari menggunakan nama atau informasi pribadi</li>
                    <li>Gunakan password yang unik untuk setiap akun</li>
                </ul>
            </div>
            
            <p>Jika Anda memiliki pertanyaan atau butuh bantuan, hubungi tim support kami.</p>
        </div>
        <div class="footer">
            <p style="margin: 0; padding: 10px 0;">© 2025 Aplikasi Telemedicine RSUD dr. R. Soedarsono</p>
            <p style="margin: 0; color: #999;">Email ini dikirim secara otomatis, harap tidak balas.</p>
        </div>
    </div>
</body>
</html>
