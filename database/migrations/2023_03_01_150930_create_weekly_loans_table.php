<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekly_loans', function (Blueprint $table) {
            $table->id();
            // I use column name 'amount' instead of 'required amount' to prevent ambiguity in required validator error message
            // e.g. : required amount field is required
            $table->decimal('amount');
            $table->integer('loan_term', false, true);
            $table->decimal('remaining');
            $table->smallInteger('status', false, false)->default(0);
            $table->foreignId('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('updated_by', false, true);
            $table->bigInteger('approved_rejected_by')->nullable();
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
        Schema::dropIfExists('loans');
    }
}
