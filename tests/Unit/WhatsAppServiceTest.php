<?php

namespace Tests\Unit;

use App\Services\WhatsAppService;
use PHPUnit\Framework\TestCase;

class WhatsAppServiceTest extends TestCase
{
    private WhatsAppService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WhatsAppService();
    }

    public function test_is_disabled_without_config(): void
    {
        $this->assertFalse($this->service->isEnabled());
    }

    public function test_normalizes_colombian_phone(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('normalizePhone');
        $method->setAccessible(true);

        $this->assertEquals('573001234567', $method->invoke($this->service, '3001234567'));
        $this->assertEquals('573001234567', $method->invoke($this->service, '+57 300 123 4567'));
        $this->assertEquals('573001234567', $method->invoke($this->service, '573001234567'));
    }

    public function test_send_message_returns_disabled_when_not_configured(): void
    {
        $result = $this->service->sendMessage('3001234567', 'test_template');

        $this->assertFalse($result['sent']);
        $this->assertEquals('disabled', $result['reason']);
    }

    public function test_send_text_message_returns_disabled_when_not_configured(): void
    {
        $result = $this->service->sendTextMessage('3001234567', 'Hola');

        $this->assertFalse($result['sent']);
    }

    public function test_shipment_notification_methods_return_disabled(): void
    {
        $created = $this->service->sendShipmentCreated('3001234567', 'ABC123', 'Bogota', 'https://example.com');
        $this->assertFalse($created['sent']);

        $transit = $this->service->sendShipmentInTransit('3001234567', 'ABC123', 'Medellin');
        $this->assertFalse($transit['sent']);

        $delivered = $this->service->sendShipmentDelivered('3001234567', 'ABC123');
        $this->assertFalse($delivered['sent']);
    }
}