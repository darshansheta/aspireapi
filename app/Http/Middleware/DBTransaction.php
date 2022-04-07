<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DBTransaction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        \DB::beginTransaction();

        try {
            $response = $next($request);
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }

        if ($response instanceof JsonResponse && $response->getStatusCode() > 399) {
            \DB::rollBack();
        } else {
            \DB::commit();
        }

        return $response;
    }
}
