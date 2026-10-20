<?php
namespace App\Http\Controllers;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
class VerificationController extends Controller
{
    public function notice() { return view('auth.verify'); }
    public function verify(EmailVerificationRequest $request) { if ($request->user()->markEmailAsVerified()) event(new Verified($request->user())); return redirect('/dashboard'); }
    public function send(\Illuminate\Http\Request $request) { if ($request->user()->hasVerifiedEmail()) return redirect('/dashboard'); $request->user()->sendEmailVerificationNotification(); return back()->with('status','Link de verificação reenviado.'); }
}
