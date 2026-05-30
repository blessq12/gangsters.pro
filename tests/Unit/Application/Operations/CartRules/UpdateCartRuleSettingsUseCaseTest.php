<?php

namespace Tests\Unit\Application\Operations\CartRules;

use App\Application\Operations\CartRules\Command\UpdateCartRuleSettingsUseCase;
use App\Application\Operations\CartRules\Contracts\CartRuleSettingsRepository;
use App\Application\Operations\CartRules\DTO\CartRuleSettingsDTO;
use App\Application\Operations\CartRules\Query\GetAdminCartRuleSettingsQuery;
use Tests\TestCase;

final class UpdateCartRuleSettingsUseCaseTest extends TestCase
{
    public function test_execute_saves_and_returns_settings(): void
    {
        $saved = false;
        $dto = new CartRuleSettingsDTO(
            complementRuleEnabled: true,
            giftRuleEnabled: false,
            giftThresholdKopecks: 500000,
            rollsPerComplement: 3,
            complementRuleSort: 10,
            giftRuleSort: 20,
        );

        $repo = new class($dto, $saved) implements CartRuleSettingsRepository {
            public function __construct(
                private CartRuleSettingsDTO $dto,
                private bool &$saved,
            ) {}

            public function get(): CartRuleSettingsDTO
            {
                return $this->dto;
            }

            public function save(CartRuleSettingsDTO $settings): void
            {
                $this->dto = $settings;
                $this->saved = true;
            }
        };

        $useCase = new UpdateCartRuleSettingsUseCase($repo, new GetAdminCartRuleSettingsQuery($repo));
        $result = $useCase->execute($dto);

        $this->assertTrue($saved);
        $this->assertTrue($result['complement_rule_enabled']);
        $this->assertSame(5000.0, $result['gift_threshold_rubles']);
    }
}
