@component('mail::message')
# Doctor Verification Update

Hello **Dr. {{ $doctor->name }}**,

Thank you for submitting your doctor verification request.

Unfortunately, we were unable to verify your documents at this time.

## Reason for Rejection

{{ $rejection_reason ?? 'Please contact support for details.' }}

## What to Do Next

Please review the rejection reason carefully and:

1. Prepare the correct or updated documents
2. Ensure all documents meet the requirements
3. Check file format and quality
4. Resubmit your verification request

@component('mail::button', ['url' => route('doctor.verification.resubmit')])
Resubmit Verification
@endcomponent

## Requirements

Your verification must include:
- ✓ Valid KTP (Identity Card)
- ✓ SKP (Medical Competence Certification)
- ✓ Medical License/Certificate

## Support

If you have questions or need clarification about the rejection, please contact our support team. We're here to help!

Best regards,  
**{{ config('app.name') }} Team**
@endcomponent
