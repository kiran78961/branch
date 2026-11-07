<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salary_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('monthly_salary', 12, 2);
            $table->string('change_type', 20);
            $table->timestamp('changed_at');
            $table->index(['user_id', 'changed_at']);
        });

        $snapshotTime = now()->toDateTimeString();

        DB::table('users')
            ->whereNotNull('monthly_salary')
            ->orderBy('id')
            ->chunkById(500, function ($users) use ($snapshotTime): void {
                $snapshots = $users->map(static fn (object $user): array => [
                    'user_id' => $user->id,
                    'changed_by' => null,
                    'monthly_salary' => $user->monthly_salary,
                    'change_type' => 'snapshot',
                    'changed_at' => $snapshotTime,
                ])->all();

                DB::table('salary_histories')->insert($snapshots);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_histories');
    }
};
