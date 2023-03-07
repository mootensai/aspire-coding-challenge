<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepaymentSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repayment_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('due_date');
            $table->decimal('due_payment');
            $table->decimal('remaining');
            $table->smallInteger('times_reminded')->default(0);
            // I set it as foreign key to increase performance by making an index for the column because we use this field for search.
            $table->foreignId('weekly_loan_id')->references('id')->on('weekly_loans')->onDelete('cascade');
            $table->smallInteger('status', false, true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('repayment_schedules');
    }
}
