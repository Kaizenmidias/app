<?php
namespace App\Http\Controllers;
use App\Models\{Lead,LeadNote};
use Illuminate\Http\Request;
class NoteController extends Controller
{
 public function store(Request $request,Lead $lead){$this->authorize('view',$lead);$d=$request->validate(['content'=>'required|string|max:10000']);return response()->json($lead->notes()->create(['user_id'=>$request->user()->id,'content'=>$d['content']]),201);}
 public function update(Request $request,LeadNote $note){$this->authorize('update',$note);$d=$request->validate(['content'=>'required|string|max:10000']);$note->update($d);return $note;}
 public function destroy(Request $request,LeadNote $note){$this->authorize('delete',$note);$note->delete();return response()->noContent();}
}
