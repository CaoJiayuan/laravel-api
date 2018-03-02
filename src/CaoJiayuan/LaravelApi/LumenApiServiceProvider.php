<?php
namespace CaoJiayuan\LaravelApi;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use CaoJiayuan\LaravelApi\Http\Server\LumenServerCommand;
use Illuminate\Support\ServiceProvider;
use Mnabialek\LaravelSqlLogger\Providers\ServiceProvider as SqlLogServiceProvider;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Tymon\JWTAuth\Providers\LumenServiceProvider;
use CaoJiayuan\LaravelApi\WebSocket\ServerCommand as WsServerCommand;

class LumenApiServiceProvider extends ServiceProvider
{
    protected $configs = ['laravel-api'];


    public function register()
    {
        $this->mergeConfig();
        $app = $this->app;
        if (is_local()) {
            $app->register(IdeHelperServiceProvider::class);
            $app->register(SqlLogServiceProvider::class);
        }
        $app->register(LumenServiceProvider::class);
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
            /** @var Logger $writer */
            $writer = $this->app['log'];
            $writer->popHandler();
            $writer->setHandlers([$this->getCliMonologHandler()]);
        }
    }

    protected function getCliMonologHandler()
    {
        return (new StreamHandler(storage_path('logs/lumen-cli.log'), Logger::DEBUG))
            ->setFormatter(new LineFormatter(null, null, true, true));
    }

    protected function mergeConfig()
    {
        foreach ($this->configs as $key) {
            $this->mergeConfigFrom(__DIR__.'/../../config/'. $key. '.php', $key);
        }
    }

    protected function registerCommands()
    {
        $this->registerServerCommend();
        $this->registerWsCommend();
        $this->commands(['command.laravel-api.server']);
        $this->commands(['command.laravel-api.ws']);
    }

    protected function registerServerCommend()
    {
        $this->app->singleton('command.laravel-api.server', function ($app) {
            return new LumenServerCommand();
        });
    }

    protected function registerWsCommend()
    {
        $this->app->singleton('command.laravel-api.ws', function ($app) {
            return new WsServerCommand();
        });
    }
}
