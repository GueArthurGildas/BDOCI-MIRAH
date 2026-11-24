<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModalFormRedirects
{
    public const HEADER = 'X-Gs-Modal';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->headers->has(self::HEADER) && $response instanceof RedirectResponse) {
            return new JsonResponse(['redirect' => $response->getTargetUrl()]);
        }

        return $response;
    }
}
