<?php

namespace App\Http\Controllers\Front;

use App\Enums\LegalDocumentType;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\WorkShedule;
use App\Repositories\LegalDocumentRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFacade;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MainController extends Controller
{
    public function __construct(
        private LegalDocumentRepository $legalDocuments
    ) {
        ViewFacade::share('company', Company::first());
        ViewFacade::share('currentDayShedule', WorkShedule::getCurrentDayShedule());
    }

    public function index()
    {
        return view('front.index', [
            'banner' => \App\Models\Banner::latest()->first(),
        ]);
    }

    public function vacancy()
    {
        return view('front.vacancy');
    }

    public function about()
    {
        return view('front.about');
    }

    public function contact()
    {
        $company = Company::first();
        $raw = $company->legals()->first();
        $legals = $this->mapPublicLegals($raw?->toArray() ?? []);

        return view('front.contact', ['legals' => $legals]);
    }

    public function purchaseAndDelivery(): View
    {
        return view('front.purchaseAndDelivery');
    }

    public function privacy(): View
    {
        return $this->legalDocument(LegalDocumentType::PRIVACY);
    }

    public function offer(): View
    {
        return $this->legalDocument(LegalDocumentType::OFFER);
    }

    public function terms(): View
    {
        return $this->legalDocument(LegalDocumentType::TERMS);
    }

    public function pdnConsent(): View
    {
        return $this->legalDocument(LegalDocumentType::PDN_CONSENT);
    }

    public function cookies(): View
    {
        return $this->legalDocument(LegalDocumentType::COOKIES);
    }

    public function seller(): View
    {
        $document = $this->legalDocuments->findCurrent(LegalDocumentType::SELLER_INFO);
        if (!$document) {
            throw new NotFoundHttpException();
        }

        $company = Company::first();
        $raw = $company?->legals()->first();
        $legals = $this->mapPublicLegals($raw?->toArray() ?? []);

        return view('front.seller', [
            'document' => $document,
            'legals' => $legals,
        ]);
    }

    private function legalDocument(string $type): View
    {
        $document = $this->legalDocuments->findCurrent($type);
        if (!$document) {
            throw new NotFoundHttpException();
        }

        return view('front.legal-document', ['document' => $document]);
    }

    /**
     * @param array<string, mixed> $legals
     * @return array<string, string>
     */
    private function mapPublicLegals(array $legals): array
    {
        if ($legals === []) {
            return [];
        }

        $mapped = [
            'Форма собственности' => (string) ($legals['legal_form'] ?? ''),
            'Директор' => (string) ($legals['owner'] ?? ''),
            'ИНН' => (string) ($legals['inn'] ?? ''),
            'ОГРН' => (string) ($legals['ogrn'] ?? ''),
            'ОКПО' => (string) ($legals['okpo'] ?? ''),
            'Адрес регистрации' => (string) ($legals['registration_address'] ?? ''),
            'Электронная почта' => (string) ($legals['legal_email'] ?? ''),
        ];

        $isIp = ($legals['legal_form'] ?? '') === 'ИП';
        if (!$isIp && !empty($legals['kpp'])) {
            $mapped['КПП'] = (string) $legals['kpp'];
        }

        return array_filter($mapped, static fn ($value) => $value !== '');
    }

    public function loyalty(): View
    {
        return view('front.loyalty');
    }

    public function resetPassword(Request $request)
    {
        if (User::where('token_to_reset_password', $request->token)->exists()) {
            return view('front.reset-password', ['token' => $request->token]);
        }

        return redirect()->route('main.index')->with('error', 'Ссылка для сброса пароля недействительна');
    }

    public function attachCategoriesToProducts()
    {
        $products = \App\Models\Product::all();
        foreach ($products as $product) {
            $product->categories()->attach($product->product_category_id);
        }
    }
}
