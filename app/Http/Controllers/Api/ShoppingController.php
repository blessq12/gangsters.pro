<?php

namespace App\Http\Controllers\Api;

use App\Application\Shopping\Command\ClearCartUseCase;
use App\Application\Shopping\Command\LogoutShoppingSessionUseCase;
use App\Application\Shopping\Command\MergeGuestShoppingUseCase;
use App\Application\Shopping\Command\MigrateLocalShoppingStateUseCase;
use App\Application\Shopping\Command\PatchCheckoutDraftUseCase;
use App\Application\Shopping\Command\RecalculateShoppingCartUseCase;
use App\Application\Shopping\Command\RemoveCartLineUseCase;
use App\Application\Shopping\Command\RemoveFavoriteUseCase;
use App\Application\Shopping\Command\ToggleFavoriteUseCase;
use App\Application\Shopping\Command\UpsertCartLineUseCase;
use App\Application\Shopping\Query\GetShoppingStateUseCase;
use App\Domain\Shopping\Entities\ShoppingSession;
use App\Http\Middleware\EnsureShoppingSession;
use App\Http\Requests\Shopping\MigrateLocalShoppingRequest;
use App\Http\Requests\Shopping\PatchCheckoutDraftRequest;
use App\Http\Requests\Shopping\UpsertCartLineRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ShoppingController extends Controller
{
    private function session(Request $request): ShoppingSession
    {
        $s = $request->attributes->get(EnsureShoppingSession::ATTRIBUTE_KEY);
        if (! $s instanceof ShoppingSession) {
            throw new \RuntimeException('Shopping session missing from request.');
        }

        return $s;
    }

    public function state(Request $request, GetShoppingStateUseCase $useCase): JsonResponse
    {
        return response()->json(['data' => $useCase->execute($this->session($request))]);
    }

    public function upsertCartLine(
        UpsertCartLineRequest $request,
        UpsertCartLineUseCase $useCase,
    ): JsonResponse {
        $v = $request->validated();

        $data = $useCase->execute(
            $this->session($request),
            (int) $v['product_id'],
            (int) $v['quantity'],
            isset($v['payload']) && is_array($v['payload']) ? $v['payload'] : null,
        );

        return response()->json(['data' => $data]);
    }

    public function removeCartLine(Request $request, int $productId, RemoveCartLineUseCase $useCase): JsonResponse
    {
        $data = $useCase->execute($this->session($request), $productId);

        return response()->json(['data' => $data]);
    }

    public function clearCart(Request $request, ClearCartUseCase $useCase): JsonResponse
    {
        $data = $useCase->execute($this->session($request));

        return response()->json(['data' => $data]);
    }

    public function recalculate(Request $request, RecalculateShoppingCartUseCase $useCase): JsonResponse
    {
        $data = $useCase->execute($this->session($request));

        return response()->json(['data' => $data]);
    }

    public function toggleFavorite(Request $request, int $productId, ToggleFavoriteUseCase $useCase): JsonResponse
    {
        $data = $useCase->execute($this->session($request), $productId);

        return response()->json(['data' => $data]);
    }

    public function removeFavorite(Request $request, int $productId, RemoveFavoriteUseCase $useCase): JsonResponse
    {
        $data = $useCase->execute($this->session($request), $productId);

        return response()->json(['data' => $data]);
    }

    public function patchCheckoutDraft(
        PatchCheckoutDraftRequest $request,
        PatchCheckoutDraftUseCase $useCase,
    ): JsonResponse {
        $draft = $this->normalizeCheckoutDraft($request->validated());
        $data = $useCase->execute($this->session($request), $draft);

        return response()->json(['data' => $data]);
    }

    public function merge(Request $request, MergeGuestShoppingUseCase $useCase): JsonResponse
    {
        $user = $request->user('sanctum');
        $clientId = is_object($user) && isset($user->id) ? (int) $user->id : 0;
        $result = $useCase->execute($this->session($request), $clientId);

        return response()->json([
            'data' => $result['state'],
            'meta' => ['cookie_public_id' => $result['cookie_public_id']],
        ]);
    }

    public function migrate(
        MigrateLocalShoppingRequest $request,
        MigrateLocalShoppingStateUseCase $useCase,
    ): JsonResponse {
        $data = $useCase->execute($this->session($request), $request->validated());

        return response()->json(['data' => $data]);
    }

    public function logout(Request $request, LogoutShoppingSessionUseCase $useCase): JsonResponse
    {
        $useCase->execute($this->session($request));

        return response()->json(['data' => ['ok' => true]]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function normalizeCheckoutDraft(array $validated): array
    {
        $out = [];
        if (isset($validated['delivery_info'])) {
            $out['delivery_info'] = $validated['delivery_info'];
        }
        if (isset($validated['payment_info'])) {
            $out['payment_info'] = $validated['payment_info'];
        }
        if (isset($validated['guest_contact'])) {
            $out['guest_contact'] = $validated['guest_contact'];
        }
        if (array_key_exists('customer_comment', $validated)) {
            $out['customer_comment'] = $validated['customer_comment'];
        }
        if (isset($validated['promotions']) && is_array($validated['promotions'])) {
            $out['promotions'] = $validated['promotions'];
        }

        return $out;
    }
}
