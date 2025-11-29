<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('application_status', ['none', 'pending_approval', 'approved', 'rejected'])
                  ->default('none')
                  ->after('is_active');
            $table->enum('application_type', ['restaurant_owner', 'courier'])
                  ->nullable()
                  ->after('application_status');
            $table->text('application_note')->nullable()->after('application_type');
            $table->unsignedBigInteger('application_reviewed_by')->nullable()->after('application_note');
            $table->timestamp('application_reviewed_at')->nullable()->after('application_reviewed_by');
            $table->string('application_rejection_reason', 500)->nullable()->after('application_reviewed_at');
            $table->boolean('is_verified')->default(false)->after('application_rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'application_status',
                'application_type',
                'application_note',
                'application_reviewed_by',
                'application_reviewed_at',
                'application_rejection_reason',
                'is_verified',
            ]);
        });
    }
};
