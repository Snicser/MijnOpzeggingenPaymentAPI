<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentStoreRequest;
use App\Http\Resources\BacsDebitPaymentResource;
use App\Http\Resources\CreditCardPaymentResource;
use App\Http\Resources\DefaultPaymentResource;
use App\Http\Resources\SepaDebitPaymentResource;
use App\utils\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PaymentStoreRequest $request
     * @return BacsDebitPaymentResource|CreditCardPaymentResource|DefaultPaymentResource|SepaDebitPaymentResource
     */
    public function store(PaymentStoreRequest $request)
    {
         $validation = $request->validated();

         switch ($validation['paymentOption']) {
             case Constants::SEPA_DEBIT:
                 return new SepaDebitPaymentResource($validation);
             case Constants::BACS_DEBIT:
                  return new BacsDebitPaymentResource($validation);
             case Constants::CREDIT_CARD:
                 return new CreditCardPaymentResource($validation);
             default:
                 return new DefaultPaymentResource($validation);
         }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
