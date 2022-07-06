<?php

namespace App\Http\Resources;

use App\Services\CsApiService;
use App\Services\PaymentCustomerService;
use App\utils\Constants;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Stripe\Charge;
use Stripe\Invoice;
use Stripe\PaymentIntent;
use Stripe\Source;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SepaDebitPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        // TODO Add new request
        // The default response
        $response = [
            'version' => Constants::API_VERSION,
            'datetime' => date('c'),
            'path' => $_SERVER['REQUEST_SCHEME'] .'://' .$_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'],
            'data' => [
                'response' => [
                    'status' => ResponseAlias::HTTP_BAD_REQUEST,
                    'message' => ResponseAlias::$statusTexts[400],
                ],
            ],
        ];

        // Client
        $firstName = $request['user']['firstname'];
        $lastName = $request['user']['lastname'];
        $name = $firstName . ' ' . $lastName;
        $email = $request['user']['email'];
        $phoneNumber = $request['user']['phone'];

        $cancelDate = $request['user']['canceldate'];

        // Adress
        $city = $request['user']['city'];
        $address = $request['user']['street_and_nr'];
        $postalCode = $request['user']['zipcode'];

        // User message
        $metadata = $request['message'];

        // Customer URL API
        $apiUrl = config('api.base_url');

        // Currency
        $currency = Constants::SEPA_DEBIT_CURRENCY;

        // Landcode for example USA or NL
        $landCode = config('api.land_code');

        $iBan = $request['user']['IBAN'];

        $items = $request['items'];

        // Payment price
        $amount = calculate_price($items, true);

        try {
            // Create source object
            $sourceObject = Source::create([
                'type' => 'sepa_debit',
                'sepa_debit' => ['iban' => $iBan],
                'currency' => $currency,
                'owner' => [
                    'name' => $name,
                    'email' => $email,
                    'address' => [
                        'city' => $city,
                        'country' => $landCode,
                        'line1' => $address,
                        'postal_code' => $postalCode,
                    ],
                    'phone' => $phoneNumber
                ],
            ]);

            $customer = (new PaymentCustomerService())->create($name, $email, $city, $landCode, $address, $postalCode, $phoneNumber, $sourceObject);

            $charge = create_charge($customer, $amount, $currency, (string) $landCode, $metadata, $city, $address, $postalCode, $name, $sourceObject, $phoneNumber);

            if ($charge->status != Charge::STATUS_FAILED) {
                $response['data']['response']['status'] = ResponseAlias::HTTP_OK;
                $response['data']['response']['message'] = ResponseAlias::$statusTexts[200];

                (new CsApiService())->call($apiUrl, $firstName, $lastName, $email, $cancelDate, $address, $postalCode, $city, $items, $customer);
            }
        } catch (Exception $ex) {
            $response['data']['response']['exception'] = $ex->getMessage();
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param JsonResponse $response
     */
    public function withResponse($request, $response)
    {
        // Bunch of policy and security related settings; check relevant documentation for definition and current support by browser agent
        $response->header('Vary', 'Origin');
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('X-Frame-Options', 'DENY');
        $response->header('X-XSS-Protection', ['1', 'mode=block']);
        $response->header('Referrer-Policy', 'same-origin');
        $response->header('Feature-Policy', ["accelerometer 'none'", "ambient-light-sensor 'none'", "autoplay 'none'", "camera 'none'", "encrypted-media 'none'", "fullscreen 'self'", "geolocation 'none'", "gyroscope 'none'", "magnetometer 'none'", "microphone 'none'", "midi 'none'", "picture-in-picture 'none'", "speaker 'none'", "usb 'none'", "vr 'none'"]);
        $response->header('Content-Security-Policy', ["default-src 'none'", "script-src 'self'", "img-src 'self'", "style-src 'self'",  "connect-src 'self'"]);

        // Tell the browser agent this document is json (which is UTF-y in nature)
        $response->header('Content-Type', 'application/json');

        // No caching please
        $response->header('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $response->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        $response->header('Cache-Control', ['no-store', 'no-cache', 'must-revalidate']);
        $response->header('Cache-Control', ['post-check=0', 'pre-check=0'], false);
        $response->header('Pragma', 'no-cache');
    }
}
