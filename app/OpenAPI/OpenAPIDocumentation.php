<?php

/**
 * OpenAPI/Swagger Configuration
 * 
 * Comprehensive API documentation and specification
 * Uses L5-Swagger (Swagger PHP) for automated documentation
 */

use OpenAPI\Attributes as OA;

#[OA\Info(
    version: "2.0.0",
    description: "Telemedicine Appointment & Consultation System API",
    title: "Telemedicine API",
    contact: new OA\Contact(
        name: "API Support",
        url: "https://telemedicine.local",
        email: "api@telemedicine.local"
    ),
    license: new OA\License(
        name: "Apache 2.0",
        url: "http://www.apache.org/licenses/LICENSE-2.0.html"
    )
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "Development Environment"
)]
#[OA\Server(
    url: "https://api.telemedicine.local",
    description: "Production Environment"
)]
#[OA\SecurityScheme(
    type: "http",
    description: "Login with email and password to get the authentication token",
    name: "Token based based auth header",
    in: "header",
    scheme: "bearer",
    bearerFormat: "JWT",
    securityScheme: "bearerAuth",
)]
class OpenAPIDocumentation
{
    // Documentation attributes handled above
}

// API Endpoints Documentation

#[OA\Post(
    path: "/auth/register",
    summary: "User Registration",
    description: "Register a new user account",
    requestBody: new OA\RequestBody(
        description: "User registration data",
        required: true,
        content: new OA\JsonContent(
            required: ["name", "email", "password", "password_confirmation", "role"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "John Doe"),
                new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
                new OA\Property(property: "password", type: "string", format: "password"),
                new OA\Property(property: "password_confirmation", type: "string", format: "password"),
                new OA\Property(property: "role", type: "string", enum: ["pasien", "dokter", "admin"]),
                new OA\Property(property: "phone", type: "string", example: "+62812345678"),
            ]
        )
    ),
    tags: ["Authentication"],
    responses: [
        new OA\Response(
            response: 201,
            description: "User successfully registered",
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "data", properties: [
                    new OA\Property(property: "id", type: "integer"),
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "email", type: "string"),
                    new OA\Property(property: "role", type: "string"),
                ])
            ])
        ),
        new OA\Response(response: 422, description: "Validation error"),
    ]
)]
class RegisterController
{
}

#[OA\Post(
    path: "/auth/login",
    summary: "User Login",
    description: "Authenticate user and get access token",
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password"],
            properties: [
                new OA\Property(property: "email", type: "string", format: "email"),
                new OA\Property(property: "password", type: "string", format: "password"),
            ]
        )
    ),
    tags: ["Authentication"],
    responses: [
        new OA\Response(
            response: 200,
            description: "Login successful",
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "access_token", type: "string"),
                new OA\Property(property: "token_type", type: "string", example: "Bearer"),
                new OA\Property(property: "expires_in", type: "integer"),
            ])
        ),
        new OA\Response(response: 401, description: "Invalid credentials"),
    ]
)]
class LoginController
{
}

#[OA\Get(
    path: "/appointments/available-slots",
    summary: "Get Available Appointment Slots",
    description: "Retrieve available time slots for a doctor on a specific date",
    tags: ["Appointments"],
    security: [["bearerAuth" => []]],
    parameters: [
        new OA\Parameter(
            name: "doctor_id",
            in: "query",
            required: true,
            schema: new OA\Schema(type: "integer")
        ),
        new OA\Parameter(
            name: "date",
            in: "query",
            required: true,
            schema: new OA\Schema(type: "string", format: "date")
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: "Available slots retrieved",
            content: new OA\JsonContent(properties: [
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(
                        type: "string",
                        example: "09:00"
                    )
                )
            ])
        ),
        new OA\Response(response: 404, description: "Doctor not found"),
    ]
)]
class AppointmentSlotsController
{
}

#[OA\Post(
    path: "/appointments",
    summary: "Book Appointment",
    description: "Create a new appointment booking",
    tags: ["Appointments"],
    security: [["bearerAuth" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["doctor_id", "scheduled_at", "type"],
            properties: [
                new OA\Property(property: "doctor_id", type: "integer"),
                new OA\Property(property: "scheduled_at", type: "string", format: "date-time"),
                new OA\Property(property: "type", type: "string", enum: ["online", "offline"]),
                new OA\Property(property: "notes", type: "string"),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: "Appointment created successfully",
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "data", properties: [
                    new OA\Property(property: "id", type: "integer"),
                    new OA\Property(property: "status", type: "string"),
                    new OA\Property(property: "scheduled_at", type: "string", format: "date-time"),
                ])
            ])
        ),
        new OA\Response(response: 422, description: "Validation error"),
        new OA\Response(response: 409, description: "Slot already booked"),
    ]
)]
class BookAppointmentController
{
}

