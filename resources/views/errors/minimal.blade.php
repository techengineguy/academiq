@extends('errors.layout', [
    'code'          => $exception->getStatusCode(),
    'title'         => 'Something Went Wrong',
    'message'       => $exception->getMessage() ?: 'An unexpected error occurred. Please try again or contact support if the problem persists.',
    'accent'        => '#485AE0',
    'codeGradient'  => '#818cf8',
    'glow'          => 'rgba(72,90,224,0.12)',
])
