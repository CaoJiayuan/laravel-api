<?php
/**
 * Created by PhpStorm.
 * User: caojiayuan
 * Date: 17-10-31
 * Time: 上午9:40
 */

namespace CaoJiayuan\LaravelApi\Foundation\Exceptions\Traits;

use CaoJiayuan\LaravelApi\Foundation\Exceptions\CustomHttpException;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

trait ExceptionRenderer
{

    /**
     * @param Request $request
     * @param Exception $exception
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function renderException($request, Exception $exception)
    {
        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }
        $code = 500;
        if ($exception instanceof HttpExceptionInterface) {
            $code = $exception->getStatusCode();
        }

        $baseName = class_basename($exception);

        $methodName = 'handle' . $baseName;
        if (method_exists($this, $methodName)) {
            $response = call_user_func_array([$this, $methodName], [$exception, $request]);
            if ($response instanceof Response) {
                return $response;
            }
            if ($response instanceof Exception) {
                $exception = $response;
            }
        }

        $message = $exception->getMessage();
        if ($request->expectsJson()) {
            return $this->toJsonExceptionResponse($exception);
        }
        if ($code == 200) {
            return response($message);
        }
        return parent::render($request, $exception);
    }

    /**
     * @param Exception $exception
     * @return array
     */
    public function parseTrace($exception)
    {
        $t = $exception->getTrace();

        $trace = [];

        foreach ($t as $i => $item) {
            $file = array_get($item, 'file', '[internal function]');
            $file = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
            $line = array_get($item, 'line');

            $class = array_get($item, 'class');
            $func = array_get($item, 'function');
            $type = array_get($item, 'type');
            $line = $line ? '(' . $line . ')' : '';
            $args = array_get($item, 'args', []);

            $ar = '';
            foreach ($args as $arg) {
                $a = $arg;

                if (is_object($a)) $a = get_class($a);

                if (is_array($a)) {
                    $a = '[]';
                } else if (is_string($a)) {
                    $a = "'" . $a . "'";
                } else if (is_bool($a)) {
                    $a = $a ? 'true' : 'false';
                }

                $ar .= $a . ', ';
            }


            $ar = rtrim($ar, ', ');

            $trace[] = '[#' . $i . '] ' . $file . $line . ': ' . $class . $type . $func . '(' . $ar . ')';
        }

        return $trace;
    }

    /**
     * @param ModelNotFoundException $exception
     * @param Request $request
     * @return Exception|\Illuminate\Http\JsonResponse
     */
    public function handleModelNotFoundException(ModelNotFoundException $exception, $request)
    {
        $model = $exception->getModel();
        $message = $exception->getMessage();

        if (defined($model .'::DISPLAY_NAME')) {
            $displayName = $model::DISPLAY_NAME;

            $message = trans('errors.modelNotFound', [
                'model' => $displayName
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'code'    => 404,
                'errors'  => [],
                'model'   => class_basename($model),
                'message' => $message
            ], 404);
        }

        return new HttpException(404, $message, $exception);
    }

    /**
     * @param Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function toJsonExceptionResponse(Exception $exception)
    {
        $code = 500;
        $errors = [];
        if ($exception instanceof HttpExceptionInterface) {
            $code = $exception->getStatusCode();
        }

        $message = $exception->getMessage();
        if ($exception instanceof ValidationException) {
            $code = 422;
            $errors = $exception->validator->getMessageBag();
            $message = $errors->first();
        }
        $respondCode = $code;

        if ($exception instanceof CustomHttpException) {
            $respondCode = $exception->getCustomCode();
            $errors = $exception->getErrorData();
            $message = $exception->getMessage();
        }

        $debug = env('APP_DEBUG');

        if (!$debug && $code >= 500) {
            $message = 'Server error';
        }

        $data = ['code' => $respondCode, 'errors' => $errors, 'message' => $message];


        if ($debug && $code >= 500) {
            $file = $exception->getFile();
            $file = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
            $line = $exception->getLine();
            $data['exception'] = class_basename($exception);
            $data['file'] = "$file. Line:[$line]";
            $data['trace'] = $this->parseTrace($exception);
        }


        return response()->json($this->exceptionDataResolver($data, $code, $exception), $code);
    }

    protected function exceptionDataResolver($data, $code, $exception)
    {
        return $data;
    }
}
