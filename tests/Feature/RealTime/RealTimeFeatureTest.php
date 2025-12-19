<?php

namespace Tests\Feature\RealTime;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Models\PesanChat;
use App\Events\MessageSent;
use App\Events\MessageRead;
use App\Events\ConsultationStarted;
use App\Events\ConsultationEnded;
use App\Events\ConsultationStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

/**
 * Real-Time Features Broadcasting Tests
 * 
 * Tests verify that broadcasting events are dispatched correctly
 * when consultation and messaging actions occur.
 */
class RealTimeFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $pasienUser;
    protected $dokterUser;
    protected $konsultasi;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create pasien user with associated patient using factory
        $this->pasienUser = User::factory()->create(['role' => 'pasien']);
        $pasien = Pasien::factory()->create(['user_id' => $this->pasienUser->id]);
        
        // Create dokter user with associated doctor using factory
        $this->dokterUser = User::factory()->create(['role' => 'dokter']);
        $dokter = Dokter::factory()->create(['user_id' => $this->dokterUser->id]);
        
        // Create consultation using factory
        $this->konsultasi = Konsultasi::factory()->create([
            'patient_id' => $pasien->id,
            'doctor_id' => $dokter->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test message sent event broadcasts to consultation channel
     */
    public function test_message_sent_event_broadcasts_to_consultation_channel()
    {
        Event::fake();

        // Create a message without sender_type (not in DB schema)
        $message = PesanChat::factory()->create([
            'consultation_id' => $this->konsultasi->id,
            'sender_id' => $this->pasienUser->id,
        ]);

        broadcast(new MessageSent($message, $message->consultation_id))->toOthers();

        // Verify event was dispatched
        Event::assertDispatched(MessageSent::class);
    }

    /**
     * Test message read event broadcasts
     */
    public function test_message_read_event_broadcasts()
    {
        Event::fake();

        // Create a message
        $message = PesanChat::factory()->create([
            'consultation_id' => $this->konsultasi->id,
        ]);

        // Dispatch read event with required parameters
        broadcast(new MessageRead($message, $message->consultation_id, $this->dokterUser->id))->toOthers();

        // Verify
        Event::assertDispatched(MessageRead::class);
    }

    /**
     * Test consultation started event
     */
    public function test_consultation_started_event_broadcasts()
    {
        Event::fake();
        broadcast(new ConsultationStarted($this->konsultasi))->toOthers();
        Event::assertDispatched(ConsultationStarted::class);
    }

    /**
     * Test consultation ended event
     */
    public function test_consultation_ended_event_broadcasts()
    {
        Event::fake();
        broadcast(new ConsultationEnded($this->konsultasi))->toOthers();
        Event::assertDispatched(ConsultationEnded::class);
    }

    /**
     * Test consultation status changed event
     */
    public function test_consultation_status_changed_event_broadcasts()
    {
        Event::fake();
        broadcast(new ConsultationStatusChanged($this->konsultasi, 'pending', 'active'))->toOthers();
        Event::assertDispatched(ConsultationStatusChanged::class);
    }

    /**
     * Test message event contains correct consultation data
     */
    public function test_message_event_data_includes_consultation_id()
    {
        Event::fake();

        $message = PesanChat::factory()->create([
            'consultation_id' => $this->konsultasi->id,
            'message' => 'Test message',
        ]);

        broadcast(new MessageSent($message, $message->consultation_id))->toOthers();

        Event::assertDispatched(MessageSent::class, function ($event) {
            return $event->message->consultation_id === $this->konsultasi->id;
        });
    }

    /**
     * Test multiple messages dispatch multiple events
     */
    public function test_multiple_messages_dispatch_multiple_events()
    {
        Event::fake();

        $message1 = PesanChat::factory()->create([
            'consultation_id' => $this->konsultasi->id,
        ]);
        broadcast(new MessageSent($message1, $message1->consultation_id))->toOthers();

        $message2 = PesanChat::factory()->create([
            'consultation_id' => $this->konsultasi->id,
        ]);
        broadcast(new MessageSent($message2, $message2->consultation_id))->toOthers();

        Event::assertDispatchedTimes(MessageSent::class, 2);
    }

    /**
     * Test consultation status transitions broadcast events
     */
    public function test_consultation_status_transitions_broadcast_correctly()
    {
        Event::fake();

        // Simulate status transitions
        broadcast(new ConsultationStatusChanged($this->konsultasi, 'pending', 'active'))->toOthers();
        broadcast(new ConsultationStatusChanged($this->konsultasi, 'active', 'closed'))->toOthers();

        Event::assertDispatchedTimes(ConsultationStatusChanged::class, 2);
    }

    /**
     * Test event uses private consultation channel
     */
    public function test_message_event_uses_private_consultation_channel()
    {
        $message = PesanChat::factory()->create([
            'consultation_id' => $this->konsultasi->id,
        ]);

        $event = new MessageSent($message, $message->consultation_id);
        $channels = $event->broadcastOn();
        
        // Should broadcast to private consultation channel
        $this->assertNotEmpty($channels);
        $this->assertStringContainsString('consultation.' . $this->konsultasi->id, $channels[0]->name);
    }

    /**
     * Test message broadcast has required data fields
     */
    public function test_message_broadcast_has_required_fields()
    {
        $message = PesanChat::factory()->create([
            'consultation_id' => $this->konsultasi->id,
            'message' => 'Appointment confirmed',
        ]);

        $event = new MessageSent($message, $message->consultation_id);
        $broadcastData = $event->broadcastWith();

        // Verify required fields exist in broadcast data
        $this->assertArrayHasKey('id', $broadcastData);
        $this->assertArrayHasKey('consultation_id', $broadcastData);
        $this->assertArrayHasKey('message', $broadcastData);
        $this->assertEquals($message->id, $broadcastData['id']);
    }
}

