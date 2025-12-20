@extends('layouts.pdf')

@section('content')
<div style="font-family: Arial, sans-serif; color: #333;">
    <!-- Header -->
    <table style="width: 100%; margin-bottom: 30px;">
        <tr>
            <td style="width: 60%;">
                <h1 style="margin: 0; color: #2563eb;">{{ config('app.name') }}</h1>
                <p style="margin: 5px 0; font-size: 12px; color: #666;">
                    {{ config('app.address', '') }}<br>
                    {{ config('app.phone', '') }}
                </p>
            </td>
            <td style="width: 40%; text-align: right;">
                <div style="font-size: 12px; color: #666;">
                    <strong>Prescription</strong><br>
                    Date: {{ $generatedDate }}<br>
                    Ref: #{{ $prescription->id }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Patient & Doctor Info -->
    <table style="width: 100%; margin-bottom: 30px; font-size: 12px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div style="border: 1px solid #ddd; padding: 12px;">
                    <strong style="color: #2563eb;">Patient Information</strong><br><br>
                    <strong>Name:</strong> {{ $patient->name }}<br>
                    <strong>Email:</strong> {{ $patient->email }}<br>
                    <strong>Phone:</strong> {{ $patient->phone_number ?? '-' }}<br>
                    <strong>Date of Birth:</strong> {{ $patient->date_of_birth ? $patient->date_of_birth->format('d/m/Y') : '-' }}
                </div>
            </td>
            <td style="width: 50%; padding-left: 20px; vertical-align: top;">
                <div style="border: 1px solid #ddd; padding: 12px;">
                    <strong style="color: #2563eb;">Doctor Information</strong><br><br>
                    <strong>Name:</strong> Dr. {{ $doctor->name }}<br>
                    <strong>Specialization:</strong> {{ $doctor->specialization ?? '-' }}<br>
                    <strong>Email:</strong> {{ $doctor->email }}<br>
                    <strong>License:</strong> {{ $doctor->medical_license ?? '-' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Consultation Details -->
    <div style="border: 1px solid #ddd; padding: 12px; margin-bottom: 30px; font-size: 12px;">
        <strong style="color: #2563eb; display: block; margin-bottom: 10px;">Consultation Details</strong>
        <table style="width: 100%;">
            <tr>
                <td><strong>Consultation Date:</strong> {{ $consultation->created_at->format('d/m/Y H:i') }}</td>
                <td><strong>Status:</strong> {{ ucfirst($consultation->status) }}</td>
            </tr>
            <tr style="margin-top: 5px;">
                <td colspan="2"><strong>Chief Complaint:</strong> {{ $consultation->reason ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- Medicines -->
    <div style="margin-bottom: 30px;">
        <strong style="color: #2563eb; display: block; margin-bottom: 10px; font-size: 14px;">Prescribed Medicines</strong>
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <thead>
                <tr style="background-color: #f3f4f6; border-bottom: 2px solid #2563eb;">
                    <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Medicine</th>
                    <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Dosage</th>
                    <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Frequency</th>
                    <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Duration</th>
                </tr>
            </thead>
            <tbody>
                @forelse($medicines as $medicine)
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            <strong>{{ $medicine['name'] ?? 'N/A' }}</strong>
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            {{ $medicine['dosage'] ?? '-' }}
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            {{ $medicine['frequency'] ?? '-' }}
                        </td>
                        <td style="padding: 8px; border: 1px solid #ddd;">
                            {{ $medicine['duration'] ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding: 10px; text-align: center; color: #999;">
                            No medicines prescribed
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Additional Notes -->
    @if($prescription->notes)
        <div style="background-color: #f9fafb; border-left: 4px solid #2563eb; padding: 12px; margin-bottom: 30px; font-size: 12px;">
            <strong style="color: #2563eb;">Additional Notes</strong><br>
            <p style="margin: 8px 0; white-space: pre-wrap;">{{ $prescription->notes }}</p>
        </div>
    @endif

    <!-- Instructions -->
    <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; padding: 12px; margin-bottom: 30px; font-size: 11px;">
        <strong style="color: #1e40af;">Usage Instructions:</strong>
        <ul style="margin: 8px 0; padding-left: 20px;">
            <li>Take medicines as prescribed above</li>
            <li>If any side effects occur, contact your doctor immediately</li>
            <li>Do not exceed the recommended dosage</li>
            <li>Keep medicines in a cool, dry place away from sunlight</li>
            <li>Do not share medicines with others</li>
        </ul>
    </div>

    <!-- Footer -->
    <div style="border-top: 1px solid #ddd; padding-top: 20px; font-size: 11px; color: #666;">
        <p style="margin: 0;">
            <strong>Doctor Signature:</strong> Dr. {{ $doctor->name }}<br>
            <strong>Date:</strong> {{ $generatedDate }}<br>
            <strong>License:</strong> {{ $doctor->medical_license ?? 'N/A' }}
        </p>
        <p style="margin-top: 15px; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px;">
            This is an electronically generated prescription from {{ config('app.name') }} telemedicine platform.<br>
            For patient safety and verification, please check the hospital records.
        </p>
    </div>
</div>
@endsection
