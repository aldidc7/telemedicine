<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4f46e5; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; border-radius: 0 0 5px 5px; }
        .button { display: inline-block; background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verifikasi Email Anda</h1>
        </div>
        
        <div class="content">
            <p>Halo <strong>{{ $user->name }}</strong>,</p>
            
            <p>Terima kasih telah mendaftar di Aplikasi Telemedicine RSUD dr. R. Soedarsono.</p>
            
            <p>Untuk menyelesaikan proses registrasi, silakan verifikasi email Anda dengan mengklik tombol di bawah:</p>
            
            <center>
                <a href="{{ $verificationUrl }}" class="button">Verifikasi Email</a>
            </center>
            
            <p>Atau salin dan paste link ini ke browser Anda:</p>
            <p style="word-break: break-all; background-color: #f3f4f6; padding: 10px; border-radius: 3px;">
                {{ $verificationUrl }}
            </p>
            
            <p><strong>Catatan:</strong> Link ini akan berlaku selama 24 jam.</p>
            
            <p>Jika Anda tidak melakukan registrasi, abaikan email ini.</p>
            
            <div class="footer">
                <p>© 2025 Aplikasi Telemedicine RSUD dr. R. Soedarsono. Semua hak dilindungi.</p>
            </div>
        </div>
    </div>
</body>
</html>
