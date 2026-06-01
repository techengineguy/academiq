@extends('errors.layout', [
    'code'          => '429',
    'title'         => 'Too Many Requests',
    'message'       => 'You\'ve sent too many requests in a short time. Take a breath and try again in a moment.',
    'accent'        => '#f97316',
    'codeGradient'  => '#fdba74',
    'glow'          => 'rgba(249,115,22,0.12)',
])
