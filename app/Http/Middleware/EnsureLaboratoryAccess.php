<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
final class EnsureLaboratoryAccess {
 public function handle(Request $request, Closure $next): Response {
  $u=$request->user();
  abort_unless($u && method_exists($u,'hasRole') && $u->hasRole('laborant') && $u->laboratory_clinic_id,403);
  return $next($request);
 }
}
