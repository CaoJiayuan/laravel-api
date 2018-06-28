<?php

use CaoJiayuan\LaravelApi\Html\Document;
use CaoJiayuan\LaravelApi\Html\Documents;
use CaoJiayuan\LaravelApi\Html\LazyLoadDocument;
use CaoJiayuan\LaravelApi\Html\LazyLoadDocuments;
use CaoJiayuan\LaravelApi\Promise\Promise;
use Illuminate\Support\Debug\HtmlDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;

if (!function_exists('is_local')) {
    function is_local()
    {
        return env('APP_ENV') == 'local';
    }
}

if (!function_exists('array_remove_empty')) {
    function array_remove_empty($array, $remove = ['', null])
    {
        $removed = [];
        foreach ((array)$array as $key => $item) {
            if (!in_array($item, $remove)) {
                $removed[$key] = $item;
            }
        }
        return $removed;
    }
}


if (!function_exists('object_to_array')) {
    function object_to_array($object, &$result)
    {
        $data = $object;
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $res = null;
                object_to_array($value, $res);
                if (($key == '@attributes') && ($key)) {
                    $result = $res;
                } else {
                    $result[$key] = $res;
                }
            }
        } else {
            $result = $data;
        }
    }
}
if (!function_exists('xml_to_array')) {
    function xml_to_array($xml)
    {
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
}

if (!function_exists('file_map')) {
    /**
     * @param string|array $file
     * @param callable $closure
     * @param bool $recursive
     */
    function file_map($file, callable $closure, $recursive = true)
    {
        foreach ((array)$file as $fe) {
            if (is_dir($fe)) {
                $items = new FilesystemIterator($fe);
                /** @var SplFileInfo $item */
                foreach ($items as $item) {
                    if ($item->isDir() && !$item->isLink() && $recursive) {
                        $closure($item->getPathname(), $item, true);
                        file_map($item->getPathname(), $closure);
                    } else {
                        $closure($item->getPathname(), $item, $item->isDir());
                    }
                }
            } else {
                $f = new SplFileInfo($fe);
                $closure($fe, $f, false);
            }
        }
    }
}

if (!function_exists('array_find')) {
    function array_find($array, $findChain, $default = null)
    {
        if (!is_array($findChain)) {
            return array_get($array, $findChain, $default);
        }
        foreach ($findChain as $key) {
            if (array_key_exists($key, $array)) {
                return $array[$key];
            }
        }

        return $default;
    }
}

if (!function_exists('promise')) {
    /**
     * @param callable $promising
     * @param array $params
     * @return Promise
     */
    function promise($promising, $params = [])
    {
        return Promise::resolve($promising, $params);
    }
}


if (!function_exists('html_dump')) {
    function html_dump(...$args)
    {
        ob_start();
        foreach ($args as $x) {
            (new HtmlDumper())->dump((new VarCloner())->cloneVar($x));
        }
        return ob_get_clean();
    }
}


if (!function_exists('serial_number')) {
    function serial_number($num, $length = 4, $prepend = '0')
    {
        return sprintf("%'{$prepend}{$length}d", $num);
    }
}


if (!function_exists('document')) {
    /**
     * @param null $doc
     * @return LazyLoadDocument
     */
    function document($doc)
    {
        $document = new LazyLoadDocument($doc);

        return $document;
    }
}

if (!function_exists('documents')) {
    /**
     * @param $loads
     * @param array $configs
     * @param array $options
     * @return Documents|Document[]|LazyLoadDocuments
     */
    function documents($loads, $configs = [], $options = [])
    {
        $docs = new LazyLoadDocuments($loads);
        $docs->config($configs)->onLoad($options);

        return $docs;
    }
}
