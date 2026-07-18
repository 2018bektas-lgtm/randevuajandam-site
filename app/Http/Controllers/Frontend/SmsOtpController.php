<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Rules\TurkishMobilePhone;
use App\Services\PhoneOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SmsOtpController extends Controller
{
    public function gonder(Request $request, PhoneOtpService $otp): JsonResponse
    {
        $data = $request->validate([
            'telefon' => ['required', 'string', new TurkishMobilePhone],
            'purpose' => ['required', 'in:randevu,kayit'],
            'doktor_id' => ['nullable', 'integer', 'exists:doktorlar,id'],
        ], [
            'telefon.required' => 'Telefon zorunludur.',
            'purpose.required' => 'İşlem türü zorunludur.',
        ]);

        $phone = TurkishMobilePhone::normalize($data['telefon']);
        $purpose = $data['purpose'];
        $doktorId = $purpose === 'randevu' ? (int) ($data['doktor_id'] ?? 0) : null;

        if ($purpose === 'randevu' && empty($doktorId)) {
            return response()->json([
                'success' => false,
                'message' => 'Hekim bilgisi eksik.',
            ], 422);
        }

        try {
            $otp->send($phone, (string) $request->ip(), $purpose, $doktorId ?: null);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Doğrulama kodu SMS ile gönderildi.',
            'telefon' => $phone,
            'expires_in' => 300,
        ]);
    }

    public function dogrula(Request $request, PhoneOtpService $otp): JsonResponse
    {
        $data = $request->validate([
            'telefon' => ['required', 'string', new TurkishMobilePhone],
            'kod' => ['required', 'string', 'size:6'],
            'purpose' => ['required', 'in:randevu,kayit'],
            'doktor_id' => ['nullable', 'integer', 'exists:doktorlar,id'],
        ], [
            'kod.required' => 'Doğrulama kodu zorunludur.',
            'kod.size' => 'Doğrulama kodu 6 haneli olmalıdır.',
        ]);

        $phone = TurkishMobilePhone::normalize($data['telefon']);
        $purpose = $data['purpose'];
        $doktorId = $purpose === 'randevu' ? (int) ($data['doktor_id'] ?? 0) : null;

        try {
            $otp->verify($phone, $data['kod'], $purpose, $doktorId ?: null);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Telefon doğrulandı.',
            'telefon' => $phone,
        ]);
    }
}
