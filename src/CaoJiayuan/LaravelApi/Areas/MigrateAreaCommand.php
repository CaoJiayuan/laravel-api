<?php
/**
 * Created by PhpStorm.
 * User: caojiayuan
 * Date: 2018/7/28
 * Time: 下午3:46
 */

namespace CaoJiayuan\LaravelApi\Areas;


use CaoJiayuan\LaravelApi\Areas\Entity\Area;
use CaoJiayuan\LaravelApi\Areas\Entity\City;
use CaoJiayuan\LaravelApi\Areas\Entity\Province;
use Illuminate\Console\Command;
use League\Flysystem\FilesystemNotFoundException;

class MigrateAreaCommand extends Command
{
    protected $signature = 'api-util:areas {file=data/cities.php : Data file relative to storage path} {--force : Force migrate}';

    protected $description = 'Migrate china areas data (preset data file and migration file in [vendor/cao-jiayuan/laravel-api/src/misc/areas/])';


    public function handle()
    {
        $file = storage_path($this->argument('file'));
        if (!file_exists($file)) {
            throw new FilesystemNotFoundException(sprintf('data file not found [%s]', $file));
        }

        $location = require $file;

        if (!$this->option('force') && Area::count() < 1) {
            $this->warn('Areas data exists, if you want migrate anyway, use [--force] option');
            return;
        }

        DB::table('cities')->truncate();
        DB::table('provinces')->truncate();
        DB::table('areas')->truncate();

        Model::unguard();
        DB::transaction(function () use ($location) {
            foreach ($location as $item) {
                $province = $item['province_name'];
                $p = Province::create(['name' => $province]);
                $this->info("Province [{$province}]");
                if (isset($item['city'])) {
                    foreach ((array)$item['city'] as $c) {
                        $cityName = $c['city_name'];
                        $this->info("    >> City [{$cityName}]");
                        $city = City::create(['province_id' => $p['id'], 'name' => $cityName]);

                        foreach (array_get($c, 'area', []) as $area) {
                            $this->info("       >> Area [{$area}]");
                            Area::create([
                                'city_id' => $city->id,
                                'name'    => $area,
                            ]);
                        }
                    }
                }
            }
        });
        Model::reguard();

        $this->info('Areas migrate success!');
    }
}