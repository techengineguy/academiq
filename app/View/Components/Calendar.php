<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use TallStackUi\Components\Floating\Component as Floating;

class Calendar extends \TallStackUi\Components\Calendar\Component
{
    /**
     * Create a new component instance.
     */
     public function customization(): array
    {
        return Arr::dot([
            'wrapper' => [
                'outer' => 'inline-flex flex-col gap-2',
                'body' => 'relative p-3 rounded-lg bg-white dark:bg-zinc-800 shadow-md',
                'single' => 'w-[17rem]',
                'dual' => 'grid grid-cols-1 sm:grid-cols-2 gap-4',
                'helpers' => 'custom-scrollbar flex items-center justify-between space-x-2 overflow-auto pb-2',
            ],
            'floating' => [
                'default' => collect(app(Floating::class)->customization())->get('wrapper'),
                'class' => 'p-3 w-[17rem] h-[17rem]',
            ],
            'box' => [
                'picker' => [
                    'button' => 'text-gray-900 focus:ring-dark-200 flex items-center justify-between rounded-lg px-2 py-1 mb-6 text-sm font-semibold focus:outline-hidden focus:ring-2 dark:text-white',
                    'wrapper' => [
                        'second' => 'flex w-full flex-wrap h-full',
                        'third' => 'flex h-10 w-full items-center justify-between px-1 mb-2',
                    ],
                    'label' => 'text-gray-900 dark:bg-dark-700 hover:bg-dark-100 dark:hover:bg-dark-600 focus:ring-dark-200 flex cursor-pointer items-center justify-between rounded-lg bg-white px-2 py-1 text-sm font-semibold focus:outline-hidden focus:ring-0 dark:text-white',
                    'range' => 'text-gray-400 dark:text-dark-400 font-medium hover:bg-dark-100 dark:hover:bg-dark-600 text-gray-600 dark:text-dark-400 disabled:text-gray-400 dark:disabled:text-dark-500 flex h-6 w-1/4 cursor-pointer select-none items-center justify-center rounded-md p-1 text-center font-normal disabled:cursor-not-allowed',
                    'separator' => 'mx-1',
                    'today' => 'text-gray-500 dark:text-dark-300 bg-dark-200 dark:bg-dark-600 hover:bg-dark-300 dark:hover:bg-dark-500 select-none whitespace-nowrap rounded-md px-2 py-1 text-xs font-medium cursor-pointer',
                    'navigate-wrapper' => 'flex items-center gap-0.5',
                ],
            ],
            'label' => [
                'days' => 'text-gray-400 dark:text-dark-400 select-none text-center text-xs font-medium',
                'month' => 'text-gray-800 dark:text-dark-100 cursor-pointer select-none text-lg font-bold',
                'year' => 'text-gray-600 dark:text-dark-400 ml-1 cursor-pointer select-none text-lg font-normal',
                'locked' => 'cursor-default pointer-events-none',
            ],
            'button' => [
                'blank' => 'border border-transparent p-1 text-center text-sm',
                'day' => 'focus:shadow-outline disabled:text-gray-400 dark:disabled:text-dark-500 dark:active:bg-primary-500 ring-primary-500 active:bg-primary-600 flex h-7 w-7 items-center justify-center rounded-full text-center text-sm leading-none outline-hidden transition-all duration-200 ease-in-out hover:shadow-sm active:text-white disabled:cursor-not-allowed cursor-pointer',
                'select' => 'text-gray-600 dark:text-dark-400 hover:bg-dark-200 dark:hover:bg-dark-600',
                'today' => 'text-primary-500 dark:text-dark-300! font-bold!',
                'selected' => 'bg-primary-500 text-white! hover:bg-primary-500/75',
                'helpers' => 'text-gray-500 dark:text-dark-300 bg-dark-200 dark:bg-dark-600 hover:bg-dark-300 dark:hover:bg-dark-500 select-none whitespace-nowrap rounded-md px-2 py-1 text-sm font-medium',
                'navigate' => 'focus:shadow-outline hover:bg-dark-100 dark:hover:bg-dark-600 inline-flex cursor-pointer rounded-full p-1 transition duration-100 ease-in-out focus:outline-hidden',
            ],
            'icon' => [
                'navigate' => 'text-gray-600 dark:text-dark-300 h-5 w-5',
            ],
            'range' => 'bg-dark-200 dark:bg-dark-600',
        ]);
    }

}
