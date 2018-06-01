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

class OfferRepositoryTest extends TestCase
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
    public function repository_can_get_all_offers()
    {
        factory(Offer::class, 5)->create();
        $this->assertCount(5, $this->offerRepository->all());
    }

    /** @test */
    public function repository_can_create_a_offer()
    {
        $data = ['name'=> 'check' , 'discount' => '34'];
        $this->offerRepository->create($data);

        $this->assertDatabaseHas('offers', [
            'name' => $this->offerRepository->dataset()->name,
            'discount' => $this->offerRepository->dataset()->discount,
        ]);

        $this->assertCount(1, $this->offerRepository->all());
    }

    /** @test */
    public function repository_creates_unique_offers()
    {
        $data = ['name'=> 'check' , 'discount' => '0.34'];

        $this->offerRepository->create($data);

        $this->offerRepository->create($data);

        $this->assertFalse($this->offerRepository->status());

        $this->assertCount(1, $this->offerModel->where('name', $data['name'])->get());
    }

    /** @test */
    public function repository_returns_message_response_in_success_and_fail()
    {
        $data = ['name'=> 'check' , 'discount' => '0.34'];

        $this->offerRepository->create($data);

        $this->assertEquals('Offer is succesfully created.', $this->offerRepository->message());

        $this->offerRepository->create($data);

        $this->assertStringStartsWith('This Offer already exists.', $this->offerRepository->message());
    }

    /** @test */
    public function repository_generates_vouchers_for_a_offer()
    {
        
        $users = factory(User::class, 100)->create();

        $data = ['name'=> 'check' , 'discount' => '0.34'];

        $offer = $this->offerRepository->create($data);

        $this->offerRepository->for($offer->dataset()->id)->generateVouchers(Carbon::tomorrow());

        $this->assertCount(100, $this->offerRepository->voucher()->all());
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
