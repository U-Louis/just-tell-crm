<?php

use App\Domain\Crm\CrmException;
use App\Http\CrmLog;
use App\Http\Middleware\AssignCorrelationId;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(prepend: [
            AssignCorrelationId::class,
        ], append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'voice/catalog',
            'voice/tools/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReport([
            CrmException::class,
        ]);

        $exceptions->render(function (CrmException $exception, Request $request) {
            CrmLog::write($request, $exception->featureId, 'refused', $exception->errorCode);

            $error = [
                'code' => $exception->errorCode,
                'feature_id' => $exception->featureId,
            ];
            if ($exception->fields !== null) {
                $error['fields'] = $exception->fields;
            }

            return back()->with('error', $error);
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if ($exception instanceof CrmException
                || $exception instanceof ValidationException
                || $exception instanceof HttpResponseException
                || $exception instanceof HttpExceptionInterface
                || ! $request->hasSession()) {
                return null;
            }

            CrmLog::write($request, null, 'error', 'error.unexpected');

            $previous = url()->previous();
            if ($previous === $request->fullUrl() || $previous === url()->current()) {
                return null;
            }

            return back()->with('error', [
                'code' => 'error.unexpected',
                'feature_id' => null,
            ]);
        });
    })->create();
