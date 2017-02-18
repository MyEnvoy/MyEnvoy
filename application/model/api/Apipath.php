<?php

namespace Api;

abstract class Apipath {

    /**
     * @var \Famework\LaCodon\Param\Paramhandler
     */
    protected $_paramHandler;
    private $_pathPartCount = 0;

    public function __construct() {
        $this->_paramHandler = new \Famework\LaCodon\Param\Paramhandler();
    }

    public function handlePath($apiPath) {
        $pathParts = explode('/', $apiPath);
        foreach ($pathParts as &$part) {
            $part = strtolower($part);
        }
        $this->_pathPartCount = count($pathParts);
        $this->pathToAttr($pathParts);

        try {
            return $this->executeCall();
        } catch (\Exception $e) {
            if ($e->getCode() === \Errorcode::API_ENDPOINT_NOT_FOUND) {
                header('HTTP/1.0 404 Not Found');
                exit();
            } else {
                throw $e;
            }
        }
    }

    protected function throwExceptionOnTooLongPath($maxLength) {
        if ($this->_pathPartCount > $maxLength) {
            throw new \Exception('API Endpoint not found', \Errorcode::API_ENDPOINT_NOT_FOUND);
        }
    }

    public abstract function pathToAttr($pathParts);

    protected abstract function executeCall();
}
