<?php

namespace App\Http\Middleware;

use App\Enums\Community;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCommunity
{
    public function handle(Request $request, Closure $next): Response
    {
        $community = Community::tryFrom((string) $request->route('community'));
        abort_if($community === null, 404);

        if ($user = $request->user()) {
            if ($user->community !== $community) {
                return redirect('/'.$user->community->value.'/dashboard');
            }
            if ($request->routeIs('community.login')) {
                return redirect('/'.$community->value.'/dashboard');
            }
        }

        $request->attributes->set('community', $community);

        return $next($request);
    }
}
