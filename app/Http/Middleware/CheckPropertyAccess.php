<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPropertyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Перевірка заголовка для створення нерухомості
        if (
            $request->isMethod('post') &&
            trim((string) $request->header('X-Property-Agent', '')) === ''
        ) {
            return response()->json([
                'status' => 'access_denied',
                'message' => 'Доступ заборонено: відсутній заголовок X-Property-Agent.',
            ], 403);
        }

        // Передача запиту наступному компоненту
        $response = $next($request);

        // Додавання службового заголовка
        $response->headers->set('X-Security-Policy', 'Enforced');

        return $response;
    }
}
