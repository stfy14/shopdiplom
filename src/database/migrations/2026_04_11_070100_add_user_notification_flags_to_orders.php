<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('user_has_unseen_status_change')->default(false)->after('has_unseen_activity');
            $table->boolean('user_has_unseen_contact_update')->default(false)->after('user_has_unseen_status_change');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['user_has_unseen_status_change', 'user_has_unseen_contact_update']);
        });
    }
};