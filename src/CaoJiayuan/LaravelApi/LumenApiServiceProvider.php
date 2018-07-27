<?php
namespace CaoJiayuan\LaravelApi;

use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use CaoJiayuan\LaravelApi\Http\Server\LumenServerCommand;
use Mnabialek\LaravelSqlLogger\Providers\ServiceProvider as SqlLogServiceProvider;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Tymon\JWTAuth\Providers\LumenServiceProvider;

class LumenApiServiceProvider extends LaravelApiServiceProvider
{
    protected $configs = ['api-util'];


    public function register()
    {
        $this->mergeConfig();
        $app = $this->app;
        if (is_local()) {
            $this->registerIfExists(IdeHelperServiceProvider::class);
            $this->registerIfExists(SqlLogServiceProvider::class);
        }
        $app->register(LumenServiceProvider::class);
        $this->setLogWriter();
        if (PHP_SAPI == 'cli') {
            $this->registerCommands();
        }
        $this->registerSignature();
    }

    public function publish()
    {

    }

    protected function setLogWriter()
    {
        if (!config('api-util.separate_log_file')) {
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
            $this->app->configure($key);
            $this->mergeConfigFrom(__DIR__.'/../../config/'. $key. '.php', $key);
        }
    }

    protected function registerServerCommend()
    {
        $this->app->singleton('command.laravel-api.server', function ($app) {
            return new LumenServerCommand();
        });
    }
}
