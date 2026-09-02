<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_fetchers', function (Blueprint $table) {
            $table->string('type', 32)->change();
        });
    }

    public function down(): void
    {
        Schema::table('price_fetchers', function (Blueprint $table) {
            $table->enum('type', [
                'digikala',
                'fafait',
                'markazi',
                'fater',
                'setaregan',
                'technolife',
                'torob',
            ])->change();
        });
    }
};
