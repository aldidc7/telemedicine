<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Consultation;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Phase 6: Payment Integration Tests
 * 
 * Test suite untuk payment gateway integration, invoice generation, 
 * dan payment confirmation flow
 */
class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $patient;
    protected User $doctor;
    protected Consultation $consultation;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->patient = User::factory()->patient()->create([
            'name' => 'Patient Test',
            'email' => 'patient@test.com',
        ]);

        $this->doctor = User::factory()->doctor()->create([
            'name' => 'Doctor Test',
            'email' => 'doctor@test.com',
        ]);

        // Create test consultation
        $this->consultation = Consultation::factory()->create([
            'patient_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'status' => 'completed',
            'consultation_fee' => 150000,
        ]);
    }

    // ============== PAYMENT CREATION TESTS ==============

    /** @test */
    public function patient_can_initiate_payment()
    {
        $this->actingAs($this->patient);

        $response = $this->postJson('/api/v1/payments', [
            'consultation_id' => $this->consultation->id,
            'amount' => 150000,
            'payment_method' => 'stripe',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.status', 'pending');
        $this->assertDatabaseHas('payments', [
            'consultation_id' => $this->consultation->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function payment_requires_valid_consultation()
    {
        $this->actingAs($this->patient);

        $response = $this->postJson('/api/v1/payments', [
            'consultation_id' => 99999,
            'amount' => 150000,
            'payment_method' => 'stripe',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function payment_requires_authentication()
    {
        $response = $this->postJson('/api/v1/payments', [
            'consultation_id' => $this->consultation->id,
            'amount' => 150000,
            'payment_method' => 'stripe',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function payment_method_must_be_valid()
    {
        $this->actingAs($this->patient);

        $response = $this->postJson('/api/v1/payments', [
            'consultation_id' => $this->consultation->id,
            'amount' => 150000,
            'payment_method' => 'invalid_method',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function only_patient_can_initiate_payment_for_own_consultation()
    {
        $otherPatient = User::factory()->patient()->create();
        $this->actingAs($otherPatient);

        $response = $this->postJson('/api/v1/payments', [
            'consultation_id' => $this->consultation->id,
            'amount' => 150000,
            'payment_method' => 'stripe',
        ]);

        $response->assertStatus(403);
    }

    // ============== PAYMENT CONFIRMATION TESTS ==============

    /** @test */
    public function payment_can_be_confirmed()
    {
        $payment = Payment::factory()->pending()->create([
            'consultation_id' => $this->consultation->id,
            'user_id' => $this->patient->id,
        ]);

        $this->actingAs($this->patient);

        $response = $this->postJson("/api/v1/payments/{$payment->id}/confirm", [
            'stripe_payment_id' => 'pi_test123',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'confirmed',
        ]);
    }

    /** @test */
    public function confirmed_payment_creates_invoice()
    {
        $payment = Payment::factory()->pending()->create([
            'consultation_id' => $this->consultation->id,
            'user_id' => $this->patient->id,
            'amount' => 150000,
        ]);

        $this->actingAs($this->patient);

        $this->postJson("/api/v1/payments/{$payment->id}/confirm", [
            'stripe_payment_id' => 'pi_test123',
        ]);

        $this->assertDatabaseHas('invoices', [
            'payment_id' => $payment->id,
            'user_id' => $this->patient->id,
        ]);
    }

    /** @test */
    public function payment_confirmation_requires_valid_stripe_id()
    {
        $payment = Payment::factory()->pending()->create([
            'consultation_id' => $this->consultation->id,
            'user_id' => $this->patient->id,
        ]);

        $this->actingAs($this->patient);

        $response = $this->postJson("/api/v1/payments/{$payment->id}/confirm");

        $response->assertStatus(422);
    }

    // ============== PAYMENT RETRIEVAL TESTS ==============

    /** @test */
    public function patient_can_view_own_payment()
    {
        $payment = Payment::factory()->confirmed()->create([
            'user_id' => $this->patient->id,
        ]);

        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/payments/{$payment->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $payment->id);
    }

    /** @test */
    public function patient_cannot_view_others_payment()
    {
        $otherPatient = User::factory()->patient()->create();
        $payment = Payment::factory()->confirmed()->create([
            'user_id' => $otherPatient->id,
        ]);

        $this->actingAs($this->patient);

        $response = $this->getJson("/api/v1/payments/{$payment->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function patient_can_list_own_payments()
    {
        Payment::factory()->confirmed()->count(5)->create([
            'user_id' => $this->patient->id,
        ]);

        $this->actingAs($this->patient);

        $response = $this->getJson('/api/v1/payments?status=confirmed');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.user_id', $this->patient->id);
    }

    // ============== REFUND TESTS ==============

    /** @test */
    public function patient_can_request_refund()
    {
        $payment = Payment::factory()->confirmed()->create([
            'user_id' => $this->patient->id,
        ]);

        $this->actingAs($this->patient);

        $response = $this->postJson("/api/v1/payments/{$payment->id}/refund", [
            'reason' => 'Changed mind',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function refund_creates_audit_log()
    {
        $payment = Payment::factory()->confirmed()->create([
            'user_id' => $this->patient->id,
        ]);

        $this->actingAs($this->patient);

        $this->postJson("/api/v1/payments/{$payment->id}/refund", [
            'reason' => 'Test refund',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payment_refunded',
            'entity_id' => $payment->id,
        ]);
    }

    // ============== PAYMENT METHOD TESTS ==============

    /** @test */
    public function payment_supports_stripe()
    {
        $this->actingAs($this->patient);

        $response = $this->postJson('/api/v1/payments', [
            'consultation_id' => $this->consultation->id,
            'amount' => 150000,
            'payment_method' => 'stripe',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function payment_supports_gcash()
    {
        $this->actingAs($this->patient);

        $response = $this->postJson('/api/v1/payments', [
            'consultation_id' => $this->consultation->id,
            'amount' => 150000,
            'payment_method' => 'gcash',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function payment_supports_bank_transfer()
    {
        $this->actingAs($this->patient);

        $response = $this->postJson('/api/v1/payments', [
            'consultation_id' => $this->consultation->id,
            'amount' => 150000,
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertStatus(201);
    }

    // ============== PAYMENT WEBHOOK TESTS ==============

    /** @test */
    public function webhook_updates_payment_status()
    {
        $payment = Payment::factory()->pending()->create();

        $response = $this->postJson('/api/v1/webhooks/payment', [
            'id' => 'evt_test',
            'type' => 'charge.captured',
            'data' => [
                'object' => [
                    'id' => 'pi_test123',
                    'amount' => $payment->amount * 100,
                    'status' => 'succeeded',
                ],
            ],
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function invalid_webhook_signature_rejected()
    {
        $response = $this->postJson('/api/v1/webhooks/payment', [
            'invalid' => 'data',
        ], [
            'Stripe-Signature' => 'invalid_signature',
        ]);

        $response->assertStatus(403);
    }
}
