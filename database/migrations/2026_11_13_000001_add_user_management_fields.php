<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'mobile_number')) $table->string('mobile_number', 20)->nullable();
            if (! Schema::hasColumn('users', 'role')) $table->unsignedBigInteger('role')->nullable()->index();
            if (! Schema::hasColumn('users', 'status')) $table->string('status', 10)->default('active');
            if (! Schema::hasColumn('users', 'gender')) $table->string('gender', 10)->nullable();
            if (! Schema::hasColumn('users', 'birth_date')) $table->date('birth_date')->nullable();
            if (! Schema::hasColumn('users', 'profile_picture')) $table->string('profile_picture')->nullable();
            if (! Schema::hasColumn('users', 'deleted_at')) $table->timestamp('deleted_at')->nullable()->index();
        });

        if (Schema::hasColumn('users', 'status')) {
            \Illuminate\Support\Facades\DB::table('users')->whereNull('status')->update(['status' => 'active']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['mobile_number', 'role', 'status', 'gender', 'birth_date', 'profile_picture', 'deleted_at'] as $column) {
                if (Schema::hasColumn('users', $column)) $table->dropColumn($column);
            }
        });
    }
};
