# Useful utils for laravel/lumen

### Install
```composer require cao-jiayuan/laravel-api[:dev-master]```
### Provider

* Laravel ```CaoJiayuan\LaravelApi\LaravelApiServiceProvider```
* Lumen ```CaoJiayuan\LaravelApi\LumenApiServiceProvider```

### Usage

#### 1.Exception render

* Trait : ```CaoJiayuan/LaravelApi/Foundation/Exceptions/Traits/ExceptionRenderer.php```
* Usage : 
```php
<?php

namespace App\Exceptions;
//......
use CaoJiayuan\LaravelApi\Foundation\Exceptions\Traits\ExceptionRenderer;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
//......

class Handler extends ExceptionHandler
{
    use ExceptionRenderer;
    //......
    public function render($request, Exception $exception)
    {
        return $this->renderException($request, $exception);
    }
}

```
#### 2.Build-in http server

Usage

```bash
php artisan api-util:server [start|restart|stop|status] [--port=8888] [--count=4] [--daemon=1]
```
You can write you APIs as usual. each time you change the code, you should run ```php artisan api-util:server restart```

#### 3.Build-in Websocket server

* Usage

```bash
php artisan api-util:ws [start|restart|stop|status] [--port=3000] [--count=4] [--daemon=1]
```

* Events

    1. Client connected ```CaoJiayuan\LaravelApi\WebSocket\Events\WebSocketConnected```
    2. Message received ```CaoJiayuan\LaravelApi\WebSocket\Events\WebSocketMessage```
    3. Client closed ```CaoJiayuan\LaravelApi\WebSocket\Events\WebSocketClosed```
    4. Worker started ```CaoJiayuan\LaravelApi\WebSocket\Events\WorkerStarted```

#### 4.Interact with Excel

* Extends ```CaoJiayuan\LaravelApi\Database\Eloquent\SheetModel``` .
* Excel example

|标题|内容|
|-|-|
|Foo|Some random string|

* Overwrite methods/properties

```php
<?php
namespace App\Models;

use CaoJiayuan\LaravelApi\Database\Eloquent\SheetModel;

class ExcelModel extends SheetModel {
    
    protected $excelHeaders = [
        'title' => '标题', // 'Table field' => 'Excel header' 
        'content' => '内容',
    ];
    
    public function getImportTemplateRow() // Import template rows
    {
        return [
            [
                'title'   => 'aa',
                'content' => 'test string',
            ],
            [
                'title'   => 'bb',
                'content' => 'other test string',
            ]
        ];
    }
    // ....
}

```

* Export
```php
<?php
$name = 'test.xlsx';
App\Models\ExcelModel::exportSheet($name);
App\Models\ExcelModel::where('title', 'foo')->exportSheet($name);

```

* Import
```php
<?php
$file = 'test.xlsx';
App\Models\ExcelModel::importSheet($file);

```


#### 5.Helpers

#####  RequestHelper
* Trait ```CaoJiayuan\LaravelApi\Http\Request\RequestHelper```
* Method ```getValidatedData(array $rules, array $messages = [], array $customAttributes = [])```
```php
<?php
namespace App\Http\Controllers;

use CaoJiayuan\LaravelApi\Http\Request\RequestHelper;

class FooController extends Controller
{
    use RequestHelper;
    //
    
    public function post(){
        $data = $this->getValidatedData([
            'name' => 'required', // Key => Rule,
            'foo', //Only key [default null]
            'bar' => ['required'/* or null*/, 1], // // Key => [Rule, default],
            'baz' => ['required'/* or null*/, function($value){
                return $value + 1;
            }] // Key => [Rule, resolver],
        ]);
    }
}

```

#### 6. Recommended

```workerman/workerman``` required to use server/ws command 

```maatwebsite/excel``` required to use excel model functional.
