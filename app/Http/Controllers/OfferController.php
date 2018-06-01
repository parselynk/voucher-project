<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Contracts\OfferInterface;

class OfferController extends Controller
{
    
    private $offer;

    public function __construct(OfferInterface $offer)
    {
        $this->offer = $offer;
    }
    
    public function index()
    {
        $offers = $this->offer->all();
        $statistics = $this->offer->statistics();
        return view('offer', compact('offers', 'statistics'));
    }

    public function create()
    {

        request()->validate(
            ['name' => 'required',
            'discount' => 'required|digits_between:1,100'],
            $this->validationMessages()
        );

        $response = $this->offer->create(request(['name','discount']));

        if (!$response->status()) {
            return back()->withErrors([
                "message" =>  $response->message()
            ]);
        }
        
        if ($response->message() !== null) {
            session()->flash('message', $response->message());
        }

        return redirect()->back();
    }

    public function validationMessages()
    {
        return [
            'name.required' => 'Please provide a name for the offer.',
        ];
    }
}
