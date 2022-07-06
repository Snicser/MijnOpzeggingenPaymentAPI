<?php

namespace App\Http\Resources;

use App\utils\Constants;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DefaultPaymentResource extends JsonResource
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
        return [
            'version' => Constants::API_VERSION,
            'datetime' => date('c'),
            'path' => $_SERVER['REQUEST_SCHEME'] .'://' .$_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'],
            'data' => [
                'response' => [
                    'status' => ResponseAlias::HTTP_BAD_REQUEST,
                    'message' => ResponseAlias::$statusTexts[400],
                    'info' => 'No payment option provided'
                ],
            ],
        ];
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
