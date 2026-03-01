<?php
namespace App\Library\JWT\Providers;

use Illuminate\Support\ServiceProvider;
use App\Library\JWT\JWTAuth;

class JWTAuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('jwt.auth', function($app) {
            return new JWTAuth();
        });
    }
}
