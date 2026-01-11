<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    // Show 2FA challenge form
    public function challenge()
    {
        $user = Auth::user();

        if (!$user || !$user->two_factor_enabled) {
            request()->session()->put('2fa_passed', true);
            return redirect()->route('users.dashboard');
        }

        // Ensure a secret exists (first-time setup)
        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $this->google2fa->generateSecretKey();
            $user->save();
        }

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            'MSC',
            $user->email,
            $user->two_factor_secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('auth.2fa', compact('qrCodeSvg'));
    }

    // Verify submitted 2FA code
    public function verify(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required|digits:6',
        ]);

        $user = Auth::user();

        if (!$user || !$user->two_factor_enabled) {
            $request->session()->put('2fa_passed', true);
            return redirect()->intended('/dashboard');
        }

        if (!$user->two_factor_secret) {
            return redirect()->route('2fa.challenge');
        }

        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->one_time_password);

        if ($valid) {
            $request->session()->put('2fa_passed', true);
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['one_time_password' => 'Ungültiger 2FA Code']);
    }

    public function setup()
    {
        $user = Auth::user();
        $google2fa = new Google2FA();

        // Generate a secret if not already set
        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        // Create the otpauth URL
        $google2fa_url = $google2fa->getQRCodeUrl(
            'MSC',
            $user->email,
            $user->two_factor_secret
        );

        // Generate QR code as SVG
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($google2fa_url);

        return view('users.2fa.setup', compact('qrCodeSvg', 'user'));
    }

    public function enable(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required|digits:6',
        ]);

        $user = Auth::user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->one_time_password);

        if ($valid) {
            $user->two_factor_enabled = true;
            $user->save();

            // OTP was verified during enable, so consider this session verified.
            $request->session()->put('2fa_passed', true);

            return redirect()->route('users.dashboard')
                ->with('success', '2FA erfolgreich aktiviert!');
        }

        return back()->withErrors(['one_time_password' => 'Ungültiger Code']);
    }
}
