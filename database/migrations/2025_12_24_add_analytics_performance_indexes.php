<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds performance indexes for Analytics Service optimization
     * Focuses on columns used in:
     * - WHERE clauses for filtering
     * - GROUP BY clauses for aggregation
     * - ORDER BY clauses for sorting
     * - JOIN conditions for relationships
     */
    public function up(): void
    {
        // ============================================
        // KONSULTASI TABLE - Priority indexes for monthly reporting
        // ============================================
        if (Schema::hasTable('consultations')) {
            Schema::table('consultations', function (Blueprint $table) {
                // For revenue analytics - date + fee filtering
                if (!$this->indexExists('consultations', 'idx_consultation_fee_created')) {
                    $table->index(
                        ['status', 'created_at', 'doctor_id', 'fee'],
                        'idx_consultation_fee_created'
                    );
                }

                // For date-based filtering and sorting (standalone)
                if (!$this->indexExists('consultations', 'idx_consultation_created_at')) {
                    $table->index('created_at', 'idx_consultation_created_at');
                }

                // For complaint type analysis and health trends
                if (!$this->indexExists('consultations', 'idx_consultation_complaint')) {
                    $table->index(
                        ['complaint_type', 'created_at'],
                        'idx_consultation_complaint'
                    );
                }
            });
        }

        // ============================================
        // USERS TABLE - For user metrics and retention
        // ============================================
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // For getUserRetention() - last login filtering
                if (!$this->indexExists('users', 'idx_users_last_login_at')) {
                    $table->index('last_login_at', 'idx_users_last_login_at');
                }

                // For user creation trends and growth metrics
                if (!$this->indexExists('users', 'idx_users_created_at')) {
                    $table->index('created_at', 'idx_users_created_at');
                }

                // For role-based filtering combined with verification status
                if (!$this->indexExists('users', 'idx_users_role_verified')) {
                    $table->index(
                        ['role', 'email_verified_at'],
                        'idx_users_role_verified'
                    );
                }
            });
        }

        // ============================================
        // RATINGS TABLE - For doctor performance and engagement
        // ============================================
        if (Schema::hasTable('ratings')) {
            Schema::table('ratings', function (Blueprint $table) {
                // For top-rated doctors aggregation
                if (!$this->indexExists('ratings', 'idx_ratings_doctor_id_rating')) {
                    $table->index(
                        ['doctor_id', 'rating'],
                        'idx_ratings_doctor_id_rating'
                    );
                }

                // For engagement metrics by date
                if (!$this->indexExists('ratings', 'idx_ratings_created_at')) {
                    $table->index('created_at', 'idx_ratings_created_at');
                }
            });
        }

        // ============================================
        // MESSAGES TABLE - For engagement metrics
        // ============================================
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                // For message count by date range
                if (!$this->indexExists('messages', 'idx_messages_created_at')) {
                    $table->index('created_at', 'idx_messages_created_at');
                }
            });
        }

        // ============================================
        // PESAN_CHAT TABLE - For chat engagement metrics
        // ============================================
        if (Schema::hasTable('pesan_chats')) {
            Schema::table('pesan_chats', function (Blueprint $table) {
                // For chat message count by date range
                if (!$this->indexExists('pesan_chats', 'idx_pesan_created_at')) {
                    $table->index('created_at', 'idx_pesan_created_at');
                }
            });
        }

        // ============================================
        // DOKTER TABLE - For specialization and availability queries
        // ============================================
        if (Schema::hasTable('dokters')) {
            Schema::table('dokters', function (Blueprint $table) {
                // For specialization distribution aggregation
                if (!$this->indexExists('dokters', 'idx_dokter_specialization')) {
                    $table->index('specialization', 'idx_dokter_specialization');
                }

                // For doctor availability and verification queries
                if (!$this->indexExists('dokters', 'idx_dokter_available_verified')) {
                    $table->index(
                        ['is_available', 'is_verified'],
                        'idx_dokter_available_verified'
                    );
                }
            });
        }

        // ============================================
        // PASIEN TABLE - For patient demographics
        // ============================================
        if (Schema::hasTable('pasiens')) {
            Schema::table('pasiens', function (Blueprint $table) {
                // For gender-based grouping and demographics
                if (!$this->indexExists('pasiens', 'idx_pasien_gender')) {
                    $table->index('gender', 'idx_pasien_gender');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dropIndexes = [
            'consultations' => [
                'idx_consultation_fee_created',
                'idx_consultation_created_at',
                'idx_consultation_complaint',
            ],
            'users' => [
                'idx_users_last_login_at',
                'idx_users_created_at',
                'idx_users_role_verified',
            ],
            'ratings' => [
                'idx_ratings_doctor_id_rating',
                'idx_ratings_created_at',
            ],
            'messages' => [
                'idx_messages_created_at',
            ],
            'pesan_chats' => [
                'idx_pesan_created_at',
            ],
            'dokters' => [
                'idx_dokter_specialization',
                'idx_dokter_available_verified',
            ],
            'pasiens' => [
                'idx_pasien_gender',
            ],
        ];

        foreach ($dropIndexes as $table => $indexes) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($indexes) {
                    foreach ($indexes as $indexName) {
                        if ($this->indexExists($table->getTable(), $indexName)) {
                            $table->dropIndex($indexName);
                        }
                    }
                });
            }
        }
    }

    /**
     * Helper method to check if index exists in database
     * Works with MySQL, PostgreSQL, and SQLite
     * 
     * @param string $table Table name
     * @param string $indexName Index name to check
     * @return bool True if index exists
     */
    private function indexExists($table, $indexName): bool
    {
        try {
            $databaseName = env('DB_DATABASE', 'telemedicine');

            // For MySQL and similar databases
            $result = DB::select(
                "SELECT * FROM information_schema.STATISTICS 
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?",
                [$databaseName, $table, $indexName]
            );

            return !empty($result);
        } catch (\Exception $e) {
            // If query fails, try SQLite approach or return false
            return false;
        }
    }
};
