<?php

namespace App\Http\Controllers\Api;

use App\Application\YandexFood\DTO\IssueAccessTokenDto;
use App\Application\YandexFood\Query\GetYandexFoodMenuAvailabilityUseCase;
use App\Application\YandexFood\Query\GetYandexFoodMenuCompositionUseCase;
use App\Application\YandexFood\Query\GetYandexFoodMenuPromosUseCase;
use App\Application\YandexFood\Query\GetYandexFoodRestaurantsUseCase;
use App\Application\YandexFood\Command\IssueYandexFoodAccessTokenUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class YandexFoodController extends Controller
{
    public function __construct(
        private readonly IssueYandexFoodAccessTokenUseCase $issueAccessToken,
        private readonly GetYandexFoodMenuCompositionUseCase $menuComposition,
        private readonly GetYandexFoodMenuAvailabilityUseCase $menuAvailability,
        private readonly GetYandexFoodMenuPromosUseCase $menuPromos,
        private readonly GetYandexFoodRestaurantsUseCase $restaurants,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');

        return response()->json(
            $this->issueAccessToken->execute(new IssueAccessTokenDto(
                clientId: is_string($clientId) ? $clientId : null,
                clientSecret: is_string($clientSecret) ? $clientSecret : null,
            )),
        );
    }

    public function getMenuComposition(string $id): JsonResponse
    {
        unset($id);

        return response()->json($this->menuComposition->execute());
    }

    public function getMenuAvailability(string $id): JsonResponse
    {
        unset($id);

        return response()->json($this->menuAvailability->execute());
    }

    public function getMenuPromos(string $id): JsonResponse
    {
        unset($id);

        return response()->json($this->menuPromos->execute());
    }

    public function getRestaurants(): JsonResponse
    {
        return response()->json($this->restaurants->execute());
    }
}
