<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('callin', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unique_id')->nullable();
            $table->string('hotline')->nullable();
            $table->string('number_trunk')->nullable();
            $table->string('customer_number')->nullable();
            $table->integer('customer_number_type')->nullable();
            $table->string('customer_area_code')->nullable();
            $table->string('customer_province')->nullable();
            $table->string('customer_crm_id')->nullable();
            $table->string('customer_city')->nullable();
            $table->integer('customer_vip')->default(0);
            $table->string('client_number')->nullable();
            $table->string('client_area_code')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_crm_id')->nullable();
            $table->string('cno')->nullable();
            $table->string('exten')->nullable();
            $table->bigInteger('start_time')->nullable();
            $table->bigInteger('answer_time')->nullable();
            $table->bigInteger('join_queue_time')->nullable();
            $table->bigInteger('bridge_time')->nullable();
            $table->bigInteger('end_time')->nullable();
            $table->integer('bill_duration')->default(0);
            $table->integer('bridge_duration')->default(0);
            $table->integer('total_duration')->default(0);
            $table->decimal('cost', 38, 3);
            $table->string('ivr_id')->nullable();
            $table->string('ivr_name')->nullable();
            $table->string('ivr_flow')->nullable();
            $table->string('queue_name')->nullable();
            $table->string('record_file')->nullable();
            $table->integer('score')->default(0);
            $table->string('score_comment')->nullable();
            $table->integer('in_case_lib')->default(0);
            $table->integer('call_type');
            $table->integer('status');
            $table->integer('mark')->default(0);
            $table->string('mark_data')->nullable();
            $table->integer('end_reason')->default(0);
            $table->string('gw_ip')->nullable();
            $table->integer('processed')->default(0);
            $table->string('process_comment')->nullable();
            $table->string('user_field')->nullable();
            $table->integer('sip_cause')->default(0);
            $table->string('total_cost')->nullable();
            $table->timestamp('create_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('callin');
    }
}
