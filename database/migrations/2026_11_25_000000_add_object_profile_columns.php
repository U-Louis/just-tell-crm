<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('description', 255)->nullable();
            $table->string('notes', 255)->nullable();
            $table->string('company_size', 50)->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users');
        });

        DB::table('contacts')->whereNull('owner_id')->update([
            'owner_id' => DB::raw('user_id'),
        ]);

        Schema::table('products', function (Blueprint $table) {
            $table->string('description', 255)->nullable();
            $table->string('category', 100)->nullable();
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable();
        });

        DB::table('estimates')->whereNull('sent_at')->update([
            'sent_at' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn('sent_at');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['description', 'category']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
            $table->dropColumn(['description', 'notes', 'company_size']);
        });
    }
};
