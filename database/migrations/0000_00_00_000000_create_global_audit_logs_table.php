<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('global_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('http_method')->nullable();
            $table->string('action')->nullable();
            $table->nullableUlidMorphs('model');
            $table->ipAddress('ip_address')->nullable();
            $table->longText('url')->nullable();
            $table->longText('user_agent')->nullable();
            $table->jsonb('changes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_audit_logs');
    }
};
