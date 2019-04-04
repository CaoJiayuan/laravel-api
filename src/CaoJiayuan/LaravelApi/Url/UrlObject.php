<?php namespace CaoJiayuan\LaravelApi\Url;

/**
 * @author caojiayuan
 */
class UrlObject
{
    protected $components;

    protected $queries = [];

    public function __construct($url)
    {
        $this->components = parse_url($url);
        $this->parseQueries();
    }

    public function query($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                if (!is_numeric($k)) {
                    $this->queries[$k] = $v;
                }
            }
        } else if (!is_null($value)) {
            $this->queries[$key] = $value;
        }

        return $this;
    }

    protected function parseQueries()
    {
        if (array_key_exists('query', $this->components)) {
            $queries = explode('&', $this->components['query']);
        } else {
            $queries = [];
        }

        $this->queries = [];
        foreach ($queries as $query) {
            list($key, $value) = explode('=', $query);
            $this->queries[$key] = $value;
        }
    }

    public function toString()
    {
        return sprintf('%s://%s%s?%s', $this->components['scheme'],
            $this->components['host'],
            $this->components['path'], http_build_query($this->queries)
        );
    }

    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return array
     */
    public function getQueries()
    {
        return $this->queries;
    }

    public function getPath()
    {
        return $this->components['path'];
    }

    public function getHost()
    {
        return $this->components['host'];
    }

    public function getScheme()
    {
        return $this->components['scheme'];
    }
}