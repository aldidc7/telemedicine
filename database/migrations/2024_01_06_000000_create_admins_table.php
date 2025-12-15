<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->onDelete('cascade');
            
            $table->integer('permission_level')
                ->default(1)
                ->comment('1=basic, 2=advanced, 3=super_admin');
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('permission_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};