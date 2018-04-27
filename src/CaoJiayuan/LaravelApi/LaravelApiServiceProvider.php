<?php
/**
 * Created by PhpStorm.
 * User: 0x01301c74
 * Date: 2017/8/20
 * Time: 19:10
 */

namespace CaoJiayuan\LaravelApi;


use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use CaoJiayuan\LaravelApi\Http\Proxy\HttpProxyCommand;
use CaoJiayuan\LaravelApi\Http\Server\ServerCommand;
use CaoJiayuan\LaravelApi\WebSocket\ServerCommand as WsServerCommand;
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
            $configs   => $configPath
        ]);
    }

    public function register()
    {
        $this->mergeConfig();
        if (config('app.env') == 'local') {
            $this->app->register(IdeHelperServiceProvider::class);
            $this->app->register(SqlLoggerServiceProvider::class);
        }

        $this->app->register(LaravelServiceProvider::class);
        $this->setLogWriter();
        if (PHP_SAPI == 'cli') {
            $this->registerCommands();
        }
    }

    protected function setLogWriter()
    {
        if (!config('laravel-api.separate_log_file')) {
            return;
        }

        if (PHP_SAPI == 'cli') {
            if ($this->app->version() >= 5.6) {
                /** @var \Illuminate\Config\Repository $config */
                $config = $this->app['config'];
                $config->set('logging.channels.cli-log', $this->getCliLogChannel());
                $config->set('logging.default', 'cli-log');
                return;
            } else {
                /** @var Writer $writer */
                $writer = $this->app['log'];
                $writer->getMonolog()->popHandler();
                $writer->useFiles(storage_path('logs/laravel-cli.log'));
            }
        }
    }

    protected function getCliLogChannel()
    {
        return [
            'driver' => 'single',
            'path'   => storage_path('logs/laravel-cli.log'),
            'level'  => 'debug',
        ];
    }

    protected function mergeConfig()
    {
        foreach ($this->configs as $key) {
            $this->mergeConfigFrom(__DIR__ . '/../../config/' . $key . '.php', $key);
        }
    }

    protected function registerCommands()
    {
        $this->registerServerCommend();
        $this->registerProxyCommend();
        $this->registerWsCommend();
        $this->commands(['command.laravel-api.server']);
        $this->commands(['command.laravel-api.proxy']);
        $this->commands(['command.laravel-api.ws']);
    }

    protected function registerServerCommend()
    {
        $this->app->singleton('command.laravel-api.server', function ($app) {
            return new ServerCommand();
        });
    }

    protected function registerProxyCommend()
    {
        $this->app->singleton('command.laravel-api.proxy', function ($app) {
            return new HttpProxyCommand();
        });
    }

    protected function registerWsCommend()
    {
        $this->app->singleton('command.laravel-api.ws', function ($app) {
            return new WsServerCommand();
        });
    }
}
