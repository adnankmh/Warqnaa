<?php

namespace Tests\Feature;

use Tests\TestCase;

class V136SeederCountryFixTest extends TestCase
{
    public function test_database_seeder_does_not_use_undefined_country_helper_variable(): void
    {
        $seeder=file_get_contents(database_path('seeders/DatabaseSeeder.php'));
        $this->assertStringNotContainsString('$countryNameTextV135', $seeder);
        $this->assertStringContainsString('country_name($country)', $seeder);
    }

    public function test_country_name_returns_scalar_string(): void
    {
        $this->assertIsString(country_name('JO'));
        $this->assertSame('الأردن', country_name('JO'));
    }
}
