<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('ad_dn')->nullable()->after('id');
            $table->string('ad_username')->nullable()->index()->after('ad_dn');
            $table->string('department')->nullable()->after('email_verified_at');
            $table->string('title')->nullable()->after('department');
            $table->string('phone')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['ad_username']);
            $table->dropColumn(['ad_dn', 'ad_username', 'department', 'title', 'phone']);
        });
    }
};
