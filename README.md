# Useful utils for laravel/lumen

### Install
```composer require cao-jiayuan/laravel-api[:dev-master]```
### Providers

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
#### 2.Eloquent pipeline query
* Trait ```CaoJiayuan\LaravelApi\Database\Eloquent\Helpers\Pipelineable```

* Usage
```php
<?php
namespace App;

use CaoJiayuan\LaravelApi\Database\Eloquent\Helpers\Pipelineable;
use Illuminate\Database\Eloquent\Model;

class Foo extends Model {
    
    use Pipelineable;
    
    public function relate() {
        return $this->belongsTo(App\Bar::class);
    }
}

\App\Foo::pipeline('with:relate|select:id,name');//equals \App\Foo::with('relate')->select(['id','name'])->get();
\App\Foo::join('baz', 'foo_id', '=', 'baz_id')
          ->pipeline('with:relate|select:id,name,baz.title as baz|get|pluck:name|random');

```

#### 3.Dummy data generator
Generate test data with giving template (inspired by [Mock.js](https://github.com/nuysoft/Mock)), working with [fzaninotto/Faker](https://github.com/fzaninotto/Faker)

Usage: 
```
template = [
    'key|rule1[:params][|rule2[:params]]' [=> 'value']
]
dummy(template) array|mixed
dummy_pager(total, template [,page=1] [,perPage=25]) LengthAwarePaginator
```
example:
```php
<?php

dummy([
   'id' => 1,
   'name|name',
   'address|address',
   'data|list:2' => [
        'id|1+1',   
        'name|name',   
    ]
]);
```
results:
```json
{
    "id":1,
    "name":"Mrs. Bulah Hilll MD",
    "address":"445 Hudson Isle\nJunehaven, NM 74631",
    "data":[
        {"id":1,"name":"Sophie Kris"},
        {"id":2,"name":"Cooper Thompson"}
    ]
}
```


rules:
* ```list[:size=20]``` generate array of data with giving template as value, alias ```l:size```
* ```increase[:step=1,base=1]``` generate number auto-increase ```step``` from ```base``` in list template, alias ```base+step```
* ```date[:format=Y-m-d H:i:s,now=time()]``` generate datetime string with format
* ```randDate[:start,end='now',format='Y-m-d H:i:s']``` generate random datetime string between ```start``` and ```end```
* ```rand[:min,max,value=null]``` generate random value, eg:

    ```php
     <?php
      dummy('rand|1,100');// return random number between 1 and 100
      dummy('rand|true,false');
      dummy([
        'data|list:10|rand:1,5' => [// working with pipeline, generate 10 items, randomly take 1-5 item[s]
             'id|1+1',   
            'name|name',
         ]
      ]);
    ```
* ```pick[:num=1]``` pick ```num``` of item from value, if ```num``` = 1, return single item
* ```from``` return data from giving value
* ```db[:table,limit=null]``` get result from database with giving value as callback

    ```php
     <?php
      dummy([
        'data1|db:users' => function(\Illuminate\Database\Query\Builder $builder) {
              /// database query
            $builder->select(['id', 'name']);
            /// ......
         },
         'data2|db:articles,2', /// randomly take 2 rows from table articles 
         'data3|db:connection1.articles,2', /// specified connection
         'data4|db:articles,10|pick:2' /// working with pipeline
      ]);
    ```

#### 4.Helpers

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
        ], ['name.required' => '......'], ['foo' => '-_-!!!']);
    }
}

```
