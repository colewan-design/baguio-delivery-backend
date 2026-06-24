<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->after('user_id')->constrained('leads')->nullOnDelete();
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->after('user_id')->constrained('leads')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_id');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_id');
        });
    }
};
