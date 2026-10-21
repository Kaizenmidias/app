<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class EnsureFrontendCsrf { public function handle(Request $request, Closure $next): Response { if(in_array($request->method(),['POST','PUT','PATCH','DELETE'],true)){ $expected=(string)$request->session()->token(); $tokens=array_filter([$request->header('X-XSRF-TOKEN'),$request->header('X-CSRF-TOKEN'),$request->input('_token'),$request->cookie('XSRF-TOKEN')]); $valid=false; foreach($tokens as $token){ if(hash_equals($expected,urldecode((string)$token))){$valid=true;break;} } if(!$valid) abort(419,'CSRF token mismatch.'); } return $next($request); } }
