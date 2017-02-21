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
        $this->_paramHandler->bindMethods(\Famework\LaCodon\Param\Paramhandler::POST);
    }

    protected function getBearer() {
        $authHeader = getallheaders();

        if (isset($authHeader['Auth'])) {
            $bearer = $authHeader['Auth'];
            if (strpos($bearer, 'Bearer ') === 0) {
                return str_replace('Bearer ', '', $bearer);
            }
        }

        header('HTTP/1.1 401 Unauthorized');
        exit(1);
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
                exit(1);
            } else {
                return array(
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                );
            }
        }
    }

    protected function throwExceptionOnTooLongPath($maxLength) {
        if ($this->_pathPartCount > $maxLength) {
            throw new \Exception('API Endpoint not found', \Errorcode::API_ENDPOINT_NOT_FOUND);
        }
    }

    protected function hasToBePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new \Exception('POST request expected.', \Errorcode::API_POST_REQUIRED);
        }
    }

    public abstract function pathToAttr($pathParts);

    protected abstract function executeCall();
}
