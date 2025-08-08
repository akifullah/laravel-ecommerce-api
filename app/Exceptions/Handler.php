
<?php


use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    // ... (other methods and properties)

    /**
     * Handle unauthenticated exception.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json([
            'success' => false,
            'message' => 'You must be logged in to access this resource.',
            'error' => 'Unauthenticated'
        ], 401);
    }
}
