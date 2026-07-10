<?php

namespace Tests\Feature;

use Tests\TestCase;

class V135CountryNameSeedFixTest extends TestCase
{
    public function test_country_name_returns_string_not_array(): void
    {
        $this->assertIsString(country_name('JO'));
        $this->assertSame('الأردن', country_name('JO'));
        $this->assertIsString(country_name('PS'));
    }

    public function test_register_view_handles_array_country_config(): void
    {
        $view=file_get_contents(resource_path('views/auth/register.blade.php'));
        $this->assertStringContainsString('is_array($name)', $view);
    }
}
