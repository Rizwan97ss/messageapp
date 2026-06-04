<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
        $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();

        $table->longText('body')->nullable();

        // Later for end-to-end encryption
        $table->longText('ciphertext')->nullable();
        $table->string('nonce')->nullable();
        $table->json('encryption_meta')->nullable();

        $table->enum('type', ['text', 'image', 'video', 'audio', 'file', 'system'])->default('text');
        $table->foreignId('reply_to_id')->nullable()->constrained('messages')->nullOnDelete();

        $table->timestamp('edited_at')->nullable();
        $table->timestamp('deleted_at')->nullable();
        $table->timestamps();

        $table->index(['conversation_id', 'created_at']);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
