<?php
/**
 * Created by PhpStorm.
 * User: 0x01301c74
 * Date: 2017/8/20
 * Time: 19:10
 */

namespace CaoJiayuan\LaravelApi;


use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Log\Writer;
use Illuminate\Support\ServiceProvider;
use Mnabialek\LaravelSqlLogger\Providers\ServiceProvider as SqlLoggerServiceProvider;
use Tymon\JWTAuth\Providers\LaravelServiceProvider;

class LaravelApiServiceProvider extends ServiceProvider
{

    protected $configs = ['laravel-api'];

    public function boot()
    {
        $this->publish();
    }

    public function publish()
    {
        $resources = __DIR__ . '/../../resources';

        $resourcePath = resource_path();

        $configs = __DIR__ . '/../../config';

        $configPath = config_path();

        $this->publishes([
            $resources => $resourcePath,
            $configs => $configPath
        ]);
    }

    public function register()
    {
        $this->mergeConfig();
        $this->app->register(IdeHelperServiceProvider::class);
        $this->app->register(SqlLoggerServiceProvider::class);
        $this->app->register(LaravelServiceProvider::class);
        $this->setLogWriter();
    }

    public function setLogWriter()
    {
        if (!config('laravel-api.separate_log_file')) {
            return;
        }
        /** @var Writer $writer */
        $writer = $this->app['log'];
        if (PHP_SAPI == 'cli') {
            $writer->getMonolog()->popHandler();
            $writer->useFiles(storage_path('logs/laravel-cli.log'));
        }
    }

    public function mergeConfig()
    {
        foreach ($this->configs as $key) {
            $this->mergeConfigFrom(__DIR__.'/../../config/'. $key. '.php', $key);
        }
    }
}
