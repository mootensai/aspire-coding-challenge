<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WeeklyLoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => 1,
            'amount' => 10000.00,
            'loan_term' => 4,
            'remaining' => 10000.00,
            'status' => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'approved_rejected_by' => null,
            'created_at' => '2023-03-06 00:00:00',
            'updated_at' => '2023-03-06 00:00:00'
        ];
    }
}
