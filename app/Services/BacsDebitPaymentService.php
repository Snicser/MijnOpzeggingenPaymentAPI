<?php

namespace App\Services;

use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;

class BacsDebitPaymentService
{

    /**
     * @throws ApiErrorException
     */
    public function pay(float $amount, Customer $customer, string $city, string $landCode, string $address, string $postalCode, string $name, string $userIp, string $userAgent, string $accountNumber, string $sortCode, string $email, string $company): PaymentIntent{
        // Payment method
        return PaymentIntent::create([
            'amount' => $amount * 100,
            'currency' => "gbp",
            'customer' => $customer->id,
            'shipping' => [
                'address' => [
                    'city' => $city,
                    'country' => $landCode,
                    'line1' => $address,
                    'postal_code' => $postalCode,
                ],
                'name' => $name,
            ],
            'confirm' => true,
            'mandate_data' => [
                'customer_acceptance' => [
                    'type' => 'online',
                    'online' => [
                        'ip_address' => $userIp,
                        'user_agent' => $userAgent,
                    ],
                ],
            ],
            'description' => 'Cancelled ' . $company,
            'metadata' => [
                'Cancelled subscription' => $company,
            ],
            'setup_future_usage' => 'off_session',
            'payment_method_types' => ['bacs_debit'],
            'payment_method_data' => [
                'type' => 'bacs_debit',
                'bacs_debit' => [
                    'account_number' => $accountNumber,
                    'sort_code' => $sortCode,
                ],
                'billing_details' => [
                    'address' => [
                        'city' => $city,
                        'country' => $landCode,
                        'line1' => $address,
                        'postal_code' => $postalCode,
                    ],
                    'email' => $email,
                    'name' => $name,
                ]
            ],
            'use_stripe_sdk' => false,
        ]);
    }

}
