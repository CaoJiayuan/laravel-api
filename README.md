# Useful utils for laravel/lumen

### Install
```composer require cao-jiayuan/laravel-api[:dev-master]```
### Usage

#### 1.Exception render

* Trait : ```CaoJiayuan/LaravelApi/Foundation/Exceptions/Traits/ExceptionRenderer.php```
* Usage : 
```php
namespace App\Exceptions;
......
use CaoJiayuan\LaravelApi\Foundation\Exceptions\Traits\ExceptionRenderer;
......

class Handler extends ExceptionHandler
{
    use ExceptionRenderer;
    ......
    public function render($request, Exception $exception)
    {
        return $this->renderException($request, $exception);
    }
}

```
