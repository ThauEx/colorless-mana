<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StringExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('linkify', [$this, 'linkify'], [
                'pre_escape' => 'html',
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function linkify($str): array|string|null
    {
        return preg_replace('/\[([^\[]+)]\((.+?)\)/', '<a href="$2">$1</a>', $str);
    }
}
