<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\SiteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\TelegramNotifier;

class LicenseController extends Controller
{
    public function index()
    {
        $licenses = License::latest()->paginate(10);
        return view('licenses.index', compact('licenses'));
    }

    public function update(Request $request, License $license)
    {
        $license->domain = $request->domain;
        $license->secret = $request->secret;
        $license->expired_at = $request->expired_at;
        $license->status = (int) $request->status;

        // Перегенеруємо хеш, якщо змінено домен або секрет
        $license->hash = hash('sha256', $license->domain . $license->secret);

        $license->save();

        return response()->json(['message' => 'Оновлено']);
    }

    public function create()
    {
        return view('licenses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|unique:licenses,domain',
        ]);

        $secret = Str::random(32);
        $hash = hash('sha256', $request->domain . $secret);

        License::create([
            'domain' => $request->domain,
            'secret' => $secret,
            'hash' => $hash,
            'status' => 1,
            'expired_at' => null,
        ]);

        return redirect()->route('licenses.index')->with('success', 'Сайт додано');
    }

    public function generateJs(License $license)
    {
        $template = file_get_contents(resource_path('js/wp-template.js'));

        $code = str_replace('{{secret}}', $license->secret, $template);

        $encoded = base64_encode($code);
        $obfuscated = "eval(atob('" . $encoded . "'))";

        $filename = 'license-' . $license->id . '.js';
        $fullPath = public_path('generated-js/' . $filename);

        // Створити папку, якщо не існує
        if (!file_exists(public_path('generated-js'))) {
            mkdir(public_path('generated-js'), 0755, true);
        }

        file_put_contents($fullPath, "<script>$obfuscated;</script>");

        return response()->json([
            'download_url' => asset('generated-js/' . $filename),
            'filename' => $filename,
        ]);
    }

    public function reactivate(License $license)
    {
        $license->branding_removed = 0;
        $license->status = 1;
        $license->expired_at = now()->addMonth();
        $license->save();

        return redirect()->back()->with('success', 'Сайт активовано');
    }

//

    public function check(Request $request)
    {
        $domain = $request->input('domain');
        $key = $request->input('key');
        $brandingRemoved = $request->boolean('branding_removed');

        $license = License::where('domain', $domain)->first();

        if (!$license) {
            return $this->reject($request, null, $domain, 1, 'not_found');
        }

        if (!$this->isValidKey($license, $key, $domain)) {
            return $this->reject($request, $license, $domain, 2, 'invalid');
        }

        if ($brandingRemoved && !$license->branding_removed) {
            return $this->handleBrandingViolation($request, $license);
        }

        if ($this->isExpiredOrInactive($license)) {
            return $this->reject($request, $license, $domain, 3, 'expired');
        }

        $this->logRequest($request, $license, $domain, 0);
        return response()->json(['status' => 'ok']);
    }


    private function isValidKey(License $license, string $providedKey, string $domain): bool
    {
        return hash_equals(hash('sha256', $domain . $license->secret), $providedKey);
    }

    private function isExpiredOrInactive(License $license): bool
    {
        return $license->status !== 1 || ($license->expired_at && now()->gt($license->expired_at));
    }

    private function handleBrandingViolation(Request $request, License $license)
    {
        $license->branding_removed = true;
        $license->status = 0;
        $license->save();

        TelegramNotifier::send(
            "🚨 *Порушення ліцензії!*\n" .
            "Домен: `{$license->domain}`\n" .
            "IP: `{$request->ip()}`\n" .
            "Причина: клієнт спробував прибрати брендинг"
        );

        $this->logRequest($request, $license, $license->domain, 4);
        return response()->json(['status' => 'blocked'], 403);
    }

    private function reject(Request $request, ?License $license, string $domain, int $statusCode, string $statusText)
    {
        $this->logRequest($request, $license, $domain, $statusCode);
        return response()->json(['status' => $statusText], 403);
    }

    private function logRequest(Request $request, ?License $license, string $domain, int $statusCode): void
    {
        SiteRequest::create([
            'license_id'  => $license?->id,
            'domain'      => $domain,
            'ip'          => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'success'     => $statusCode === 0,
            'status_code' => $statusCode,
        ]);
    }



}
