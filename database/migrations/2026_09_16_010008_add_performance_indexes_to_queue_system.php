<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->index(
                ['counter_id', 'status'],
                'queues_counter_status_index'
            );

            $table->index(
                ['counter_id', 'handled_by', 'status'],
                'queues_counter_handled_status_index'
            );

            $table->index(
                ['service_id', 'status', 'created_at', 'id'],
                'queues_service_status_created_id_index'
            );
        });

        Schema::table('queue_logs', function (Blueprint $table) {
            $table->index(
                'queue_id',
                'queue_logs_queue_id_index'
            );
        });

        Schema::table('counters', function (Blueprint $table) {
            $table->index(
                'service_id',
                'counters_service_id_index'
            );
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(
                'counter_id',
                'users_counter_id_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropIndex(
                'queues_counter_status_index'
            );

            $table->dropIndex(
                'queues_counter_handled_status_index'
            );

            $table->dropIndex(
                'queues_service_status_created_id_index'
            );
        });

        Schema::table('queue_logs', function (Blueprint $table) {
            $table->dropIndex(
                'queue_logs_queue_id_index'
            );
        });

        Schema::table('counters', function (Blueprint $table) {
            $table->dropIndex(
                'counters_service_id_index'
            );
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(
                'users_counter_id_index'
            );
        });
    }
};