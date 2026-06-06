<?php

namespace Tests\Unit;

use App\Models\Product;
use InvalidArgumentException;
use Tests\TestCase;

class ParseTireSizeTest extends TestCase
{
    public function test_parses_standard_size_string()
    {
        $result = Product::parseTireSize('225/45R17');

        $this->assertSame(['width' => 225, 'aspect_ratio' => 45, 'rim_diameter' => 17], $result);
    }

    public function test_throws_on_invalid_string()
    {
        $this->expectException(InvalidArgumentException::class);
        Product::parseTireSize('invalid-size');
    }

    public function test_accepts_components()
    {
        $result = Product::parseTireSize(null, 205, 55, 16);
        $this->assertSame(['width' => 205, 'aspect_ratio' => 55, 'rim_diameter' => 16], $result);
    }
}
