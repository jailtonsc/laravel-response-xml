<?php

namespace XmlResponse;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Response;
use XmlResponse\Exception\XmlResponseException;
use Illuminate\Container\Container;

/**
 * Class XmlResponse
 * @package XmlResponse
 */
class XmlResponse
{
    /**
     * @var
     */
    private $caseSensitive;

    /**
     * @var
     */
    private $template;

    /**
     * @var
     */
    private $showEmptyField;

    /**
     * @var
     */
    private $charset;

    /**
     * @var
     */
    private $rowName;

    /**
     * @var boolean
     */
    private $asXml = false;

    /**
     * XmlResponse constructor.
     */
    public function __construct()
    {
        $app = $this->app();

        $this->caseSensitive = $app->get('xml.caseSensitive');
        $this->template = $app->get('xml.template');
        $this->showEmptyField = $app->get('xml.showEmptyField');
        $this->charset = $app->get('xml.charset');
        $this->rowName = $app->get('xml.rowName');
    }

    /**
     * @return mixed
     */
    public function app()
    {
        $container = new Container();
        return $container->getInstance()->make('config');
    }

    /**
     * @return mixed
     */
    private function header()
    {
        return [
            'Content-Type' => $this->charset()
        ];
    }

    /**
     * @param $array
     * @return string
     */
    private function charset($header = [])
    {
        $charset = 'application/xml; ';

        if (!empty($this->charset)) {
            $charset .= "charset={$this->charset}";
        }

        return $charset;
    }

    /**
     * add encoding
     */
    private function encodingXml()
    {
        if (!empty($this->charset) && strpos($this->template, 'encoding') === false) {
            $this->template = "<?xml version=\"1.0\" encoding=\"{$this->charset}\"?>{$this->template}";
        }
    }

    /**
     * @param $value
     * @return bool
     */
    private function isType($value)
    {
        return in_array($value, [
            'model',
            'collection',
            'array',
            'object'
        ]);
    }

    /**
     * @param $value
     * @return mixed
     */
    private function caseSensitive($value)
    {
        if ($this->caseSensitive) {
            $value = explode('_', $value);
            $value = lcfirst(join('', array_map("ucfirst", $value)));
        }
        return $value;
    }

    private function rowName($row) 
    {
        if (!empty($this->rowName)) {
            return $this->rowName;
        }
        return 'row_' . $row;
    }

    /**
     * @param $value
     * @return bool
     */
    private function isConfig($value)
    {
        return in_array($value, [
            'template',
            'caseSensitive',
            'showEmptyField',
            'charset',
            'rowName'
        ]);
    }

    /**
     * replaces the current setting
     * 
     * @param array $config
     * @return bool
     */
    private function config($config = []) 
    {
        foreach ($config as $key => $value) {
            if ($this->isConfig($key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @param $array
     * @param bool $xml
     * @param array $headerAttribute
     * @return mixed
     * @throws XmlResponseException
     */
    public function array2xml($array, $xml = false, $config = [], $status = 200)
    {
        if (is_object($array) && $array instanceof Arrayable) {
            $array = $array->toArray();
        }

        if (!$this->isType(gettype($array))) {
            throw new XmlResponseException('It is not possible to convert the data');
        }

        $this->config($config);

        if ($xml === false) {
            $this->encodingXml();
            $xml = new \SimpleXMLElement($this->template);
        }

        foreach ($array as $key => $value) {

            if (is_array($value)) {
                if (is_numeric($key)) {
                    $this->array2xml($value, $xml->addChild($this->caseSensitive($this->rowName($key))));
                } else {
                    $this->array2xml($value, $xml->addChild($this->caseSensitive($key)));
                }
            } elseif (is_object($value)) {
                $this->array2xml($value, $xml->addChild($this->caseSensitive((new \ReflectionClass(get_class($value)))->getShortName())));
            } else {
                if (!is_null($value) || $this->showEmptyField) {
                    if (is_numeric($key)) {
                        $xml->addChild($this->caseSensitive($this->rowName($key)), htmlspecialchars($value));
                    } else {
                        $xml->addChild($this->caseSensitive($key), htmlspecialchars($value));
                    }
                }
            }
        }

        if ($this->asXml) {
            return $xml->asXML();
        }
        return Response::make($xml->asXML(), $status, $this->header());
    }

    /**
     * @param $array
     * @return string 
     * @throws XmlResponseException
     */
    public function asXml($array = [], $config = [])
    {
        $this->asXml = true;
        return $this->array2xml($array, false, $config);
    }
}
