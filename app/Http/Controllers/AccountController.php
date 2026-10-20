<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
class AccountController extends Controller {
 public function edit(Request $r){return view('account.edit',['user'=>$r->user()]);}
 public function update(Request $r){$u=$r->user();$d=$r->validate(['name'=>['required','string','max:255'],'email'=>['required','email','max:255',Rule::unique('users','email')->ignore($u->id)]]);$changed=strcasecmp($u->email,$d['email'])!==0;DB::transaction(function()use($u,$d,$changed){$a=['name'=>$d['name'],'email'=>$d['email']];if($changed)$a['email_verified_at']=null;$u->forceFill($a)->save();});if($changed)$u->sendEmailVerificationNotification();return back()->with('status',$changed?'Dados atualizados. Confirme o novo e-mail para continuar.':'Dados atualizados.');}
 public function updatePassword(Request $r){$d=$r->validate(['current_password'=>['required','current_password'],'password'=>['required','confirmed',Password::defaults()]]);$r->user()->forceFill(['password'=>Hash::make($d['password'])])->save();return back()->with('status','Senha atualizada.');}
}
