<?php

namespace Tests\Unit\Infrastructure\Notifications;

use App\Application\Notifications\Ports\ClientOutboundNotifier;
use App\Application\Notifications\Ports\NotificationDeliveryLogger;
use App\Infrastructure\Notifications\Client\LoggingClientOutboundNotifierDecorator;
use PHPUnit\Framework\TestCase;

final class LoggingClientOutboundNotifierDecoratorTest extends TestCase
{
    public function test_logs_sent_when_inner_notifier_succeeds(): void
    {
        $inner = $this->createMock(ClientOutboundNotifier::class);
        $inner->expects($this->once())
            ->method('sendRegistrationWelcome')
            ->with('user@example.com', 'Alex');

        $logger = $this->createMock(NotificationDeliveryLogger::class);
        $logger->expects($this->once())
            ->method('logSent')
            ->with('mail', 'registration_welcome', 'user@example.com', ['name' => 'Alex']);
        $logger->expects($this->never())->method('logFailed');

        $decorator = new LoggingClientOutboundNotifierDecorator($inner, 'mail', $logger);
        $decorator->sendRegistrationWelcome('user@example.com', 'Alex');
    }

    public function test_logs_failed_when_inner_notifier_throws(): void
    {
        $inner = $this->createMock(ClientOutboundNotifier::class);
        $inner->expects($this->once())
            ->method('sendPasswordResetLink')
            ->willThrowException(new \RuntimeException('SMTP down'));

        $logger = $this->createMock(NotificationDeliveryLogger::class);
        $logger->expects($this->once())
            ->method('logFailed')
            ->with(
                'telegram',
                'password_reset',
                'user@example.com',
                'SMTP down',
                ['has_token' => true],
            );
        $logger->expects($this->never())->method('logSent');

        $decorator = new LoggingClientOutboundNotifierDecorator($inner, 'telegram', $logger);
        $decorator->sendPasswordResetLink('user@example.com', 'secret-token');
    }

    public function test_does_not_log_plain_token_in_payload(): void
    {
        $inner = $this->createMock(ClientOutboundNotifier::class);
        $inner->method('sendPasswordResetLink');

        $logger = $this->createMock(NotificationDeliveryLogger::class);
        $logger->expects($this->once())
            ->method('logSent')
            ->with(
                'mail',
                'password_reset',
                'user@example.com',
                $this->callback(function (array $payload): bool {
                    return ($payload['has_token'] ?? false) === true
                        && ! array_key_exists('plain_token', $payload)
                        && ! in_array('top-secret', $payload, true);
                }),
            );

        $decorator = new LoggingClientOutboundNotifierDecorator($inner, 'mail', $logger);
        $decorator->sendPasswordResetLink('user@example.com', 'top-secret');
    }

    public function test_order_created_logs_order_id_only(): void
    {
        $inner = $this->createMock(ClientOutboundNotifier::class);
        $inner->method('sendOrderCreatedConfirmation');

        $logger = $this->createMock(NotificationDeliveryLogger::class);
        $logger->expects($this->once())
            ->method('logSent')
            ->with('sms', 'order_created', 'buyer@example.com', ['order_id' => 'ord-42']);

        $decorator = new LoggingClientOutboundNotifierDecorator($inner, 'sms', $logger);
        $decorator->sendOrderCreatedConfirmation('buyer@example.com', [
            'id' => 'ord-42',
            'total' => 9999,
        ]);
    }
}
