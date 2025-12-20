@component('mail::message')
# Doctor Verification Approved

Hello **Dr. {{ $doctor->name }}**,

Congratulations! Your doctor verification has been **successfully approved**.

You can now:
- ✓ Accept patient consultations
- ✓ Prescribe medicines  
- ✓ Access all doctor features
- ✓ Earn from consultations

@component('mail::button', ['url' => route('doctor.profile')])
View Your Profile
@endcomponent

## What's Next?

1. Update your profile with availability
2. Set your consultation fee
3. Start accepting patient consultations

## Need Help?

If you have any questions, our support team is here to help.

Thanks for joining {{ config('app.name') }}!

Best regards,  
**{{ config('app.name') }} Team**
@endcomponent
