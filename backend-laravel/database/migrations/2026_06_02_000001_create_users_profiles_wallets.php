<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('users', function(Blueprint $t){$t->id();$t->string('username')->unique();$t->string('email')->unique();$t->string('password');$t->boolean('is_admin')->default(false);$t->boolean('is_banned')->default(false);$t->timestamp('last_seen_at')->nullable();$t->rememberToken();$t->timestamps();});
  Schema::create('profiles', function(Blueprint $t){$t->id();$t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();$t->string('display_name');$t->string('avatar')->nullable();$t->string('country_code',2)->default('PS');$t->string('country_name')->default('Palestine');$t->string('name_color')->default('#facc15');$t->string('text_color')->default('#ffffff');$t->string('badge')->nullable();$t->integer('pasha_days')->default(0);$t->unsignedInteger('level')->default(1);$t->unsignedBigInteger('xp')->default(0);$t->timestamps();});
  Schema::create('wallets', function(Blueprint $t){$t->id();$t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();$t->unsignedBigInteger('tokens')->default(50);$t->unsignedBigInteger('gems')->default(0);$t->timestamps();});
  Schema::create('wallet_transactions', function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->foreignId('counterparty_id')->nullable()->constrained('users')->nullOnDelete();$t->string('type');$t->bigInteger('amount');$t->bigInteger('fee')->default(0);$t->json('meta')->nullable();$t->timestamps();});
 }
 public function down(): void {Schema::dropIfExists('wallet_transactions');Schema::dropIfExists('wallets');Schema::dropIfExists('profiles');Schema::dropIfExists('users');}
};
