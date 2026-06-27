<?php

namespace Tests\Unit\Order;

use App\Application\Order\Support\OrderDeliveryCommentComposer;
use App\Shared\Enum\PaymentMethod;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OrderDeliveryCommentComposerTest extends TestCase
{
    #[Test]
    public function compose_добавляет_сдачу_к_комментарию_доставки(): void
    {
        $comment = OrderDeliveryCommentComposer::compose(
            deliveryComment: 'Без имбиря',
            paymentMethod: PaymentMethod::Cash,
            changeFromRubles: 2000,
        );

        $this->assertSame('Без имбиря. Сдача с 2000 ₽', $comment);
    }

    #[Test]
    public function compose_создаёт_комментарий_только_из_сдачи(): void
    {
        $comment = OrderDeliveryCommentComposer::compose(
            deliveryComment: null,
            paymentMethod: PaymentMethod::Cash,
            changeFromRubles: 5000,
        );

        $this->assertSame('Сдача с 5000 ₽', $comment);
    }

    #[Test]
    public function compose_игнорирует_сдачу_для_безналичной_оплаты(): void
    {
        $comment = OrderDeliveryCommentComposer::compose(
            deliveryComment: 'Позвонить',
            paymentMethod: PaymentMethod::CardCourier,
            changeFromRubles: 2000,
        );

        $this->assertSame('Позвонить', $comment);
    }

    #[Test]
    public function compose_игнорирует_нулевую_или_пустую_сдачу(): void
    {
        $this->assertSame(
            'Позвонить',
            OrderDeliveryCommentComposer::compose(
                deliveryComment: 'Позвонить',
                paymentMethod: PaymentMethod::Cash,
                changeFromRubles: 0,
            ),
        );

        $this->assertNull(
            OrderDeliveryCommentComposer::compose(
                deliveryComment: null,
                paymentMethod: PaymentMethod::Cash,
                changeFromRubles: null,
            ),
        );
    }
}
