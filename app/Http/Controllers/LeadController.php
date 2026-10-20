<?php
namespace App\Http\Controllers;
use App\Models\Lead;
use Illuminate\Http\Request;
class LeadController extends Controller
{
    public function index(Request $request) { return $request->user()->leads()->latest()->paginate(25); }
    public function show(Request $request, Lead $lead) { abort_unless($lead->user_id===$request->user()->id,404); return $lead->load('notes'); }
    public function update(Request $request, Lead $lead) { abort_unless($lead->user_id===$request->user()->id,404); $data=$request->validate(['name'=>'sometimes|string|max:255','phone'=>'nullable|string|max:255','email'=>'nullable|email','website'=>'nullable|string|max:2048']); $lead->update($data); return $lead; }
    public function destroy(Request $request, Lead $lead) { abort_unless($lead->user_id===$request->user()->id,404); $lead->delete(); return response()->noContent(); }
}
