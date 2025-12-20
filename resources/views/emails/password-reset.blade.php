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
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
        }
        .content {
            margin: 30px 0;
        }
        .greeting {
            font-size: 16px;
            color: #333;
            margin-bottom: 15px;
        }
        .message {
            color: #555;
            font-size: 14px;
            line-height: 1.8;
            margin-bottom: 25px;
        }
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        .reset-button {
            display: inline-block;
            padding: 14px 40px;
            background-color: #4f46e5;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .reset-button:hover {
            background-color: #4338ca;
        }
        .link-text {
            color: #666;
            font-size: 12px;
            margin-top: 15px;
            word-break: break-all;
        }
        .link-text a {
            color: #4f46e5;
            text-decoration: none;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            font-size: 13px;
            color: #92400e;
        }
        .footer {
            border-top: 1px solid #eee;
            margin-top: 40px;
            padding-top: 20px;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">🏥 Telemedicine</div>
            <div class="subtitle">RSUD dr. R. Soedarsono</div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">Halo {{ $user->name }},</div>

            <div class="message">
                Anda telah meminta untuk mereset password akun Telemedicine Anda. 
                Klik tombol di bawah ini untuk melanjutkan proses reset password.
            </div>

            <!-- Reset Button -->
            <div class="button-container">
                <a href="{{ $resetUrl }}" class="reset-button">
                    Reset Password
                </a>
                <div class="link-text">
                    Atau copy link ini ke browser:<br>
                    <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
                </div>
            </div>

            <!-- Warning -->
            <div class="warning">
                ⏱️ <strong>Link berlaku selama {{ $tokenExpiry }}</strong><br>
                Link reset password ini akan kadaluarsa dalam {{ $tokenExpiry }}. 
                Jika tidak digunakan, Anda perlu meminta link baru.
            </div>

            <div class="message">
                <strong>Keamanan Akun:</strong><br>
                ✓ Jangan pernah bagikan link ini dengan siapapun<br>
                ✓ Telemedicine tidak akan pernah meminta password via email<br>
                ✓ Jika Anda tidak meminta reset ini, abaikan email ini
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; 2025 Telemedicine RSUD dr. R. Soedarsono</p>
            <p>Jl. Raya, Kota | Telepon: (0274) XXX-XXXX</p>
            <p>Email: <a href="mailto:support@telemedicine.local" style="color: #4f46e5;">support@telemedicine.local</a></p>
        </div>
    </div>
</body>
</html>
