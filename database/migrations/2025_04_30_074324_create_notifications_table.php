<?php
// database/migrations/2025_05_01_000000_create_notification_settings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');            // e.g. 'assignment_due'
            $table->boolean('enabled')->default(true);
            $table->enum('frequency',['once','hourly','daily'])->default('once');
            $table->integer('before_hours')->default(24);
            $table->json('channels')->default(json_encode(['database']));
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_settings');
    }
}
