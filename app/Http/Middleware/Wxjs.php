<?php

namespace App\Http\Middleware;
use App\Tools\Jssdk;
use Closure;

class Wxjs
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $jssdk = new Jssdk();
        $signPackage = $jssdk->GetSignPackage();
        $wxconfig=['signPackage'=>$signPackage];
        $request->merge($wxconfig);
        return $next($request);
    }
}
