<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    protected $levels = [];
    protected $dontReport = [];
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // 🔹 Step 1: API request  handleApiExceptions
        if ($this->isApiRequest($request)) {
            return $this->handleApiExceptions($exception);
        }

        // 🔹 Step 2: Forbidden requests (web)
        if ($this->isForbiddenException($exception)) {
            return response()->view('errors.403', [], Response::HTTP_FORBIDDEN);
        }

        // 🔹 Step 3: Web request — log + friendly view
        $functionName = $this->getFunctionFromTrace($exception);
        $lineNumber = $exception->getLine();

        //  Exception  function name
        logStore($functionName . ' at line ' . $lineNumber, $exception->getMessage());

        // 🔹 Step 4: Production  Custom error page
        if (app()->environment('production')) {
            session()->flash('dismiss', 'Something went wrong! Please try again later.');

            return response()->view('errors.custom', [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 🔹 Step 5: Local/dev  default Laravel debug
        return parent::render($request, $exception);
    }

    protected function handleApiExceptions(Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json([
                'status' => Response::HTTP_UNAUTHORIZED,
                'success' => false,
                'message' => '',
                'error_message' => $exception->getMessage(),
                'data' => [],
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($exception instanceof ValidationException) {
            return response()->json([
                'status' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'message' => '',
                'error_message' => $exception->getMessage(),
                'data' => [],
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($exception instanceof ThrottleRequestsException) {
            return response()->json([
                'status' => Response::HTTP_TOO_MANY_REQUESTS,
                'success' => false,
                'message' => 'Too Many Requests. Please try again later.',
                'error_message' => $exception->getMessage(),
                'data' => [],
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        if ($this->isForbiddenException($exception)) {
            return response()->json([
                'status' => Response::HTTP_FORBIDDEN,
                'success' => false,
                'message' => 'Permission denied',
                'error_message' => $exception->getMessage() ?: 'Forbidden',
                'data' => [],
            ], Response::HTTP_FORBIDDEN);
        }

        //  Exception   function name
        $functionName = $this->getFunctionFromTrace($exception);
        $lineNumber = $exception->getLine();
        logStore($functionName . ' at line ' . $lineNumber, $exception->getMessage());

        return response()->json([
            'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'success' => false,
            'error_message' => $exception->getMessage(),
            'message' => 'Something went wrong',
            'data' => [],
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    protected function isApiRequest($request)
    {
        return $request->is('api/*') || $request->wantsJson() || $request->expectsJson();
    }

    protected function isForbiddenException(Throwable $exception): bool
    {
        if ($exception instanceof AuthorizationException || $exception instanceof AccessDeniedHttpException) {
            return true;
        }

        return $exception instanceof HttpExceptionInterface
            && $exception->getStatusCode() === Response::HTTP_FORBIDDEN;
    }

    protected function getFunctionFromTrace(Throwable $exception)
    {
        foreach ($exception->getTrace() as $trace) {
            if (isset($trace['class']) && str_starts_with($trace['class'], 'App\\')) {
                return $trace['function'] ?? 'N/A';
            }
        }
        return $exception->getTrace()[0]['function'] ?? 'N/A';
    }
}
