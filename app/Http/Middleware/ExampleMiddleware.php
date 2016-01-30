<?php namespace App\Http\Middleware;

use Closure;

class ExampleMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->header('x-requested-with') !== 'my.com.derp.whatscarrier')
            throw new Exception("Error Processing Request", 404);
            
        return $next($request);
    }

}
