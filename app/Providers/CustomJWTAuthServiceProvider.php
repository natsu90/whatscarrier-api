<?php

namespace App\Providers;

use Tymon\JWTAuth\Providers\JWTAuthServiceProvider;

/**
 * All we do here is forcing the JWTAuthServiceProvider to take
 * our custom config as the only config (force Lumen compatibility,
 * basically).
 */
class CustomJWTAuthServiceProvider extends JWTAuthServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->app['config']->set('jwt', require realpath(__DIR__ . '/../../config/jwt.php'));

        $this->bootBindings();

        $this->commands('tymon.jwt.generate');
    }
}