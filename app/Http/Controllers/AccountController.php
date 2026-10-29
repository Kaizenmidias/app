<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class AccountController extends Controller {
 public function edit(Request $r){return view('account.edit',['user'=>$r->user()]);}
 public function update(Request $r){$u=$r->user();$d=$r->validate(['name'=>['required','string','max:255'],'email'=>['required','email','max:255',Rule::unique('users','email')->ignore($u->id)],'apify_api_token'=>['nullable','string','max:500']]);$changed=strcasecmp($u->email,$d['email'])!==0;DB::transaction(function()use($u,$d,$changed){$a=['name'=>$d['name'],'email'=>$d['email']];if($changed)$a['email_verified_at']=null;if(array_key_exists('apify_api_token',$d)&&filled($d['apify_api_token']))$a['apify_api_token']=$d['apify_api_token'];$u->forceFill($a)->save();});if($changed)$u->sendEmailVerificationNotification();return back()->with('status',$changed?'Dados atualizados. Confirme o novo e-mail para continuar.':'Dados atualizados.');}
 public function updatePassword(Request $r){$d=$r->validate(['current_password'=>['required','current_password'],'password'=>['required','confirmed',Password::defaults()]]);$r->user()->forceFill(['password'=>Hash::make($d['password'])])->save();return back()->with('status','Senha atualizada.');}
 public function updatePhoto(Request $r){$r->validate(['photo'=>['required','file','image','mimes:jpeg,jpg,png,webp','max:2048']]);$u=$r->user();$old=$u->profile_photo_path;$path=$r->file('photo')->storeAs('profile-photos/'.$u->id,Str::uuid().'.'.$r->file('photo')->extension(),'local');$u->forceFill(['profile_photo_path'=>$path])->save();if($old&&$old!==$path)Storage::disk('local')->delete($old);return back()->with('status','Foto de perfil atualizada.');}
 public function removePhoto(Request $r){$u=$r->user();if($u->profile_photo_path)Storage::disk('local')->delete($u->profile_photo_path);$u->forceFill(['profile_photo_path'=>null])->save();return back()->with('status','Foto de perfil removida.');}
 public function photo(Request $r){$path=$r->user()->profile_photo_path;abort_unless($path&&Storage::disk('local')->exists($path),404);return response()->file(Storage::disk('local')->path($path));}
}
