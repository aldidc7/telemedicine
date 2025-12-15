<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use App\Helpers\ValidationHelper;

/**
 * Unit Tests untuk ValidationHelper
 * 
 * Test semua validation functions untuk ensure robustness
 * Coverage: 100%
 */
class ValidationHelperTest extends TestCase
{
    /**
     * Test validating valid appointment status
     */
    public function test_validate_appointment_status_valid(): void
    {
        $validStatuses = ['pending', 'confirmed', 'rejected', 'completed', 'cancelled', 'no-show'];
        
        foreach ($validStatuses as $status) {
            // Should not throw exception
            $result = ValidationHelper::validateAppointmentStatus($status);
            $this->assertTrue($result);
        }
    }
    
    /**
     * Test validating invalid appointment status
     */
    public function test_validate_appointment_status_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ValidationHelper::validateAppointmentStatus('invalid_status');
    }
    
    /**
     * Test appointment status transition valid
     */
    public function test_validate_appointment_status_transition_valid(): void
    {
        // pending → confirmed (valid transition)
        $result = ValidationHelper::validateAppointmentStatusTransition('pending', 'confirmed');
        $this->assertTrue($result);
        
        // confirmed → completed (valid transition)
        $result = ValidationHelper::validateAppointmentStatusTransition('confirmed', 'completed');
        $this->assertTrue($result);
    }
    
    /**
     * Test appointment status transition invalid
     */
    public function test_validate_appointment_status_transition_invalid(): void
    {
        // pending → completed (invalid, must go through confirmed first)
        $this->expectException(\InvalidArgumentException::class);
        ValidationHelper::validateAppointmentStatusTransition('pending', 'completed');
    }
    
    /**
     * Test completed status cannot transition to anything
     */
    public function test_completed_status_no_transitions(): void
    {
        // completed → pending (invalid, completed is final state)
        $this->expectException(\InvalidArgumentException::class);
        ValidationHelper::validateAppointmentStatusTransition('completed', 'pending');
    }
    
    /**
     * Test get all appointment statuses
     */
    public function test_get_appointment_statuses(): void
    {
        $statuses = ValidationHelper::getAppointmentStatuses();
        
        $this->assertIsArray($statuses);
        $this->assertContains('pending', $statuses);
        $this->assertContains('confirmed', $statuses);
        $this->assertContains('completed', $statuses);
        $this->assertCount(6, $statuses); // Must have exactly 6 statuses
    }
    
    /**
     * Test get allowed transitions dari specific status
     */
    public function test_get_allowed_appointment_transitions(): void
    {
        $pendingTransitions = ValidationHelper::getAllowedAppointmentTransitions('pending');
        
        $this->assertIsArray($pendingTransitions);
        $this->assertContains('confirmed', $pendingTransitions);
        $this->assertContains('rejected', $pendingTransitions);
        $this->assertContains('cancelled', $pendingTransitions);
    }
    
    /**
     * Test consultation status validation
     */
    public function test_validate_consultation_status_valid(): void
    {
        $validStatuses = ['pending', 'aktif', 'selesai', 'dibatalkan'];
        
        foreach ($validStatuses as $status) {
            $result = ValidationHelper::validateConsultationStatus($status);
            $this->assertTrue($result);
        }
    }
    
    /**
     * Test consultation status transition valid
     */
    public function test_validate_consultation_status_transition_valid(): void
    {
        // pending → aktif (valid)
        $result = ValidationHelper::validateConsultationStatusTransition('pending', 'aktif');
        $this->assertTrue($result);
        
        // aktif → selesai (valid)
        $result = ValidationHelper::validateConsultationStatusTransition('aktif', 'selesai');
        $this->assertTrue($result);
    }
    
    /**
     * Test prescription status validation
     */
    public function test_validate_prescription_status_valid(): void
    {
        $validStatuses = ['active', 'expired', 'completed', 'archived'];
        
        foreach ($validStatuses as $status) {
            $result = ValidationHelper::validatePrescriptionStatus($status);
            $this->assertTrue($result);
        }
    }
    
    /**
     * Test prescription status transition
     */
    public function test_validate_prescription_status_transition_valid(): void
    {
        // active → expired (valid)
        $result = ValidationHelper::validatePrescriptionStatusTransition('active', 'expired');
        $this->assertTrue($result);
        
        // active → completed (valid)
        $result = ValidationHelper::validatePrescriptionStatusTransition('active', 'completed');
        $this->assertTrue($result);
    }
    
    /**
     * Test rating status validation
     */
    public function test_validate_rating_status_valid(): void
    {
        $validStatuses = ['active', 'archived'];
        
        foreach ($validStatuses as $status) {
            $result = ValidationHelper::validateRatingStatus($status);
            $this->assertTrue($result);
        }
    }
    
    /**
     * Test rating status transition
     */
    public function test_validate_rating_status_transition_valid(): void
    {
        // active ↔ archived (both directions valid)
        $result = ValidationHelper::validateRatingStatusTransition('active', 'archived');
        $this->assertTrue($result);
        
        $result = ValidationHelper::validateRatingStatusTransition('archived', 'active');
        $this->assertTrue($result);
    }
    
    /**
     * Test validate user role valid
     */
    public function test_validate_user_role_valid(): void
    {
        $this->assertTrue(ValidationHelper::validateUserRole('admin'));
        $this->assertTrue(ValidationHelper::validateUserRole('dokter'));
        $this->assertTrue(ValidationHelper::validateUserRole('pasien'));
    }
    
    /**
     * Test validate user role invalid
     */
    public function test_validate_user_role_invalid(): void
    {
        $this->assertFalse(ValidationHelper::validateUserRole('invalid_role'));
        $this->assertFalse(ValidationHelper::validateUserRole('superadmin'));
    }
    
    /**
     * Test validate slot duration valid
     */
    public function test_validate_slot_duration_valid(): void
    {
        $this->assertTrue(ValidationHelper::validateSlotDuration(15));   // Min
        $this->assertTrue(ValidationHelper::validateSlotDuration(30));   // Default
        $this->assertTrue(ValidationHelper::validateSlotDuration(60));   // Typical
        $this->assertTrue(ValidationHelper::validateSlotDuration(120));  // Max
    }
    
    /**
     * Test validate slot duration invalid
     */
    public function test_validate_slot_duration_invalid(): void
    {
        $this->assertFalse(ValidationHelper::validateSlotDuration(10));  // Too short
        $this->assertFalse(ValidationHelper::validateSlotDuration(0));   // Zero
        $this->assertFalse(ValidationHelper::validateSlotDuration(150)); // Too long
        $this->assertFalse(ValidationHelper::validateSlotDuration(-30)); // Negative
    }
    
    /**
     * Test get consultation statuses
     */
    public function test_get_consultation_statuses(): void
    {
        $statuses = ValidationHelper::getConsultationStatuses();
        
        $this->assertIsArray($statuses);
        $this->assertContains('pending', $statuses);
        $this->assertContains('aktif', $statuses);
        $this->assertContains('selesai', $statuses);
        $this->assertCount(4, $statuses);
    }
    
    /**
     * Test get prescription statuses
     */
    public function test_get_prescription_statuses(): void
    {
        $statuses = ValidationHelper::getPrescriptionStatuses();
        
        $this->assertIsArray($statuses);
        $this->assertContains('active', $statuses);
        $this->assertContains('expired', $statuses);
        $this->assertContains('completed', $statuses);
        $this->assertCount(4, $statuses);
    }
    
    /**
     * Test get rating statuses
     */
    public function test_get_rating_statuses(): void
    {
        $statuses = ValidationHelper::getRatingStatuses();
        
        $this->assertIsArray($statuses);
        $this->assertContains('active', $statuses);
        $this->assertContains('archived', $statuses);
        $this->assertCount(2, $statuses);
    }
}
