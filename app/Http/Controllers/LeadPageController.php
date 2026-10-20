<?php
namespace App\Http\Controllers;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class LeadPageController extends Controller {
 public function __invoke(Request $r){$q=$r->user()->leads()->with('job')->latest();$term=trim((string)$r->query('q'));if($term)$q->where(fn($b)=>$b->where('name','like',"%{$term}%")->orWhere('phone','like',"%{$term}%")->orWhere('address','like',"%{$term}%"));return view('leads.index',['leads'=>$q->paginate(20)->withQueryString(),'term'=>$term]);}
 public function store(Request $r){$d=$r->validate(['name'=>['required','string','max:255'],'company'=>['nullable','string','max:255'],'phone'=>['nullable','string','max:255'],'email'=>['nullable','email','max:255'],'website'=>['nullable','url','max:2048'],'observations'=>['nullable','string','max:10000']]);$uid=$r->user()->id;$dup=Lead::where('user_id',$uid)->where('source','manual')->where('name',$d['name'])->when($d['email']??null,fn($q,$e)=>$q->where('email',$e),fn($q)=>$q->whereNull('email'))->exists();if($dup)return back()->withInput()->withErrors(['name'=>'Este lead manual já existe na sua conta.']);DB::transaction(fn()=>Lead::create(['user_id'=>$uid,'name'=>$d['name'],'phone'=>$d['phone']??null,'email'=>$d['email']??null,'website'=>$d['website']??null,'source'=>'manual','raw_data'=>array_filter(['manual_company'=>$d['company']??null,'manual_observations'=>$d['observations']??null],fn($v)=>$v!==null&&$v!== '')]));return back()->with('status','Lead cadastrado.');}
}
