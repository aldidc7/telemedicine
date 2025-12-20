<?php

namespace Tests\Feature\Api;

use App\Models\Konsultasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/** @noinspection PhpUndefinedMethodInspection */
class ConsultationChatControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $doctor;
    private User $patient;
    private Konsultasi $consultation;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users
        $this->doctor = User::factory()->create(['role' => 'doctor']);
        $this->patient = User::factory()->create(['role' => 'patient']);

        // Create consultation
        $this->consultation = Konsultasi::factory()->create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Test patient dapat mengirim message
     */
    public function test_patient_can_send_message()
    {
        $response = $this->actingAs($this->patient)
            ->postJson("/api/v1/consultations/{$this->consultation->id}/messages", [
                'message' => 'Halo dokter, saya ada pertanyaan',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'consultation_id',
                'sender_id',
                'message',
                'sender' => ['id', 'name', 'avatar_url'],
            ],
        ]);

        $this->assertDatabaseHas('consultation_messages', [
            'consultation_id' => $this->consultation->id,
            'sender_id' => $this->patient->id,
            'message' => 'Halo dokter, saya ada pertanyaan',
        ]);
    }

    /**
     * Test doctor dapat mengirim message
     */
    public function test_doctor_can_send_message()
    {
        $response = $this->actingAs($this->doctor)
            ->postJson("/api/v1/consultations/{$this->consultation->id}/messages", [
                'message' => 'Baik, silakan jelaskan gejalanya',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('consultation_messages', [
            'sender_id' => $this->doctor->id,
            'message' => 'Baik, silakan jelaskan gejalanya',
        ]);
    }

    /**
     * Test non-participant tidak bisa send message
     */
    public function test_non_participant_cannot_send_message()
    {
        /** @var User $other */
        $other = User::factory()->create();

        $response = $this->actingAs($other)
            ->postJson("/api/v1/consultations/{$this->consultation->id}/messages", [
                'message' => 'Halo',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test message tidak boleh kosong
     */
    public function test_message_is_required()
    {
        $response = $this->actingAs($this->patient)
            ->postJson("/api/v1/consultations/{$this->consultation->id}/messages", [
                'message' => '',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test get chat history
     */
    public function test_get_chat_history()
    {
        // Create messages
        for ($i = 0; $i < 5; $i++) {
            $consultation_messages = \App\Models\ConsultationMessage::create([
                'consultation_id' => $this->consultation->id,
                'sender_id' => $i % 2 === 0 ? $this->patient->id : $this->doctor->id,
                'message' => "Message {$i}",
            ]);
        }

        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/consultations/{$this->consultation->id}/messages");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'messages',
                'unread_count',
                'total',
            ],
        ]);
        $response->assertJsonPath('data.total', 5);
    }

    /**
     * Test get messages with pagination
     */
    public function test_get_messages_with_pagination()
    {
        for ($i = 0; $i < 100; $i++) {
            \App\Models\ConsultationMessage::create([
                'consultation_id' => $this->consultation->id,
                'sender_id' => $this->patient->id,
                'message' => "Message {$i}",
            ]);
        }

        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/consultations/{$this->consultation->id}/messages?limit=20&offset=0");

        $response->assertStatus(200);
        $this->assertCount(20, $response['data']['messages']);
    }

    /**
     * Test mark message as read
     */
    public function test_mark_message_as_read()
    {
        $message = \App\Models\ConsultationMessage::create([
            'consultation_id' => $this->consultation->id,
            'sender_id' => $this->doctor->id,
            'message' => 'Test message',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->patient)
            ->postJson("/api/v1/consultation-messages/{$message->id}/read");

        $response->assertStatus(200);
        $this->assertTrue($message->fresh()->is_read);
        $this->assertNotNull($message->fresh()->read_at);
    }

    /**
     * Test delete message - hanya sender
     */
    public function test_only_sender_can_delete_message()
    {
        $message = \App\Models\ConsultationMessage::create([
            'consultation_id' => $this->consultation->id,
            'sender_id' => $this->patient->id,
            'message' => 'Delete me',
        ]);

        // Patient (sender) can delete
        $response = $this->actingAs($this->patient)
            ->deleteJson("/api/v1/consultation-messages/{$message->id}");
        $response->assertStatus(200);

        // Doctor (non-sender) cannot delete
        $message2 = \App\Models\ConsultationMessage::create([
            'consultation_id' => $this->consultation->id,
            'sender_id' => $this->patient->id,
            'message' => 'Cannot delete',
        ]);

        $response = $this->actingAs($this->doctor)
            ->deleteJson("/api/v1/consultation-messages/{$message2->id}");
        $response->assertStatus(403);
    }

    /**
     * Test search messages
     */
    public function test_search_messages()
    {
        \App\Models\ConsultationMessage::create([
            'consultation_id' => $this->consultation->id,
            'sender_id' => $this->patient->id,
            'message' => 'Saya punya demam tinggi',
        ]);

        \App\Models\ConsultationMessage::create([
            'consultation_id' => $this->consultation->id,
            'sender_id' => $this->doctor->id,
            'message' => 'Istirahatlah yang cukup',
        ]);

        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/consultations/{$this->consultation->id}/messages/search?q=demam");

        $response->assertStatus(200);
        $this->assertCount(1, $response['data']);
        $this->assertStringContainsString('demam', $response['data'][0]['message']);
    }

    /**
     * Test unread count
     */
    public function test_get_unread_count()
    {
        // Doctor sends 3 messages
        for ($i = 0; $i < 3; $i++) {
            \App\Models\ConsultationMessage::create([
                'consultation_id' => $this->consultation->id,
                'sender_id' => $this->doctor->id,
                'message' => "Doctor message {$i}",
                'is_read' => false,
            ]);
        }

        $response = $this->actingAs($this->patient)
            ->getJson("/api/v1/consultations/{$this->consultation->id}/messages/unread-count");

        $response->assertStatus(200);
        $response->assertJsonPath('data.unread_count', 3);
    }

    /**
     * Test non-participant cannot view messages
    /**
     * Test non-participant tidak bisa view messages
     */
    public function test_non_participant_cannot_view_messages()
    {
        /** @var User $other */
        $other = User::factory()->create();

        $response = $this->actingAs($other)
            ->getJson("/api/v1/consultations/{$this->consultation->id}/messages");

        $response->assertStatus(403);
    }

    /**
     * Test messages are auto-marked read when fetching
     */
    public function test_messages_auto_marked_read_on_fetch()
    {
        // Doctor sends message
        \App\Models\ConsultationMessage::create([
            'consultation_id' => $this->consultation->id,
            'sender_id' => $this->doctor->id,
            'message' => 'Test',
            'is_read' => false,
        ]);

        // Patient fetches messages
        $this->actingAs($this->patient)
            ->getJson("/api/v1/consultations/{$this->consultation->id}/messages");

        // Message should be marked read
        $message = $this->consultation->messages()->first();
        $this->assertTrue($message->is_read);
        $this->assertNotNull($message->read_at);
    }
}
