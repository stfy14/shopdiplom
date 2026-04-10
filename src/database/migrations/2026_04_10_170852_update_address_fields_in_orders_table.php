<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Удаляем старое поле
            $table->dropColumn('address');
            
            // Добавляем новые поля после user_id
            $table->string('city')->after('user_id');
            $table->string('street')->after('city');
            $table->string('house')->after('street');
            $table->text('comment')->nullable()->after('house');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Если что-то пойдет не так, эта команда вернет всё как было
            $table->string('address')->after('user_id');
            $table->dropColumn(['city', 'street', 'house', 'comment']);
        });
    }
};