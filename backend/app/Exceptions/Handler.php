<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Custom exception rendering for API requests
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Handle exceptions for API requests.
     */
    protected function handleApiException(Throwable $e, Request $request): JsonResponse
    {
        return match (true) {
            $e instanceof ValidationException => $this->handleValidationException($e),
            $e instanceof AuthenticationException => $this->handleAuthenticationException($e),
            $e instanceof ModelNotFoundException => $this->handleModelNotFoundException($e),
            $e instanceof NotFoundHttpException => $this->handleNotFoundHttpException($e),
            $e instanceof MethodNotAllowedHttpException => $this->handleMethodNotAllowedHttpException($e),
            $e instanceof HttpException => $this->handleHttpException($e),
            default => $this->handleGenericException($e),
        };
    }

    /**
     * Handle validation exceptions.
     */
    protected function handleValidationException(ValidationException $e): JsonResponse
    {
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $e->errors(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Handle authentication exceptions.
     */
    protected function handleAuthenticationException(AuthenticationException $e): JsonResponse
    {
        return response()->json([
            'message' => 'Unauthenticated.',
            'error' => 'Authentication token is missing or invalid.',
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Handle model not found exceptions.
     */
    protected function handleModelNotFoundException(ModelNotFoundException $e): JsonResponse
    {
        $model = class_basename($e->getModel());

        return response()->json([
            'message' => "{$model} not found.",
            'error' => 'The requested resource does not exist.',
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Handle not found HTTP exceptions.
     */
    protected function handleNotFoundHttpException(NotFoundHttpException $e): JsonResponse
    {
        return response()->json([
            'message' => 'Endpoint not found.',
            'error' => 'The requested endpoint does not exist.',
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Handle method not allowed HTTP exceptions.
     */
    protected function handleMethodNotAllowedHttpException(MethodNotAllowedHttpException $e): JsonResponse
    {
        return response()->json([
            'message' => 'Method not allowed.',
            'error' => 'The HTTP method is not supported for this endpoint.',
        ], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * Handle HTTP exceptions.
     */
    protected function handleHttpException(HttpException $e): JsonResponse
    {
        $statusCode = $e->getStatusCode();
        $message = $e->getMessage() ?: Response::$statusTexts[$statusCode] ?? 'Unknown error';

        return response()->json([
            'message' => $message,
            'error' => $this->getHttpErrorMessage($statusCode),
        ], $statusCode);
    }

    /**
     * Handle generic exceptions.
     */
    protected function handleGenericException(Throwable $e): JsonResponse
    {
        // Log the exception for debugging
        Log::error('Unhandled exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $response = [
            'message' => 'Server Error',
            'error' => 'An unexpected error occurred.',
        ];

        // Include more details in development
        if (config('app.debug')) {
            $response['debug'] = [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),
            ];
        }

        return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Get user-friendly error message for HTTP status codes.
     */
    protected function getHttpErrorMessage(int $statusCode): string
    {
        return match ($statusCode) {
            Response::HTTP_BAD_REQUEST => 'The request was invalid.',
            Response::HTTP_UNAUTHORIZED => 'Authentication is required.',
            Response::HTTP_FORBIDDEN => 'You are not authorized to perform this action.',
            Response::HTTP_NOT_FOUND => 'The requested resource was not found.',
            Response::HTTP_METHOD_NOT_ALLOWED => 'The HTTP method is not allowed.',
            Response::HTTP_UNPROCESSABLE_ENTITY => 'The request data is invalid.',
            Response::HTTP_TOO_MANY_REQUESTS => 'Too many requests. Please try again later.',
            Response::HTTP_INTERNAL_SERVER_ERROR => 'An internal server error occurred.',
            Response::HTTP_SERVICE_UNAVAILABLE => 'The service is temporarily unavailable.',
            default => 'An error occurred.',
        };
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'Authentication token is missing or invalid.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Fixed: redirectTo() method doesn't accept any arguments
        return redirect()->guest(route('login'));
    }

    /**
     * Convert a validation exception into a JSON response.
     */
    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    /**
     * Prepare exception for rendering.
     */
    protected function prepareException(Throwable $e): Throwable
    {
        // Convert ModelNotFoundException to NotFoundHttpException for API
        if ($e instanceof ModelNotFoundException && (request()->is('api/*') || request()->expectsJson())) {
            $e = new NotFoundHttpException('Resource not found', $e);
        }

        return parent::prepareException($e);
    }
}
