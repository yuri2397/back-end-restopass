<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAbilities
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if($user->abilitie == false){
            return response()->json([
                'error' => true,
                'message' => 'Vous n\'étes pas autorisé à faire cette request.'
            ], 400);
        }
        return $next($request);
    }
}
