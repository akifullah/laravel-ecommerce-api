<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition()
    {
        return [
            'user_id' => 1, // or User::factory()
            'label' => 'Home',
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'company' => $this->faker->company,
            'address_line1' => $this->faker->streetAddress,
            'address_line2' => $this->faker->secondaryAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'postal_code' => $this->faker->postcode,
            'country_code' => $this->faker->countryCode,
            'phone' => $this->faker->phoneNumber,
            'is_default_shipping' => $this->faker->boolean,
            'is_default_billing' => $this->faker->boolean,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
        ];
    }
}
