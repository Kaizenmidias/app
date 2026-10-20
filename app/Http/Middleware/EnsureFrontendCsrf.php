<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class EnsureFrontendCsrf { public function handle(Request $request, Closure $next): Response { if(in_array($request->method(),['POST','PUT','PATCH','DELETE'],true)){ $token=$request->header('X-XSRF-TOKEN') ?: $request->input('_token'); if(!$token || !hash_equals((string)$request->session()->token(), urldecode($token))) abort(419,'CSRF token mismatch.'); } return $next($request); } }
