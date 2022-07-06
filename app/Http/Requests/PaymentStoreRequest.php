<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Intervention\Validation\Rules\Creditcard;
use Intervention\Validation\Rules\Iban;

class PaymentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        if (Auth::guard('api')->check()) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user.firstname' => [
                'required',
                'string',
            ],
            'user.lastname' => [
                'required',
                'string',
            ],
            'user.street_and_nr' => [
                'required',
                'string',
            ],
            'user.zipcode' => [
                'required',
                'string',
            ],
            'user.city' => [
                'required',
                'string',
            ],
            'user.canceldate' => [
                'required',
            ],
            'user.email' => [
                'required',
                'string',
                'email:dns',
            ],

//            'user.IBAN' => [
//                Rule::requiredIf(function () {
//                    return config('api.land_code') != ('USA');
//                }),
//                'exclude_if:user.IBAN,""',
//                'exclude_if:user.IBAN,null',
//                new Iban(),
//            ],
//
//            'user.cardnumber' => [
//                Rule::requiredIf(function () {
//                    return config('api.land_code') == ('USA');
//                }),
//                new Creditcard(),
//            ],
//            'user.exp_year' => [
//                Rule::requiredIf(function () {
//                    return config('api.land_code') == ('USA');
//                }),
//                'integer',
//            ],
//            'user.exp_month' => [
//                Rule::requiredIf(function () {
//                    return config('api.land_code') == ('USA');
//                }),
//                'integer',
//            ],
//            'user.cvc' => [
//                Rule::requiredIf(function () {
//                    return config('api.land_code') == ('USA');
//                }),
//                'string',
//            ],

            'items' => [
                'required',
            ],

            'paymentOption' => [
                'required',
                'string'
            ]
        ];
    }
}
