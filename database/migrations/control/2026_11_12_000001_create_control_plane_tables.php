<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::connection('control')->create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('prefix', 64)->unique();
            $table->string('db_database', 128)->unique();
            $table->string('status', 32)->default('provisioning')->index();
            $table->string('storage_mode', 16)->default('cloud');
            $table->string('storage_prefix', 255);
            $table->string('file_upload_path', 1024)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('control')->create('tenant_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('feature_key', 64);
            $table->boolean('enabled')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'feature_key']);
        });

        Schema::connection('control')->create('tenant_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->string('ledger_ocr_provider', 32)->default('gemini');
            $table->text('gemini_api_key_encrypted')->nullable();
            $table->string('gemini_model', 128)->nullable();
            $table->string('gemini_api_base', 255)->nullable();
            $table->string('internal_ocr_base_url', 255)->nullable();
            $table->string('internal_ocr_ledger_path', 255)->nullable();
            $table->text('internal_ocr_token_encrypted')->nullable();
            $table->unsignedInteger('internal_ocr_timeout')->nullable();
            $table->string('card_ocr_provider', 32)->nullable();
            $table->boolean('trace_enabled')->default(false);
            $table->timestamps();
        });

        Schema::connection('control')->create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('key', 128);
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'key']);
        });

        Schema::connection('control')->create('tenant_migration_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('batch_id', 64)->index();
            $table->string('target_migration', 255)->nullable();
            $table->string('status', 32)->index();
            $table->string('from_migration', 255)->nullable();
            $table->string('to_migration', 255)->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::connection('control')->create('platform_admins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->boolean('can_provision')->default(false);
            $table->boolean('can_impersonate')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('control')->create('platform_support_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token_hash', 128)->unique();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('platform_admin_id')->constrained('platform_admins')->cascadeOnDelete();
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('control')->create('platform_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_admin_id')->nullable()->constrained('platform_admins')->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('action', 64)->index();
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::connection('control')->dropIfExists('platform_audit_logs');
        Schema::connection('control')->dropIfExists('platform_support_tokens');
        Schema::connection('control')->dropIfExists('platform_admins');
        Schema::connection('control')->dropIfExists('tenant_migration_runs');
        Schema::connection('control')->dropIfExists('tenant_settings');
        Schema::connection('control')->dropIfExists('tenant_ai_settings');
        Schema::connection('control')->dropIfExists('tenant_features');
        Schema::connection('control')->dropIfExists('tenants');
    }
};
