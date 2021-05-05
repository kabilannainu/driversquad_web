<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/provider/request/*',
        '/provider/profile/available',
        '/account/kit',
        '/fare',
        '/indipay/ccavanue/web/response',
        '/indipay/ccavanue/web/cancel/response',
        '/indipay/ccavanue/response',
        '/indipay/ccavanue/cancel/response',
        '/rsa/key',
        '/user/check/mobile',
        '/provider/check/mobile',
        '/login/otp',
        '/provider/login/otp',
        '/admin/user/seleted_delete',
        '/admin/provider/seleted_delete',
        '/admin/requests/seleted_cancelled_delete',
        '/rzp/success',
        '/rzp/flow'

    ];
}
