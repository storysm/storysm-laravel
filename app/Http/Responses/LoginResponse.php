<?php

namespace App\Http\Responses;

use Laravel\Fortify\Http\Responses\LoginResponse as FortifyLoginResponse;

class LoginResponse extends FortifyLoginResponse
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return parent::toResponse($request);
        }

        if ($request->has('next')) {
            /** @var ?string */
            $url = $request->input('next');

            if ($url) {
                /** @var string */
                $appUrl = config('app.url');
                $appHost = parse_url($appUrl, PHP_URL_HOST);
                $checkHost = parse_url($url, PHP_URL_HOST);

                if ($appHost !== null && $checkHost !== null && $appHost === $checkHost) {
                    return redirect($url);
                }
            }
        }

        return parent::toResponse($request);
    }
}