#[OA\Get(
    path: "/appointments/{id}",
    summary: "Get Appointment Details",
    description: "Retrieve details of a specific appointment",
    tags: ["Appointments"],
    security: [["bearerAuth" => []]],
    parameters: [
        new OA\Parameter(
            name: "id",
            in: "path",
            required: true,
            schema: new OA\Schema(type: "integer")
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: "Appointment details",
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "data", properties: [
                    new OA\Property(property: "id", type: "integer"),
                    new OA\Property(property: "doctor_id", type: "integer"),
                    new OA\Property(property: "patient_id", type: "integer"),
                    new OA\Property(property: "status", type: "string"),
                ])
            ])
        ),
        new OA\Response(response: 404, description: "Appointment not found"),
    ]
)]
class GetAppointmentController
{
}

#[OA\Post(
    path: "/consultations/{id}/start",
    summary: "Start Consultation",
    description: "Doctor starts a consultation session",
    tags: ["Consultations"],
    security: [["bearerAuth" => []]],
    parameters: [
        new OA\Parameter(
            name: "id",
            in: "path",
            required: true,
            schema: new OA\Schema(type: "integer")
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: "Consultation started",
        ),
        new OA\Response(response: 403, description: "Not authorized to start this consultation"),
        new OA\Response(response: 404, description: "Consultation not found"),
    ]
)]
class StartConsultationController
{
}

#[OA\Post(
    path: "/consultations/{id}/end",
    summary: "End Consultation",
    description: "Doctor ends consultation and provides diagnosis/treatment",
    tags: ["Consultations"],
    security: [["bearerAuth" => []]],
    parameters: [
        new OA\Parameter(
            name: "id",
            in: "path",
            required: true,
            schema: new OA\Schema(type: "integer")
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["diagnosis", "treatment"],
            properties: [
                new OA\Property(property: "diagnosis", type: "string"),
                new OA\Property(property: "treatment", type: "string"),
                new OA\Property(property: "notes", type: "string"),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: "Consultation ended successfully"),
        new OA\Response(response: 403, description: "Not authorized"),
        new OA\Response(response: 404, description: "Consultation not found"),
    ]
)]
class EndConsultationController
{
}

#[OA\Post(
    path: "/prescriptions",
    summary: "Create Prescription",
    description: "Doctor prescribes medication for a consultation",
    tags: ["Prescriptions"],
    security: [["bearerAuth" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["consultation_id", "medication", "dosage", "duration"],
            properties: [
                new OA\Property(property: "consultation_id", type: "integer"),
                new OA\Property(property: "medication", type: "string"),
                new OA\Property(property: "dosage", type: "string", example: "2x500mg"),
                new OA\Property(property: "duration", type: "string", example: "7 days"),
                new OA\Property(property: "notes", type: "string"),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 201, description: "Prescription created"),
        new OA\Response(response: 422, description: "Validation error"),
    ]
)]
class CreatePrescriptionController
{
}

#[OA\Post(
    path: "/ratings",
    summary: "Rate Consultation",
    description: "Patient rates doctor and consultation",
    tags: ["Ratings"],
    security: [["bearerAuth" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["appointment_id", "rating"],
            properties: [
                new OA\Property(property: "appointment_id", type: "integer"),
                new OA\Property(property: "rating", type: "integer", minimum: 1, maximum: 5),
                new OA\Property(property: "comment", type: "string"),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 201, description: "Rating submitted"),
        new OA\Response(response: 422, description: "Validation error"),
    ]
)]
class CreateRatingController
{
}

#[OA\Get(
    path: "/doctor/dashboard",
    summary: "Doctor Dashboard",
    description: "Get doctor dashboard with statistics and upcoming appointments",
    tags: ["Dashboard"],
    security: [["bearerAuth" => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: "Dashboard data",
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "today_appointments", type: "integer"),
                new OA\Property(property: "total_patients", type: "integer"),
                new OA\Property(property: "average_rating", type: "number", format: "float"),
                new OA\Property(property: "recent_consultations", type: "array"),
            ])
        ),
        new OA\Response(response: 401, description: "Unauthorized"),
    ]
)]
class DoctorDashboardController
{
}

#[OA\Put(
    path: "/profile",
    summary: "Update User Profile",
    description: "Update authenticated user's profile information",
    tags: ["Profile"],
    security: [["bearerAuth" => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string"),
                new OA\Property(property: "phone", type: "string"),
                new OA\Property(property: "bio", type: "string"),
                new OA\Property(property: "specialization", type: "string"),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: "Profile updated successfully"),
        new OA\Response(response: 422, description: "Validation error"),
    ]
)]
class UpdateProfileController
{
}

#[OA\Post(
    path: "/logout",
    summary: "User Logout",
    description: "Logout user and invalidate token",
    tags: ["Authentication"],
    security: [["bearerAuth" => []]],
    responses: [
        new OA\Response(response: 200, description: "Logged out successfully"),
    ]
)]
class LogoutController
{
}

#[OA\Get(
    path: "/health",
    summary: "Health Check",
    description: "Check application health status",
    tags: ["System"],
    responses: [
        new OA\Response(
            response: 200,
            description: "Application is healthy",
            content: new OA\JsonContent(properties: [
                new OA\Property(property: "status", type: "string", example: "ok"),
                new OA\Property(property: "timestamp", type: "string", format: "date-time"),
            ])
        ),
    ]
)]
class HealthCheckController
{
}
