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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->unique()            // ← order واحد = delivery واحدة بس
                ->constrained()
                ->restrictOnDelete(); // ← مينفعش تمسح order عنده delivery
            $table->foreignId('staff_user_id')
                ->constrained('users') // ← بيشاور على users مش staff_details
                ->restrictOnDelete();  // ← مينفعش تمسح الموظف لو عنده deliveries
            $table->string('address');
            $table->string('status')->default('assigned');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('delivered_at')->nullable(); // ← nullable لأنها مش delivered لسه
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
