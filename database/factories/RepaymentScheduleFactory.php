<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;

class RepaymentScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create();

        return [
            'due_date' => $faker->dateTimeBetween('-1 month'),
            'due_payment' => 2500,
            'remaining' => 2500,
            'times_reminded' => 0,
            'weekly_loan_id' => 1,
            'status' => 1
        ];
    }
}
