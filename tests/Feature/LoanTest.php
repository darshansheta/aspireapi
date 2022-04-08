<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase; 
use Illuminate\Http\Response;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Enums\LoanStatus;
use App\Enums\RepaymentStatus;
use Money\Money;
use Carbon\Carbon;

class LoanTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function createAndLoginUser(): User
    {
        $user = User::factory()->create();
        Sanctum::actingAs(
            $user,
            [config('aspire.token_name')]
        );

        return $user;
    }

    /**
     * A user can request a loan
     *
     * @return void
     * @test
     */
    public function user_can_request_loan()
    {
        $user = $this->createAndLoginUser();

        $requestData = [
            'term' => $this->faker->numberBetween(10, 20),
            'amount' => $this->faker->numberBetween(25, 45) * 1000
        ];


        $response = $this->postJson(route('loans.store'), $requestData);
        $response->assertCreated()
            ->assertJson(fn (AssertableJson $json) =>
                $json
                    ->where('data.term', $requestData['term'])
                    ->where('data.amount', $requestData['amount'])
                    ->where('data.user_id', $user->id)
                    ->where('data.status', LoanStatus::PENDING->value)
                    ->where('data.payment_status', RepaymentStatus::UNPAID->value)
                    ->missing('data.repayments')
                     ->etc()
            );;

    }

    /**
     * A user login test
     *
     * @return void
     * @test
     */
    public function user_can_list_loans()
    {
        $user = User::factory()
            ->hasLoans(3)
            ->create();

        Sanctum::actingAs(
            $user,
            [config('aspire.token_name')]
        );

        $response = $this->getJson(route('loans.index'));

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /**
     * A user login test
     *
     * @return void
     * @test
     */
    public function user_can_view_loan()
    {
        $user = User::factory()
            ->hasLoans(1)
            ->create();

        Sanctum::actingAs(
            $user,
            [config('aspire.token_name')]
        );

        $response = $this->getJson(route('loans.show', $user->loans[0]->id));

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $user->loans[0]->id)
            ->assertJsonPath('data.amount', $user->loans[0]->amount)
            ->assertJsonPath('data.term', $user->loans[0]->term)
            ->assertJsonPath('data.remaining_amount', $user->loans[0]->remaining_amount);
    }

    /**
     * A user login test
     *
     * @return void
     * @test
     */
    public function user_cannot_view_other_user_loan()
    {
        $firstUser = $this->createAndLoginUser();

        $requestData = [
            'term'   => $this->faker->numberBetween(10, 20),
            'amount' => $this->faker->numberBetween(25, 45) * 1000
        ];


        $response = $this->postJson(route('loans.store'), $requestData);
        $response->assertCreated();

        $secondUser = $this->createAndLoginUser();
        $response = $this->getJson(route('loans.show', $firstUser->loans()->first()->id));
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     * A user login test
     *
     * @return void
     * @test
     */
    public function admin_can_approve_loan()
    {
         $user = $this->createAndLoginUser();

        $requestData = [
            'term'   => $this->faker->numberBetween(10, 20),
            'amount' => $this->faker->numberBetween(25, 45) * 1000
        ];


        $response = $this->postJson(route('loans.store'), $requestData);
        $response->assertCreated();

        $response = $this->putJson(route('loans.approve', $user->loans[0]->id), ['approve' => 1]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $admin = User::factory()->admin()->create();
         Sanctum::actingAs(
            $admin,
            [config('aspire.token_name')]
        );

         $response = $this->putJson(route('loans.approve', $user->loans[0]->id), ['approve' => 1]);

         $response
            ->assertOk()
            ->assertJsonPath('data.status', LoanStatus::APPROVED->value);

    }

    /**
     * A user login test
     *
     * @return void
     * @test
     */
    public function admin_cannot_approve_already_approved_loan()
    {
        $user = $this->createAndLoginUser();

        $requestData = [
            'term'   => $this->faker->numberBetween(10, 20),
            'amount' => $this->faker->numberBetween(25, 45) * 1000
        ];


        $response = $this->postJson(route('loans.store'), $requestData);
        $response->assertCreated();

        $admin = User::factory()->admin()->create();
         Sanctum::actingAs(
            $admin,
            [config('aspire.token_name')]
        );

         $response = $this->putJson(route('loans.approve', $user->loans[0]->id), ['approve' => 1]);

         $response
            ->assertOk()
            ->assertJsonPath('data.status', LoanStatus::APPROVED->value);

         $response = $this->putJson(route('loans.approve', $user->loans[0]->id), ['approve' => 1]);
         $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.approve', ['This loan is already approved']);



    }

    /**
     * A user login test
     *
     * @return void
     * @test
     */
    public function user_can_view_approved_loan_with_repayments()
    {
        $user = $this->createAndLoginUser();

        $requestData = [
            'term'   => $this->faker->numberBetween(10, 20),
            'amount' => $this->faker->numberBetween(25, 45) * 1000
        ];


        $response = $this->postJson(route('loans.store'), $requestData);
        $response->assertCreated();

        $admin = User::factory()->admin()->create();
        Sanctum::actingAs(
            $admin,
            [config('aspire.token_name')]
        );

        $response = $this->putJson(route('loans.approve', $user->loans[0]->id), ['approve' => 1]);
        $response->assertOk();

        Sanctum::actingAs(
            $user,
            [config('aspire.token_name')]
        );

        $response = $this->getJson(route('loans.show', $user->loans[0]->id));
        $response
            ->assertOk()
            ->assertJsonCount($user->loans[0]->term, 'data.repayments');

    }

    /**
     * A user login test
     *
     * @return void
     * @test
     */
    public function user_can_pay_unpaid_loan()
    {
        $user = $this->createAndLoginUser();

        $requestData = [
            'term'   => 3,
            'amount' => 10000
        ];


        $response = $this->postJson(route('loans.store'), $requestData);
        $response->assertCreated();

        $admin = User::factory()->admin()->create();
        Sanctum::actingAs(
            $admin,
            [config('aspire.token_name')]
        );

        $response = $this->putJson(route('loans.approve', $user->loans[0]->id), ['approve' => 1]);
        $response->assertOk();

        Sanctum::actingAs(
            $user,
            [config('aspire.token_name')]
        );

        $user->loans[0]->repayments->each(function ($repayment) use ($user) {
            $response = $this->postJson(route('loans.repay', $user->loans[0]->id), ['amount' => $repayment->amount]);
            $response
                ->assertOk()
                ->assertJsonPath('data.0.id', $repayment->id)
                ->assertJsonPath('data.0.status', RepaymentStatus::PAID->value);
        });
    }

    /**
     * A user login test
     *
     * @return void
     * @test
     */
    public function user_can_pay_full_loan()
    {
        $user = $this->createAndLoginUser();

        $requestData = [
            'term'   => 3,
            'amount' => 10000
        ];


        $response = $this->postJson(route('loans.store'), $requestData);
        $response->assertCreated();

        $admin = User::factory()->admin()->create();
        Sanctum::actingAs(
            $admin,
            [config('aspire.token_name')]
        );

        $response = $this->putJson(route('loans.approve', $user->loans[0]->id), ['approve' => 1]);
        $response->assertOk();

        Sanctum::actingAs(
            $user,
            [config('aspire.token_name')]
        );

        $response = $this->postJson(route('loans.repay', $user->loans[0]->id), ['amount' => $requestData['amount']]);

        $response
            ->assertOk();

        $response = $this->getJson(route('loans.show', $user->loans[0]->id));

        $response
            ->assertOk()
            ->assertJsonPath('data.payment_status', RepaymentStatus::PAID->value);
    }

    /**
     * A user login test
     *
     * @return void
     * @test
     */
    public function user_cannot_pay_more_than_remaining_amount()
    {
        $user = $this->createAndLoginUser();

        $requestData = [
            'term'   => 3,
            'amount' => 10000
        ];


        $response = $this->postJson(route('loans.store'), $requestData);
        $response->assertCreated();

        $admin = User::factory()->admin()->create();
        Sanctum::actingAs(
            $admin,
            [config('aspire.token_name')]
        );

        $response = $this->putJson(route('loans.approve', $user->loans[0]->id), ['approve' => 1]);
        $response->assertOk();

        Sanctum::actingAs(
            $user,
            [config('aspire.token_name')]
        );

        $response = $this->postJson(route('loans.repay', $user->loans[0]->id), ['amount' => $requestData['amount'] + 1]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.amount', ['Maximum amount you can pay is '. $requestData['amount']]);
    }

    /**
     * A user login test
     *
     * @return void
     * @test
     */
    public function user_cannot_pay_less_than_scheduled_payment()
    {
        $user = $this->createAndLoginUser();

        $requestData = [
            'term'   => 3,
            'amount' => 10000
        ];


        $response = $this->postJson(route('loans.store'), $requestData);
        $response->assertCreated();

        $admin = User::factory()->admin()->create();
        Sanctum::actingAs(
            $admin,
            [config('aspire.token_name')]
        );

        $response = $this->putJson(route('loans.approve', $user->loans[0]->id), ['approve' => 1]);
        $response->assertOk();

        Sanctum::actingAs(
            $user,
            [config('aspire.token_name')]
        );

        $firstRepayment = $user->loans[0]->repayments[0];
        $response = $this->postJson(route('loans.repay', $user->loans[0]->id), ['amount' => $firstRepayment->amount - 1]);
        $formattedAmount = Money::USD( (int) $firstRepayment->amount * 100)->getAmount() / 100;
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.amount', ['The amount must be at least '. $formattedAmount .'.']);
    }

    /**
     * A user login test
     *
     * @return void
     * @test
     */
    public function user_cannot_pay_after_cycle_missed()
    {
        $user = $this->createAndLoginUser();

        $requestData = [
            'term'   => 3,
            'amount' => 10000
        ];


        $response = $this->postJson(route('loans.store'), $requestData);
        $response->assertCreated();

        $admin = User::factory()->admin()->create();
        Sanctum::actingAs(
            $admin,
            [config('aspire.token_name')]
        );

        $response = $this->putJson(route('loans.approve', $user->loans[0]->id), ['approve' => 1]);
        $response->assertOk();

        Sanctum::actingAs(
            $user,
            [config('aspire.token_name')]
        );

        $firstRepayment = $user->loans[0]->repayments[0];

        $futureDate = now()->addDays(8);
        Carbon::setTestNow($futureDate);   

        $response = $this->postJson(route('loans.repay', $user->loans[0]->id), ['amount' => $firstRepayment->amount]);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.amount', ['You have missed repayment cycle. Please contact us']);

    }
}
