<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class SettingsController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $qrCodeSvg = null;

        $google2fa = new Google2FA();

        // Generate secret if it doesn't exist
        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        if ($user->two_factor_enabled) {
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                'MSC',          // Issuer
                $user->email,   // Account
                $user->two_factor_secret
            );

            // Generate SVG QR code
            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);
            $qrCodeSvg = $writer->writeString($qrCodeUrl);
        }

        return view('users.settings', compact('user', 'qrCodeSvg'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email',
            'rfid_uid'   => 'nullable|string',
        ]);

        $user->first_name = $request->first_name;
        $user->last_name  = $request->last_name;
        $user->email      = $request->email;
        $user->rfid_uid   = $request->rfid_uid;

        // Enable/disable 2FA
        $user->two_factor_enabled = $request->has('enable_2fa');

        $user->save();

        return redirect()->route('users.settings')->with('success', 'Einstellungen erfolgreich gespeichert!');
    }
}
