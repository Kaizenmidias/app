<?php
namespace App\Http\Controllers;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
class PasswordController extends Controller
{
    public function requestForm(){return view('auth.forgot-password');}
    public function email(Request $request){$request->validate(['email'=>'required|email']); $status=Password::sendResetLink($request->only('email')); return back()->with('status',__($status));}
    public function resetForm(Request $request,string $token){return view('auth.reset-password',['token'=>$token,'email'=>$request->email]);}
    public function reset(Request $request){$data=$request->validate(['token'=>'required','email'=>'required|email','password'=>['required','confirmed',PasswordRule::defaults()]]); $status=Password::reset($data,function($user,$password){$user->forceFill(['password'=>Hash::make($password),'remember_token'=>Str::random(60)])->save(); event(new PasswordReset($user));}); return $status===Password::PASSWORD_RESET ? redirect()->route('login')->with('status','Senha redefinida com sucesso.') : back()->withErrors(['email'=>__($status)]);}
}
