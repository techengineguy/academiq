@extends('errors.layout', [
    'code'          => '403',
    'title'         => 'Access Denied',
    'message'       => 'You don\'t have permission to view this page. Contact your administrator if you believe this is a mistake.',
    'accent'        => '#ef4444',
    'codeGradient'  => '#fca5a5',
    'glow'          => 'rgba(239,68,68,0.12)',
])
