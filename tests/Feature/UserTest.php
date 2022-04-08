<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase; 
use Illuminate\Http\Response;
use App\Models\User;
use Hash;


class UserTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    /**
     * A basic user register test
     *
     * @return void
     * @test
     */
    public function user_can_register()
    {
        $requestData = [
            'name'     => $this->faker->name,
            'email'    => $this->faker->email,
            'password' => $this->faker->password,
        ];

        $response = $this->postJson(route('auth.register'), $requestData);
        $response
            ->assertCreated()
            ->assertJsonPath('data.name', $requestData['name'])
            ->assertJsonPath('data.email', $requestData['email']);
    }

    /**
     * A basic user register validation test
     *
     * @return void
     * @test
     */
    public function user_cannot_register_if_data_invalid()
    {
        $requestData = [
            'name'     => '',
            'email'    => '',
            'password' => '',
        ];

        $response = $this->postJson(route('auth.register'), $requestData);
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertInvalid(array_keys($requestData));
    }

    /**
     * A user login test
     *
     * @return void
     * @test
     */
    public function user_can_login()
    {
        $password = $this->faker->password;
        $user = User::factory()->create([
            'password' => Hash::make($password)
        ]);

        $response = $this->postJson(route('auth.login'), [
            'email'    => $user->email,
            'password' => $password,
        ]);
        //print_r($response->decodeResponseJson());
        
        $response
            ->assertOk()
             ->assertJson(fn (AssertableJson $json) =>
                $json->where('user.id', $user->id)
                     ->missing('password')
                     ->etc()
            );
    }
}
