<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class V163CiRegressionContractTest extends TestCase
{
    public function test_flutter_store_uses_the_widget_controller(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/../flutter_app/lib/main.dart');
        $start = strpos($source, 'class _StorePageState');
        $end = strpos($source, 'class ', $start + 20);
        $store = substr($source, $start, $end === false ? null : $end - $start);

        $this->assertStringContainsString('widget.controller.level', $store);
        $this->assertStringNotContainsString('${controller.level}', $store);
    }

    public function test_mobile_client_sends_the_confirmation_flag(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/../flutter_app/lib/services/api_client.dart');
        $this->assertStringContainsString("'confirmation': true", $source);
    }
}
