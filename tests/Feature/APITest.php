<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Repositories\Contracts\OfferInterface;
use App\Repositories\Contracts\VoucherInterface;
use App\Repositories\VoucherRepository;
use App\Repositories\OfferRepository;

use App\Offer;
use App\Voucher;
use App\User;
use Carbon\Carbon;

class APITest extends TestCase
{
    use RefreshDatabase;

    public $offerModel;
    public $userModel;
    public $voucherModel;

    public $offerRepository;
    public $voucherRepository;

    public function setUp()
    {
        parent::setUp();
        $this->initiateRepository();
    }

    /** @test */
    public function api_shows_error_messages_on_wrong_parameters_enter_for_verify_endpoint()
    {
        $email = 'email';
        $code = 'code';
        $response = $this->json('GET', "/api/verify/{$email}/{$code}");

        $response
            ->assertStatus(404)
            ->assertJson([
                'data' => [
                    'errors' => [
                        'email' => ['The email must be a valid email address.'],
                        'code' => ['Voucher code should not  should not be less than 10 chars']
                    ]
                ],
                'success' => false
            ]);
    }

    /** @test */
    public function it_shows_error_if_recipient_email_is_invalid_on_verify_endpoint()
    {
        $email = 'idietrich@example.or';
        $code = 'code';
        $response = $this->json('GET', "/api/verify/{$email}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => 'The Recipient Email is not valid',
                'success' => false
            ]);
    }

    /** @test */
    public function it_shows_error_if_recipient_email_is_invalid_on_show_endpoint()
    {
        $email = 'idietrich@example.or';
        $code = 'code';
        $response = $this->json('GET', "/api/get/{$email}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => 'The Recipient Email is not valid',
                'success' => false
            ]);
    }

    /** @test */
    public function it_returns_available_vouchers_for_a_specific_recepients_on_verify_and_get_endpoints()
    {
        $offer = factory(Offer::class, 1)->create();
        $vouchers = factory(\App\Voucher::class, 5)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::today()->endOfDay()]);

        $email = $vouchers->first()->user->email;
        $code = $vouchers->first()->code;
        $offerTitle = $vouchers->first()->offer->name;
        $discount = $vouchers->first()->offer->discount * 100 ."%";
        $expiry =  \Carbon\Carbon::parse($vouchers->first()->expire_at)->toDateString();

        $data = [
            "code" => $code,
            "Offer" => $offerTitle,
            "discount" => $discount ,
            "expiry"=> $expiry
        ];


        // verify endpoint
        $response = $this->json('GET', "/api/verify/{$email}");
        
        $response
            ->assertStatus(200)
            ->assertExactJson(
                [
                'data' =>[ $data ],
                'success' => true
                ]
            );


        // get endpoint
        $response = $this->json('GET', "/api/get/{$email}");
        
        $response
            ->assertStatus(200)
            ->assertExactJson(
                [
                'data' =>[ $data ],
                'success' => true
                ]
            );
    }

        /** @test */
    public function it_only_verifies_a_voucer_once_and_returns_error_message_as_response_to_second_call_on_verify_endpoint()
    {
        $offer = factory(Offer::class, 1)->create();
        $vouchers = factory(\App\Voucher::class, 5)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::today()->endOfDay()]);

        $code = $vouchers->first()->code;
        $discount = $vouchers->first()->offer->discount*100 . "%";
        $email = $vouchers->first()->user->email;


        // verify endpoint
        $response = $this->json('GET', "/api/verify/{$email}/{$code}");

        // affter being set as used
        $used_at = $this->voucherRepository->model()->where('code', $code)->first()->used_at;

        $data = [
            "discount" => $discount,
            "used_at" => $used_at,
        ];
        
        $response
            ->assertStatus(200)
            ->assertExactJson(
                [
                'data' => $data ,
                'success' => true
                ]
            );

        // verify endpoint
        $response = $this->json('GET', "/api/verify/{$email}/{$code}");

        // affter being set as used
        $used_at = $this->voucherRepository->model()->where('code', $code)->first()->used_at;

        $data = [
            "discount" => $discount,
            "used_at" => $used_at,
        ];
        
        $response
            ->assertStatus(200)
            ->assertExactJson(
                [
                'data' => 'The voucher/ Email is not valid' ,
                'success' => false
                ]
            );
    }

    /** @test */
    public function it_returns_offer_discount_and_used_date_as_response_on_verify_endpoint()
    {
        $offer = factory(Offer::class, 1)->create();
        $vouchers = factory(\App\Voucher::class, 5)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::today()->endOfDay()]);

        $code = $vouchers->first()->code;
        $discount = $vouchers->first()->offer->discount*100 . "%";
        $email = $vouchers->first()->user->email;


        // verify endpoint
        $response = $this->json('GET', "/api/verify/{$email}/{$code}");

        // affter being set as used
        $used_at = $this->voucherRepository->model()->where('code', $code)->first()->used_at;

        $data = [
            "discount" => $discount,
            "used_at" => $used_at,
        ];
        
        $response
            ->assertStatus(200)
            ->assertExactJson(
                [
                'data' => $data ,
                'success' => true
                ]
            );
    }


    protected function initiateRepository()
    {
        $this->offerModel = new Offer;
        $this->voucherModel = new Voucher;
        $this->userModel = new User;
        $this->voucherRepository = new VoucherRepository($this->voucherModel, $this->userModel);
        $this->offerRepository = new OfferRepository($this->voucherRepository, $this->offerModel);
    }
}
