<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Zenstruck\Browser\Test\HasBrowser;

abstract class WebTestCase extends BaseWebTestCase
{
    use HasBrowser;
}
