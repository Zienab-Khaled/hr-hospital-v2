<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'ar' ? 'ar-SA-u-nu-latn' : app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ app()->getLocale() === 'ar' ? 'بيانات الدخول' : 'Login Credentials' }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <h2 style="color: #2563eb;">{{ app()->getLocale() === 'ar' ? 'أهلاً بك في نظام المستشفى' : 'Welcome to the Hospital System' }}</h2>
        <p>{{ app()->getLocale() === 'ar' ? 'تم إنشاء حساب لك بنجاح. يرجى استخدام البيانات التالية للدخول:' : 'Your account has been successfully created. Please use the following credentials to log in:' }}</p>

        <div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>{{ app()->getLocale() === 'ar' ? 'اسم المستخدم:' : 'Username:' }}</strong> {{ $username }}</p>
            <p style="margin: 5px 0;"><strong>{{ app()->getLocale() === 'ar' ? 'كلمة المرور:' : 'Password:' }}</strong> {{ $password }}</p>
        </div>

        <p>{{ app()->getLocale() === 'ar' ? 'يرجى تغيير كلمة المرور بعد أول تسجيل دخول.' : 'Please change your password after your first login.' }}</p>

        <p style="margin-top: 30px; font-size: 12px; color: #6b7280;">
            {{ app()->getLocale() === 'ar' ? 'هذه رسالة تلقائية، يرجى عدم الرد عليها.' : 'This is an automated message, please do not reply.' }}
        </p>
    </div>
</body>
</html>
