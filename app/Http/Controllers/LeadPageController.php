<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class LeadPageController extends Controller { public function __invoke(Request $request) { $query=$request->user()->leads()->with('job')->latest(); $term=trim((string)$request->query('q')); if($term){$query->where(fn($b)=>$b->where('name','like',"%{$term}%")->orWhere('phone','like',"%{$term}%")->orWhere('address','like',"%{$term}%"));} return view('leads.index',['leads'=>$query->paginate(20)->withQueryString(),'term'=>$term]); } }
