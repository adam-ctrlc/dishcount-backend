<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OnlyJson
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        // Allow multipart/form-data for file uploads and product management
        if (
            $request->hasFile('image') ||
            $request->hasFile('profile_picture') ||
            $request->hasFile('avatar') ||
            str_contains($request->header('Content-Type', ''), 'multipart/form-data') ||
            str_contains($request->getPathInfo(), '/products')
        ) {
            $response = $next($request);
            if ($response instanceof Response) {
                $response->headers->set('Content-Type', 'application/json');
            }
            return $response;
        }

        // Check if the request is not JSON
        if (!$request->expectsJson()) {
            return response()->json([
                'error' => 'Only JSON requests are allowed.'
            ], 406);
        }

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            // Check if the request has a valid JSON body
            if (!$request->isJson()) {
                return response()->json([
                    'error' => 'Invalid JSON body.'
                ], 400);
            }
        }

        $response = $next($request);

        if ($response instanceof Response) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}
