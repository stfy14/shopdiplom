<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Добавляем флаг "прочитано" в сообщения
        Schema::table('order_messages', function (Blueprint $table) {
            $table->boolean('is_read')->default(false)->after('message');
        });

        // Добавляем флаг "есть непросмотренная активность" в сам заказ
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('has_unseen_activity')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('order_messages', function (Blueprint $table) {
            $table->dropColumn('is_read');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('has_unseen_activity');
        });
    }
};