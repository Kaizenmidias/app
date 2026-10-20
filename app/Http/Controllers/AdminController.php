<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
class AdminController extends Controller
{
    public function dashboard() { return view('admin.dashboard',['users'=>User::count(),'active'=>User::where('status','active')->count(),'blocked'=>User::where('status','blocked')->count(),'leads'=>\App\Models\Lead::count(),'jobs'=>\App\Models\ProspectingJob::count()]); }
    public function users(Request $request) { $users=User::query()->when($request->q,fn($q,$v)=>$q->where(fn($x)=>$x->where('name','like',"%$v%")->orWhere('email','like',"%$v%")))->latest()->paginate(25); return view('admin.users',compact('users')); }
    public function create(){ return view('admin.user-form',['user'=>new User]); }
    public function store(Request $request){ $data=$request->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email','role'=>'required|in:partner,admin','status'=>'required|in:active,blocked']); $user=User::create([...$data,'password'=>Hash::make(str()->random(48))]); Password::sendResetLink(['email'=>$user->email]); AdminAuditLog::create(['admin_user_id'=>$request->user()->id,'action'=>'user.created','target_type'=>User::class,'target_id'=>$user->id]); return redirect()->route('admin.users')->with('status','Usuário criado e convite enviado.'); }
    public function edit(User $user){ return view('admin.user-form',compact('user')); }
    public function update(Request $request, User $user){ $data=$request->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255|unique:users,email,'.$user->id,'role'=>'required|in:partner,admin','status'=>'required|in:active,blocked']); if($user->isAdmin() && $user->status==='active' && ($data['role']!=='admin'||$data['status']!=='active') && User::where('role','admin')->where('status','active')->count()<=1) abort(403,'Mantenha um administrador ativo.'); $user->update($data); AdminAuditLog::create(['admin_user_id'=>$request->user()->id,'action'=>'user.updated','target_type'=>User::class,'target_id'=>$user->id]); return redirect()->route('admin.users'); }
    public function resendVerification(Request $request, User $user){ $user->sendEmailVerificationNotification(); AdminAuditLog::create(['admin_user_id'=>$request->user()->id,'action'=>'user.verification_resent','target_type'=>User::class,'target_id'=>$user->id]); return back(); }
    public function resetPassword(Request $request, User $user){ Password::sendResetLink(['email'=>$user->email]); AdminAuditLog::create(['admin_user_id'=>$request->user()->id,'action'=>'user.password_reset_sent','target_type'=>User::class,'target_id'=>$user->id]); return back(); }
    public function toggle(Request $request, User $user) {
        DB::transaction(function () use ($request, $user) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($locked->isAdmin() && $locked->status === 'active' && User::where('role','admin')->where('status','active')->lockForUpdate()->count() <= 1) {
                abort(403, 'Mantenha um administrador ativo.');
            }
            $newStatus = $locked->status === 'active' ? 'blocked' : 'active';
            $locked->update(['status'=>$newStatus]);
            AdminAuditLog::create(['admin_user_id'=>$request->user()->id,'action'=>'user.status_changed','target_type'=>User::class,'target_id'=>$locked->id,'metadata'=>['status'=>$newStatus]]);
        });
        return back();
    }
}
