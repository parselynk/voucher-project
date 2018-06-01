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

class OfferTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function an_offer_has_name()
    {
        $offer = new Offer(['name' => 'offer name']);

        $this->assertEquals('offer name', $offer->name);
    }

    /** @test */
    public function offer_name_is_unique()
    {
        $offer = factory(Offer::class)->create(['name' => 'offer name']);

        $this->assertEquals('offer name', $offer->name);

        $this->expectException('exception');

        $offer = factory(Offer::class)->create(['name' => 'offer name']);
    }

    /** @test */
    public function an_offer_has_discount()
    {
        $offer = factory(Offer::class)->create(['name' => 'offer name']);

        $this->assertCount(1, $offer->all());

        $this->assertDatabaseHas('offers', [
            'discount' => $offer->discount,
        ]);
    }

    /** @test */
    public function an_offer_store_discount_percentage_as_float()
    {
        $offer = factory(Offer::class)->create(['name' => 'offer name']);

        $this->assertInternalType('float', $offer->discount);
    }

    /** @test */
    public function an_offer_can_generate_a_voucher_per_each_recipient()
    {
        $offer = factory(Offer::class)->create(['name' => 'offer name']);

        $voucher = new Voucher;

        $users = factory(User::class, 100)->create();

        $this->assertDatabaseHas('users', [
            'name' => $users[10]->name,
        ]);

        $usersData = $users->map(function ($user, $key) {
            return [
                'code' => str_random(10),
                'expire_at' => Carbon::today(),
                'user_id' => $user['id']
            ];
        });


        $offer->vouchers()->createMany($usersData->toArray());
        $this->assertCount(100, $offer->vouchers()->get()->toArray());
    }

    /** @test */
    public function an_offer_has_unique_vouchers_based_on_dates()
    {
        $offer = factory(Offer::class)->create(['name' => 'offer name']);

        $users = factory(User::class, 100)->create();

        $this->assertDatabaseHas('users', [
            'name' => $users[10]->name,
        ]);

        // first set of users
        $usersData = $users->map(function ($user, $key) {
            return [
                'code' => str_random(10),
                'expire_at' => Carbon::today(),
                'user_id' => $user['id']
            ];
        });

        $offer->vouchers()->createMany($usersData->toArray());

        //second set of users
        $usersData = $users->map(function ($user, $key) {
            return [
                'code' => str_random(10),
                'expire_at' => Carbon::tomorrow(),
                'user_id' => $user['id']
            ];
        });

        $offer->vouchers()->createMany($usersData->toArray());

        $this->assertCount(200, $offer->vouchers()->get()->toArray());

        $this->expectException('exception');

        //generate vouchers with same set of users
        $offer->vouchers()->createMany($usersData->toArray());
        $this->assertCount(200, $offer->vouchers()->get()->toArray());
    }

    /** @test */
    public function an_offer_is_aware_of_its_vouchers_count()
    {
        $offer = factory(Offer::class)->create(['name' => 'offer name']);

        $users = factory(User::class, 100)->create();

        $this->assertDatabaseHas('users', [
            'name' => $users[10]->name,
        ]);

        $usersData = $users->map(function ($user, $key) {
            return [
                'code' => str_random(10),
                'expire_at' => Carbon::today(),
                'user_id' => $user['id']
            ];
        });


        $offer->vouchers()->createMany($usersData->toArray());

        $this->assertEquals(100, $offer->vouchersCount());
    }

    
    /** @test */
    public function a_post_can_count_its_used_and_unused_vouchers()
    {
        $offer = factory(Offer::class)->create(['name' => 'offer name']);

        $users = factory(User::class, 3)->create();

        $usersData = $users->map(function ($user, $key) {
            return [
                'code' => str_random(10),
                'expire_at' => Carbon::today(),
                'user_id' => $user['id']
            ];
        });


        $offer->vouchers()->createMany($usersData->toArray());
        $this->assertEquals(0, $offer->usedVouchers());

        $voucher = $offer->vouchers()->whereNull('used_at')->first();

        $offer->vouchers()->where('code', $voucher->code)->update(['used_at' => Carbon::now()]);

        $this->assertEquals(1, $offer->usedVouchers());
        $this->assertEquals(2, $offer->unusedVouchers());
    }
}
