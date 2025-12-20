<?php

namespace Tests\Unit\Services;

use App\Models\Konsultasi;
use App\Models\User;
use App\Services\Consultation\ConsultationChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationChatServiceTest extends TestCase
{
    use RefreshDatabase;

    private ConsultationChatService $chatService;
    private User $doctor;
    private User $patient;
    private Konsultasi $consultation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->chatService = app(ConsultationChatService::class);
        $this->doctor = User::factory()->create(['role' => 'doctor']);
        $this->patient = User::factory()->create(['role' => 'patient']);
        $this->consultation = Konsultasi::factory()->create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
        ]);
    }

    /**
     * Test send message
     */
    public function test_send_message()
    {
        $message = $this->chatService->sendMessage(
            $this->consultation,
            $this->patient,
            'Halo dokter'
        );

        $this->assertEquals('Halo dokter', $message->message);
        $this->assertEquals($this->patient->id, $message->sender_id);
        $this->assertDatabaseHas('consultation_messages', [
            'id' => $message->id,
            'message' => 'Halo dokter',
        ]);
    }

    /**
     * Test send message with file
     */
    public function test_send_message_with_file()
    {
        $fileData = [
            'url' => '/storage/consultations/file.pdf',
            'type' => 'document',
        ];

        $message = $this->chatService->sendMessage(
            $this->consultation,
            $this->patient,
            'File hasil lab',
            $fileData
        );

        $this->assertEquals('/storage/consultations/file.pdf', $message->file_url);
        $this->assertEquals('document', $message->file_type);
    }

    /**
     * Test get chat history
     */
    public function test_get_chat_history()
    {
        for ($i = 0; $i < 10; $i++) {
            \App\Models\ConsultationMessage::create([
                'consultation_id' => $this->consultation->id,
                'sender_id' => $i % 2 === 0 ? $this->patient->id : $this->doctor->id,
                'message' => "Message {$i}",
            ]);
        }

        $history = $this->chatService->getChatHistory($this->consultation);

        $this->assertCount(10, $history);
    }

    /**
     * Test get chat history with pagination
     */
    public function test_get_chat_history_with_pagination()
    {
        for ($i = 0; $i < 100; $i++) {
            \App\Models\ConsultationMessage::create([
                'consultation_id' => $this->consultation->id,
                'sender_id' => $this->patient->id,
                'message' => "Message {$i}",
            ]);
        }

        $history = $this->chatService->getChatHistory($this->consultation, 20, 0);
        $this->assertCount(20, $history);

        $history2 = $this->chatService->getChatHistory($this->consultation, 20, 20);
        $this->assertCount(20, $history2);
    }

    /**
     * Test mark message as read
     */
    public function test_mark_message_as_read()
    {
        $message = \App\Models\ConsultationMessage::create([
            'consultation_id' => $this->consultation->id,
            'sender_id' => $this->doctor->id,
            'message' => 'Test',
            'is_read' => false,
        ]);

        $this->chatService->markAsRead($message);

        $this->assertTrue($message->fresh()->is_read);
        $this->assertNotNull($message->fresh()->read_at);
    }

    /**
     * Test mark all as read
     */
    public function test_mark_all_as_read()
    {
        for ($i = 0; $i < 5; $i++) {
            \App\Models\ConsultationMessage::create([
                'consultation_id' => $this->consultation->id,
                'sender_id' => $this->doctor->id,
                'message' => "Message {$i}",
                'is_read' => false,
            ]);
        }

        $count = $this->chatService->markAllAsRead($this->consultation, $this->patient);

        $this->assertEquals(5, $count);
        $this->assertEquals(0, $this->consultation->messages()->unread()->count());
    }

    /**
     * Test get unread count
     */
    public function test_get_unread_count()
    {
        for ($i = 0; $i < 3; $i++) {
            \App\Models\ConsultationMessage::create([
                'consultation_id' => $this->consultation->id,
                'sender_id' => $this->doctor->id,
                'message' => "Message {$i}",
                'is_read' => false,
            ]);
        }

        $count = $this->chatService->getUnreadCount($this->consultation, $this->patient);
        $this->assertEquals(3, $count);
    }

    /**
     * Test delete message
     */
    public function test_delete_message()
    {
        $message = \App\Models\ConsultationMessage::create([
            'consultation_id' => $this->consultation->id,
            'sender_id' => $this->patient->id,
            'message' => 'Delete me',
        ]);

        $this->chatService->deleteMessage($message);

        $this->assertTrue($message->fresh()->trashed());
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
            'message' => 'Itu adalah gejala flu',
        ]);

        $results = $this->chatService->searchMessages($this->consultation, 'demam');

        $this->assertCount(1, $results);
        $this->assertStringContainsString('demam', $results[0]->message);
    }

    /**
     * Test get last message
     */
    public function test_get_last_message()
    {
        \App\Models\ConsultationMessage::create([
            'consultation_id' => $this->consultation->id,
            'sender_id' => $this->patient->id,
            'message' => 'First',
        ]);

        sleep(1);

        $last = \App\Models\ConsultationMessage::create([
            'consultation_id' => $this->consultation->id,
            'sender_id' => $this->doctor->id,
            'message' => 'Last',
        ]);

        $result = $this->chatService->getLastMessage($this->consultation);

        $this->assertEquals($last->id, $result->id);
    }

    /**
     * Test sender is doctor
     */
    public function test_sender_is_doctor()
    {
        $message = \App\Models\ConsultationMessage::create([
            'consultation_id' => $this->consultation->id,
            'sender_id' => $this->doctor->id,
            'message' => 'Doctor message',
        ]);

        $this->assertTrue($message->isSenderDoctor());
        $this->assertFalse($message->isSenderPatient());
    }
}
