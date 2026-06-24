<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE = 'course_group_membership_histories';

    private const IDX_STUDENT_JOINED = 'cgmh_student_joined_idx';

    private const IDX_GROUP_STUDENT_LEFT = 'cgmh_grp_stu_left_idx';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)) {
            Schema::create(self::TABLE, function (Blueprint $table) {
                $table->id();

                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('group_id')->constrained('course_groups')->cascadeOnDelete();

                $table->enum('role', ['member', 'leader'])->default('member');

                $table->timestamp('joined_at');
                $table->timestamp('left_at')->nullable();

                $table->text('join_reason')->nullable();
                $table->text('leave_reason')->nullable();

                $table->foreignId('joined_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('removed_by')->nullable()->constrained('users')->nullOnDelete();

                $table->string('source', 64)->default('system');
                $table->unsignedBigInteger('source_reference_id')->nullable();

                $table->timestamps();

                $table->index(['student_id', 'joined_at'], self::IDX_STUDENT_JOINED);
                $table->index(['group_id', 'student_id', 'left_at'], self::IDX_GROUP_STUDENT_LEFT);
            });

            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) {
            if (! $this->indexExists(self::IDX_STUDENT_JOINED)) {
                $table->index(['student_id', 'joined_at'], self::IDX_STUDENT_JOINED);
            }
            if (! $this->indexExists(self::IDX_GROUP_STUDENT_LEFT)) {
                $table->index(['group_id', 'student_id', 'left_at'], self::IDX_GROUP_STUDENT_LEFT);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }

    private function indexExists(string $indexName): bool
    {
        $rows = DB::select(
            'SHOW INDEX FROM `'.self::TABLE.'` WHERE Key_name = ?',
            [$indexName]
        );

        return $rows !== [];
    }
};
