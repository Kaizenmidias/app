<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class ModulePlaceholderController extends Controller {
 public function __invoke(Request $r,string $module){$m=$this->modules();abort_unless(isset($m[$module]),404);return view('modules.placeholder',['module'=>$m[$module]]);}
 private function modules():array{return ['chats'=>['title'=>'Chats','description'=>'Conversas integradas estarão disponíveis aqui.'],'numeros-conectados'=>['title'=>'Números conectados','description'=>'A gestão de números conectados está em desenvolvimento.'],'disparar'=>['title'=>'Disparar','description'=>'Este módulo está em desenvolvimento.'],'email-marketing'=>['title'=>'E-mail Marketing','description'=>'Este módulo está em desenvolvimento.'],'automacoes'=>['title'=>'Automações','description'=>'Este módulo está em desenvolvimento.'],'agenda'=>['title'=>'Agenda','description'=>'Este módulo está em desenvolvimento.'],'briefings'=>['title'=>'Briefings','description'=>'Este módulo está em desenvolvimento.']];}
}
