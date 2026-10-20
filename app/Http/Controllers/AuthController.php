<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }
    public function login(Request $request) {
        $credentials = $request->validate(['email'=>['required','email'],'password'=>['required','string']]);
        $user = User::where('email',$credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'],$user->password) || !$user->isActive()) return back()->withErrors(['email'=>'As credenciais informadas são inválidas.'])->onlyInput('email');
        if (!$user->hasVerifiedEmail()) return back()->withErrors(['email'=>'Verifique seu e-mail antes de entrar.']);
        Auth::login($user,$request->boolean('remember')); $request->session()->regenerate();
        $user->forceFill(['last_login_at'=>now()])->save(); return redirect()->intended('/dashboard');
    }
    public function showRegister() { abort_unless(config('kaizen.registration_enabled'),404); return view('auth.register'); }
    public function register(Request $request) {
        abort_unless(config('kaizen.registration_enabled'),404);
        $data=$request->validate(['name'=>['required','string','max:255'],'email'=>['required','email','max:255','unique:users,email'],'password'=>['required','confirmed',Password::defaults()],'terms'=>['accepted']]);
        $user=User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>$data['password'],'role'=>'partner','status'=>'active']); $user->sendEmailVerificationNotification();
        return redirect()->route('login')->with('status','Confira seu e-mail para verificar a conta.');
    }
    public function logout(Request $request) { Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login'); }
}
