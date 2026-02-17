<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changelog_entries', function (Blueprint $table) {
            $table->id();

            $table->morphs('trackable');

            $table->string('event', 20)->index();

            $table->json('changes')->nullable();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->nullableMorphs('causer');
            $table->string('causer_name')->nullable();

            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['trackable_type', 'trackable_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changelog_entries');
    }
};