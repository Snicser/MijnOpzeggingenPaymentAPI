<?php

namespace App\Http\Resources;

use App\Services\BacsDebitPaymentService;
use App\Services\CsApiService;
use App\Services\PaymentCustomerService;
use App\utils\Constants;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Stripe\PaymentIntent;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class BacsDebitPaymentResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        // The default response
        $response = [
            'version' => Constants::API_VERSION,
            'datetime' => date('c'),
            'path' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
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
        $name = sprintf('%s %s', $firstName, $lastName);
        $email = $request['user']['email'];
        $cancelDate = $request['user']['canceldate'];

        // Adress
        $city = $request['user']['city'];
        $address = $request['user']['street_and_nr'];
        $postalCode = $request['user']['zipcode'];

        // Customer URL API
        $apiUrl = config('api.base_url');

        // Landcode for example USA or NL
        $landCode = config('api.land_code');

        $items = $request['items'];

        // Payment price
        $amount = calculate_price($items, false);

        // Account number and sort code
        $accountNumber = $request['user']['account_number'];
        $sortCode = $request['user']['sort_code'];

        // User message
        $companyName = $request['message'];

        try {
            $customer = (new PaymentCustomerService())->create($name, $email, $city, $landCode, $address, $postalCode);

            $paymentIntent = (new BacsDebitPaymentService())->pay($amount, $customer, $city, $landCode, $address, $postalCode, $name, $request->ip(), $request->userAgent(), $accountNumber, $sortCode, $email, $companyName);

            if ($paymentIntent->status != PaymentIntent::STATUS_CANCELED) {
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
