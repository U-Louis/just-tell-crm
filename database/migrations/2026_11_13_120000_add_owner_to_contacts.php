<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('contacts', 'user_id')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
        });

        $owner = DB::table('users')->where('name', 'Ada')->value('id')
            ?? DB::table('users')->orderBy('id')->value('id');

        if ($owner !== null) {
            DB::table('contacts')->whereNull('user_id')->update(['user_id' => $owner]);
        }

        if (DB::table('contacts')->whereNull('user_id')->exists()) {
            throw new RuntimeException('A contact has no user to belong to.');
        }

        DB::statement('ALTER TABLE contacts ALTER COLUMN user_id SET NOT NULL');

        Schema::table('contacts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('contacts', 'user_id')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
