<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enhanced MySQL constraints and optimizations
     * Run AFTER basic migrations to add proper constraints
     */
    public function up(): void
    {
        // 1. USERS TABLE - Add constraints
        if (Schema::hasTable('users')) {
            if (!$this->constraintExists('users', 'UQ_users_email')) {
                Schema::table('users', function (Blueprint $table) {
                    // Ensure email is unique (if not already)
                    $table->unique('email', 'UQ_users_email');
                });
            }

            // Add indexes for common queries
            if (!$this->indexExists('users', 'IDX_users_role')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->index('role', 'IDX_users_role');
                    $table->index('created_at', 'IDX_users_created_at');
                    $table->index(['role', 'is_available'], 'IDX_users_role_available');
                });
            }
        }

        // 2. APPOINTMENTS TABLE - Add constraints
        if (Schema::hasTable('appointments')) {
            if (!$this->constraintExists('appointments', 'FK_appointments_patient')) {
                Schema::table('appointments', function (Blueprint $table) {
                    // Foreign keys with proper cascade
                    $table->foreign('patient_id', 'FK_appointments_patient')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                    
                    $table->foreign('doctor_id', 'FK_appointments_doctor')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                });
            }

            // Add indexes
            if (!$this->indexExists('appointments', 'IDX_appointments_patient')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->index('patient_id', 'IDX_appointments_patient');
                    $table->index('doctor_id', 'IDX_appointments_doctor');
                    $table->index('status', 'IDX_appointments_status');
                    $table->index(['patient_id', 'status'], 'IDX_appointments_patient_status');
                    $table->index(['doctor_id', 'status'], 'IDX_appointments_doctor_status');
                    $table->index('scheduled_at', 'IDX_appointments_scheduled_at');
                });
            }

            // Add check constraints for status
            if (!$this->constraintExists('appointments', 'CHK_appointments_status')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->comment('Appointments table with status validation');
                });
                
                // Note: Check constraints added via raw SQL
                \DB::statement(
                    "ALTER TABLE appointments ADD CONSTRAINT CHK_appointments_status 
                     CHECK (status IN ('pending', 'confirmed', 'completed', 'cancelled', 'rejected'))"
                );
            }
        }

        // 3. KONSULTASI TABLE - Add constraints
        if (Schema::hasTable('konsultasis')) {
            if (!$this->constraintExists('konsultasis', 'FK_konsultasi_patient')) {
                Schema::table('konsultasis', function (Blueprint $table) {
                    $table->foreign('patient_id', 'FK_konsultasi_patient')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                    
                    $table->foreign('doctor_id', 'FK_konsultasi_doctor')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                });
            }

            if (!$this->indexExists('konsultasis', 'IDX_konsultasi_patient')) {
                Schema::table('konsultasis', function (Blueprint $table) {
                    $table->index('patient_id', 'IDX_konsultasi_patient');
                    $table->index('doctor_id', 'IDX_konsultasi_doctor');
                    $table->index('status', 'IDX_konsultasi_status');
                    $table->index('created_at', 'IDX_konsultasi_created_at');
                });
            }
        }

        // 4. PRESCRIPTIONS TABLE - Add constraints
        if (Schema::hasTable('prescriptions')) {
            if (!$this->constraintExists('prescriptions', 'FK_prescriptions_appointment')) {
                Schema::table('prescriptions', function (Blueprint $table) {
                    $table->foreign('appointment_id', 'FK_prescriptions_appointment')
                        ->references('id')
                        ->on('appointments')
                        ->onDelete('cascade');
                    
                    $table->foreign('doctor_id', 'FK_prescriptions_doctor')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                    
                    $table->foreign('patient_id', 'FK_prescriptions_patient')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                });
            }

            if (!$this->indexExists('prescriptions', 'IDX_prescriptions_appointment')) {
                Schema::table('prescriptions', function (Blueprint $table) {
                    $table->index('appointment_id', 'IDX_prescriptions_appointment');
                    $table->index('doctor_id', 'IDX_prescriptions_doctor');
                    $table->index('patient_id', 'IDX_prescriptions_patient');
                    $table->index('status', 'IDX_prescriptions_status');
                });
            }
        }

        // 5. MESSAGES TABLE - Add constraints
        if (Schema::hasTable('messages')) {
            if (!$this->constraintExists('messages', 'FK_messages_sender')) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->foreign('sender_id', 'FK_messages_sender')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                    
                    $table->foreign('conversation_id', 'FK_messages_conversation')
                        ->references('id')
                        ->on('conversations')
                        ->onDelete('cascade');
                });
            }

            if (!$this->indexExists('messages', 'IDX_messages_conversation')) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->index('conversation_id', 'IDX_messages_conversation');
                    $table->index('sender_id', 'IDX_messages_sender');
                    $table->index('created_at', 'IDX_messages_created_at');
                });
            }
        }

        // 6. CONVERSATIONS TABLE - Add constraints
        if (Schema::hasTable('conversations')) {
            if (!$this->constraintExists('conversations', 'UQ_conversations_users')) {
                Schema::table('conversations', function (Blueprint $table) {
                    $table->unique(
                        ['user_one_id', 'user_two_id'],
                        'UQ_conversations_users'
                    );
                });
            }

            if (!$this->constraintExists('conversations', 'FK_conversations_user_one')) {
                Schema::table('conversations', function (Blueprint $table) {
                    $table->foreign('user_one_id', 'FK_conversations_user_one')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                    
                    $table->foreign('user_two_id', 'FK_conversations_user_two')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                });
            }

            if (!$this->indexExists('conversations', 'IDX_conversations_user_one')) {
                Schema::table('conversations', function (Blueprint $table) {
                    $table->index('user_one_id', 'IDX_conversations_user_one');
                    $table->index('user_two_id', 'IDX_conversations_user_two');
                });
            }
        }

        // 7. RATINGS TABLE - Add constraints
        if (Schema::hasTable('ratings')) {
            if (!$this->constraintExists('ratings', 'FK_ratings_appointment')) {
                Schema::table('ratings', function (Blueprint $table) {
                    $table->foreign('appointment_id', 'FK_ratings_appointment')
                        ->references('id')
                        ->on('appointments')
                        ->onDelete('cascade');
                    
                    $table->foreign('doctor_id', 'FK_ratings_doctor')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                    
                    $table->foreign('patient_id', 'FK_ratings_patient')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                });
            }

            // Add check constraint for rating 1-5
            if (!$this->constraintExists('ratings', 'CHK_ratings_value')) {
                \DB::statement(
                    "ALTER TABLE ratings ADD CONSTRAINT CHK_ratings_value 
                     CHECK (rating >= 1 AND rating <= 5)"
                );
            }

            if (!$this->indexExists('ratings', 'IDX_ratings_doctor')) {
                Schema::table('ratings', function (Blueprint $table) {
                    $table->index('doctor_id', 'IDX_ratings_doctor');
                    $table->index('patient_id', 'IDX_ratings_patient');
                    $table->index('appointment_id', 'IDX_ratings_appointment');
                });
            }
        }

        // 8. ACTIVITY LOGS TABLE - Add constraints
        if (Schema::hasTable('activity_logs')) {
            if (!$this->constraintExists('activity_logs', 'FK_activity_logs_user')) {
                Schema::table('activity_logs', function (Blueprint $table) {
                    $table->foreign('user_id', 'FK_activity_logs_user')
                        ->references('id')
                        ->on('users')
                        ->onDelete('cascade');
                });
            }

            if (!$this->indexExists('activity_logs', 'IDX_activity_logs_user')) {
                Schema::table('activity_logs', function (Blueprint $table) {
                    $table->index('user_id', 'IDX_activity_logs_user');
                    $table->index('action', 'IDX_activity_logs_action');
                    $table->index('created_at', 'IDX_activity_logs_created_at');
                });
            }
        }

        // 9. Update character set and collation for MySQL
        if ($this->isMySQL()) {
            $tables = [
                'users', 'appointments', 'konsultasis', 'prescriptions',
                'messages', 'conversations', 'ratings', 'activity_logs'
            ];

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    \DB::statement(
                        "ALTER TABLE {$table} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
                    );
                }
            }
        }
    }

    public function down(): void
    {
        // Drop foreign keys
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeignKey('FK_appointments_patient');
            $table->dropForeignKey('FK_appointments_doctor');
        });

        Schema::table('konsultasis', function (Blueprint $table) {
            $table->dropForeignKey('FK_konsultasi_patient');
            $table->dropForeignKey('FK_konsultasi_doctor');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropForeignKey('FK_prescriptions_appointment');
            $table->dropForeignKey('FK_prescriptions_doctor');
            $table->dropForeignKey('FK_prescriptions_patient');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeignKey('FK_messages_sender');
            $table->dropForeignKey('FK_messages_conversation');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeignKey('FK_conversations_user_one');
            $table->dropForeignKey('FK_conversations_user_two');
        });

        Schema::table('ratings', function (Blueprint $table) {
            $table->dropForeignKey('FK_ratings_appointment');
            $table->dropForeignKey('FK_ratings_doctor');
            $table->dropForeignKey('FK_ratings_patient');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeignKey('FK_activity_logs_user');
        });
    }

    /**
     * Helper: Check if constraint exists
     */
    private function constraintExists($table, $name): bool
    {
        try {
            $result = \DB::select(
                "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?",
                [\DB::connection()->getDatabaseName(), $table, $name]
            );
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Helper: Check if index exists
     */
    private function indexExists($table, $name): bool
    {
        try {
            $result = \DB::select(
                "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS 
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?",
                [\DB::connection()->getDatabaseName(), $table, $name]
            );
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Helper: Check if using MySQL
     */
    private function isMySQL(): bool
    {
        return \DB::connection()->getDriverName() === 'mysql';
    }
};
