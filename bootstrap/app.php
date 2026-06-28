<?php

use App\Core\Exceptions\OrderException;
use App\Core\Exceptions\PaymentException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Exceptions\RouteNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            AuthenticationException $e,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => null,
            ], 401);
        });

        $exceptions->shouldRenderJsonWhen(function ($request, Throwable $e) {
            return $request->is('api/*')
                || $request->expectsJson()
                || $request->wantsJson();
        });

        $exceptions->render(function (Throwable $exception) {
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof UnauthorizedHttpException || $exception instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. يجب تسجيل الدخول أولاً.',
                    'errors' => null,
                ], 401);
            }

            if ($exception instanceof AuthorizationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action. ليس لديك صلاحية للوصول.',
                    'errors' => null,
                ], 403);
            }

            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found. العنصر غير موجود.',
                    'errors' => null,
                ], 404);
            }

            if ($exception instanceof TokenExpiredException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has expired. انتهت صلاحية التوكن.',
                    'errors' => null,
                ], 401);
            }

            if ($exception instanceof TokenInvalidException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token is invalid. التوكن غير صالح.',
                    'errors' => null,
                ], 401);
            }

            if ($exception instanceof TokenBlacklistedException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has been blacklisted. التوكن تم حظره.',
                    'errors' => null,
                ], 401);
            }

            if ($exception instanceof JWTException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not provided. التوكن غير موجود.',
                    'errors' => null,
                ], 401);
            }

            if ($exception instanceof RouteNotFoundException && str_contains($exception->getMessage(), 'Route [login] not defined.')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. يجب تسجيل الدخول أولاً.',
                    'errors' => null,
                ], 401);
            }

            if ($exception instanceof HttpExceptionInterface) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage() ?: 'HTTP error.',
                    'errors' => null,
                ], $exception->getStatusCode());
            }

            if ($exception instanceof OrderException || $exception instanceof PaymentException) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'errors' => null,
                ], $exception->getCode() ?: 400);
            }

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $exception->getMessage() : 'Server error.',
                'errors' => null,
            ], 500);
        });
    })->create();
