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

class VoucherRepositoryTest extends TestCase
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
    public function it_retrives_all_recipients()
    {
        factory(User::class, 5)->create();
        $this->assertCount(5, $this->voucherRepository->availableRecipients());
    }

    /** @test */
    public function it_gets_all_vouchers()
    {
        $offer = factory(Offer::class, 1)->create();
        factory(\App\Voucher::class, 5)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::today()]);

        $this->assertCount(5, $this->voucherRepository->all());
    }

    /** @test */
    public function it_gets_all_unused_vouchers()
    {
        $offer = factory(Offer::class, 1)->create();
        factory(\App\Voucher::class, 5)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::today()]);

        $this->assertCount(5, $this->voucherRepository->unusedVouchers());
    }


    /** @test */
    public function it_gets_all_used_vouchers()
    {
        $offer = factory(Offer::class, 1)->create();
        factory(\App\Voucher::class, 5)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::today()]);
        
        $this->voucherModel->where('code', $this->voucherModel->first()->code)->update(['used_at' => Carbon::today()]);


        $this->assertCount(4, $this->voucherRepository->unusedVouchers());
        $this->assertCount(1, $this->voucherRepository->usedVouchers());
    }


    /** @test */
    public function each_vocuher_blongs_to_one_recipient()
    {
        $offer = factory(Offer::class, 1)->create();
        $voucher = factory(\App\Voucher::class)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::today()]);


        $this->assertDatabaseHas('users', [
            'name' => $voucher->first()->user->name,
        ]);
    }

    /** @test */
    public function each_vocuher_blongs_to_one_offer()
    {
        $offer = factory(Offer::class, 1)->create();
        $voucher = factory(\App\Voucher::class)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::today()]);
        $this->assertDatabaseHas('offers', [
            'name' => $voucher->first()->offer->name,
        ]);
    }

    /** @test */
    public function it_prepares_data_to_generate_voucher_for_each_user()
    {
        $offer = factory(Offer::class, 1)->create();
        factory(User::class, 5)->create();

        $this->assertCount(5, $this->voucherRepository->prepareVouchersData(Carbon::today()));

        $dataArraySample = $this->voucherRepository->prepareVouchersData(Carbon::today())[0];

        $this->assertArrayHasKey('user_id', $dataArraySample);
        $this->assertArrayHasKey('expire_at', $dataArraySample);
        $this->assertArrayHasKey('code', $dataArraySample);
    }

    /** @test */
    public function it_updates_voucher_status_as_used()
    {
        $offer = factory(Offer::class, 1)->create();
        $vouchers = factory(\App\Voucher::class, 5)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::today()]);

        $this->voucherRepository->find($vouchers->first()->code)->setAsUsed();
        $this->voucherRepository->find($vouchers->last()->code)->setAsUsed();

        
        $this->assertCount(3, $this->voucherRepository->unusedVouchers());
        $this->assertCount(2, $this->voucherRepository->usedVouchers());
    }

    /** @test */
    public function it_checks_whether_a_vocuher_is_used_or_not()
    {
        $offer = factory(Offer::class, 1)->create();
        $vouchers = factory(\App\Voucher::class, 5)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::today()]);
       
        $this->assertFalse($this->voucherRepository->find($vouchers->first()->code)->isUsed());


        $this->voucherRepository->find($vouchers->first()->code)->setAsUsed();

        $this->assertTrue($this->voucherRepository->find($vouchers->first()->code)->isUsed());
        
        $this->assertCount(4, $this->voucherRepository->unusedVouchers());
        $this->assertCount(1, $this->voucherRepository->usedVouchers());
    }

    /** @test */
    public function it_checks_whether_a_vocuher_is_expired_or_not()
    {
        $offer = factory(Offer::class, 1)->create();
        $vouchers = factory(\App\Voucher::class, 5)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::today()->endOfDay()]);

        $this->assertFalse($this->voucherRepository->find($vouchers->first()->code)->isExpired());
        $this->voucherModel->where('code', $vouchers->first()->code)->update(['expire_at' => Carbon::today()]);
        $this->assertTrue($this->voucherRepository->find($vouchers->first()->code)->isExpired());
    }

    /** @test */
    public function it_verifies_if_voucher_belongs_to_recipient()
    {
        $offer = factory(Offer::class, 1)->create();
        $vouchers = factory(\App\Voucher::class, 5)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::today()->endOfDay()]);

        $this->assertTrue($this->voucherRepository->find($vouchers->first()->code)->isRecipientValid($vouchers->first()->user->email));
    }

    /** @test */
    public function it_finds_available_vouchers_for_recipient()
    {
        //this query needs to be optimized
        
        $users = factory(User::class, 10)->create();

        $this->generateACustomOfferAndAVoucher(['name'=> 'offer 1' , 'discount' => '0.34']);
        $this->generateACustomOfferAndAVoucher(['name'=> 'offer 2' , 'discount' => '0.12']);
        $this->generateACustomOfferAndAVoucher(['name'=> 'offer 3' , 'discount' => '0.05']);

        // total emails are 30
        $this->assertCount(30, $this->offerRepository->voucher()->all());

        // 3 valid vouchers are available for recipient
        $this->assertCount(3, $this->voucherRepository->availableVouchersForRecipient($users[0]->email));

        // selecting vouchers
        $voucher_1 = $this->voucherRepository->availableVouchersForRecipient($users[0]->email)->toArray()[0];
        $voucher_2 = $this->voucherRepository->availableVouchersForRecipient($users[0]->email)->toArray()[1];

        //used voucher case
        $this->voucherRepository->find($voucher_1['code'])->setAsUsed();
        $this->assertCount(2, $this->voucherRepository->availableVouchersForRecipient($users[0]->email));
        
        //expired voucher case
        $this->voucherRepository->model()->where('code', $voucher_2['code'])
                                ->update(['expire_at' => Carbon::yesterday()]);
        $this->assertCount(1, $this->voucherRepository->availableVouchersForRecipient($users[0]->email));

        //invalid voucher case
        $this->assertCount(0, $this->voucherRepository->availableVouchersForRecipient('email@fake.null'));
    }

    /** @test */
    public function it_verifies_voucher_validity_and_sets_it_as_used_if_all_the_requirements_passed()
    {
        $offer = factory(Offer::class, 1)->create();
        $vouchers = factory(\App\Voucher::class, 5)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::today()->endOfDay()]);

        //returns voucher instance
        $this->assertInstanceOf(\App\Voucher::class, $this->voucherRepository->verify($vouchers->first()->code, $vouchers->first()->user->email));

         //returns false if email is not valid
         $this->assertFalse($this->voucherRepository->verify($vouchers->last()->code, 'invalidemail'));


        //voucher is already used
        $this->assertFalse($this->voucherRepository->verify($vouchers->first()->code, $vouchers->first()->user->email));

        //voucher is expired
        $voucher = factory(\App\Voucher::class)->create(['offer_id' => $offer->first()->id, 'expire_at' => Carbon::yesterday()]);
        $this->assertFalse($this->voucherRepository->verify($voucher->first()->code, $voucher->first()->user->email));


        //throws exception if voucher code is invalid
        $this->expectException('exception');
        $this->voucherRepository->verify('invalidcode', $vouchers->first()->user->email);
    }

    protected function generateACustomOfferAndAVoucher($params = [])
    {
         $offer = $this->offerRepository->create($params);
         $this->offerRepository->for($offer->dataset()->id)->generateVouchers(Carbon::tomorrow());
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
