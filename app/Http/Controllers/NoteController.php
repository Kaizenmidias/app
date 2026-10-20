<?php
namespace App\Http\Controllers;
use App\Models\{Lead,LeadNote};
use Illuminate\Http\Request;
class NoteController extends Controller
{
 public function store(Request $request,Lead $lead){abort_unless($lead->user_id===$request->user()->id,404);$d=$request->validate(['content'=>'required|string|max:10000']);return response()->json($lead->notes()->create(['user_id'=>$request->user()->id,'content'=>$d['content']]),201);}
 public function update(Request $request,LeadNote $note){abort_unless($note->user_id===$request->user()->id,404);$d=$request->validate(['content'=>'required|string|max:10000']);$note->update($d);return $note;}
 public function destroy(Request $request,LeadNote $note){abort_unless($note->user_id===$request->user()->id,404);$note->delete();return response()->noContent();}
}
