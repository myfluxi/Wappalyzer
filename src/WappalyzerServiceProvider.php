<?php

namespace MadeITBelgium\Wappalyzer;

use Illuminate\Support\ServiceProvider;

/**
 * MadeITBelgium Wappalyzer PHP Library.
 *
 * @version    1.0.0
 *
 * @copyright  Copyright (c) 2018 Made I.T. (https://www.madeit.be)
 * @author     Tjebbe Lievens <tjebbe.lievens@madeit.be>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-3.txt    LGPL
 */
class WappalyzerServiceProvider extends ServiceProvider
{
    protected $defer = false;

    protected $rules = [
        
    ];

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/wappalyzer.php' => config_path('wappalyzer.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('wappalyzer', function ($app) {
            $config = $app->make('config')->get('wappalyzer');
            
            $app = __DIR__ . '/apps.json';
            if(isset($config['data_file']) && $config['data_file'] !== null) {
                $app = $config['data_file'];
            }
            
            return new Wappalyzer($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['wappalyzer'];
    }
}
