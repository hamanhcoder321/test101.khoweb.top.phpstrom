<?php

namespace App\AI\Providers;

use Illuminate\Support\ServiceProvider;

class ChatAIServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(
            base_path('app/AI/resources'),
            'AI'
        );

        // LOAD API ROUTES
        $apiRoutes = base_path('app/AI/routes/api.php');
        if (file_exists($apiRoutes)) {
            $this->loadRoutesFrom($apiRoutes);
        }

        // LOAD WEB ROUTES
        $webRoutes = base_path('app/AI/routes/web.php');
        if (file_exists($webRoutes)) {
            $this->loadRoutesFrom($webRoutes);
        }
    }

    public function register()
    {
        //
    }
}
