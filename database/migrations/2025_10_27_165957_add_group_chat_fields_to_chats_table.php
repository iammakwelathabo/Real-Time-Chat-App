<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('chats', function (Blueprint $table) {
            // These should already exist from previous setup
            // $table->string('name')->nullable()->change();
            // $table->boolean('is_group')->default(false)->change();

            // Add group-specific fields
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('avatar')->nullable();
        });
    }

    public function down()
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn(['description', 'created_by', 'avatar']);
        });
    }
};
