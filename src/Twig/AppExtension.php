<?php

namespace App\Twig;

use App\Entity\LikeNotification;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigTest;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @var string
     */
    private $locale;
    /**
     * @var string
     */
    private $hello_message;

    public function __construct(string $locale, string $hello_message)
    {
        $this->locale = $locale;
        $this->hello_message = $hello_message;
    }


    public function getGlobals(): array
    {
        return [
            'locale' => $this->locale,
            'hello_message' => $this->hello_message
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('price', [$this, 'priceFilter'])
        ];
    }

    public function priceFilter($number)
    {
        return '$' . number_format($number, 2, '.', ',');
    }

    public function getTests()
    {
        return [
            new TwigTest('like', function ($obj) {
                return $obj instanceof LikeNotification;
            })
        ];
    }


}