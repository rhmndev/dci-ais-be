<!doctype html>
<html lang="en-US">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>Reset Password Email Template</title>
    <meta name="description" content="Reset Password Email Template.">
    <style type="text/css">
        a:hover {text-decoration: underline !important;}
        .reset-button {
            background: #20e277;
            text-decoration: none !important;
            font-weight: 500;
            margin-top: 35px;
            color: #fff;
            text-transform: uppercase;
            font-size: 14px;
            padding: 12px 30px;
            display: inline-block;
            border-radius: 50px;
            box-shadow: 0 4px 8px rgba(32, 226, 119, 0.3);
            transition: all 0.3s ease;
        }
        .reset-button:hover {
            background: #1bc96a;
            box-shadow: 0 6px 12px rgba(32, 226, 119, 0.4);
            transform: translateY(-2px);
        }
        .container {
            max-width: 670px;
            margin: 0 auto;
            background-color: #f2f3f8;
            font-family: 'Open Sans', sans-serif;
        }
        .header {
            text-align: center;
            padding: 40px 0;
        }
        .content {
            background: #fff;
            border-radius: 8px;
            padding: 40px;
            margin: 20px;
            box-shadow: 0 6px 18px 0 rgba(0,0,0,0.06);
        }
        .title {
            color: #1e1e2d;
            font-weight: 500;
            margin: 0 0 20px 0;
            font-size: 28px;
            font-family: 'Rubik', sans-serif;
        }
        .divider {
            display: inline-block;
            vertical-align: middle;
            margin: 20px 0;
            border-bottom: 2px solid #e0e0e0;
            width: 60px;
        }
        .description {
            color: #455056;
            font-size: 16px;
            line-height: 1.6;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>

<body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #f2f3f8;" leftmargin="0">
    <div class="container">
        <div class="header">
            <img src="https://media.licdn.com/dms/image/v2/C560BAQE13m_saMfn7g/company-logo_200_200/company-logo_200_200/0/1661152322194?e=2147483647&v=beta&t=BcpoGiGZF55vlEU0edwg4f0FMyZh7_NzsUdhTjZW89M" 
                 alt="Company Logo" style="width: 120px; height: auto;">
        </div>
        
        <div class="content">
            <h1 class="title">Reset Your Password</h1>
            <div class="divider"></div>
            
            <p class="description">
                Hello! We received a request to reset your password for your account. 
                If you didn't make this request, you can safely ignore this email.
            </p>
            
            <p class="description">
                To reset your password, click the button below. This link will expire in 60 minutes 
                for security reasons.
            </p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{config('app.front_url').'/auth/reset-password?token='.$token.'&email='.urlencode($email ?? '')}}" 
                   class="reset-button">
                    Reset Password
                </a>
            </div>
            
            <p class="description" style="font-size: 14px; color: #666;">
                If the button doesn't work, you can copy and paste this link into your browser:<br>
                <a href="{{config('app.front_url').'/auth/reset-password?token='.$token.'&email='.urlencode($email ?? '')}}" 
                   style="color: #20e277; word-break: break-all;">
                    {{config('app.front_url').'/auth/reset-password?token='.$token.'&email='.urlencode($email ?? '')}}
                </a>
            </p>
        </div>
        
        <div class="footer">
            <p>This email was sent to you because someone requested a password reset for your account.</p>
            <p>If you didn't request this, please ignore this email and your password will remain unchanged.</p>
            <p style="margin-top: 20px;">
                Best regards,<br>
                <strong>{{ config('app.name') }} Team</strong>
            </p>
        </div>
    </div>
</body>

</html>