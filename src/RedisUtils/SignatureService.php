<?php

namespace Mtkh\IdempotencyHandler\RedisUtils;


use Illuminate\Http\Request;
use InvalidArgumentException;

class SignatureService implements IdempotencyConfigInterface
{
    const DELIMITER = '.';

    private $hashMethod;

    private $signatureStructure = [];

    private $prefix;

    /**
     * Create custom signature based on user config for the current request.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function createSignatureRequest(Request $request)
    {
        $body = $this->prepareRequestBody($request);
        $headers = $this->prepareRequestHeaders($request);
        $server = $this->prepareServerParams($request);
        $params = $this->prepareParams();
        $structure = implode(self::DELIMITER, array_merge($body, $headers, $server, $params));
        return $this->prefix . $this->getHashMethod()($structure);
    }

    protected function prepareParams()
    {
        if (!empty($this->getSignatureStructure(self::MORE_PARAMS))) {
            return $this->getSignatureStructure(self::MORE_PARAMS);
        }
    }

    public function prepareServerParams(Request $request)
    {
        if (!empty($this->getSignatureStructure(self::SERVER_PARAMS))) {
            $elements = [];
            foreach ($this->getSignatureStructure(self::SERVER_PARAMS) as $key) {
                if (!$request->server->has($key)) {
                    throw new InvalidArgumentException("Server has no $key element!");
                }
                $element = $request->server($key);
                if (is_array($element)) {
                    foreach ($element as $info) {
                        $elements[] = $info;
                    }
                } else {
                    $elements [] = $element;
                }
            }
            return $elements;
        }
        return [];
    }

    public function prepareRequestHeaders(Request $request)
    {
        if (!empty($this->getSignatureStructure(self::HEADERS))) {
            $headers = [];
            foreach ($this->getSignatureStructure(self::HEADERS) as $header) {
                if (!$request->hasHeader($header)) {
                    throw new InvalidArgumentException("the mentioned $header for the request are not included in the request");
                }
                $headerValue = $request->header($header);
                if (is_array($headerValue)) {
                    foreach ($headerValue as $value) {
                        $headers [] = $value;
                    }
                }
                $headers [] = $headerValue;
            }
            return $headers;
        }
        return [];
    }

    protected function prepareRequestBody(Request $request)
    {
        foreach ($this->getSignatureStructure(self::BODY) as $key) {
            if (!$request->has($key)) {
                throw new InvalidArgumentException("the mentioned $key for the body is not included in the request!");
            }
        }
        return array_values($request->all($this->getSignatureStructure(self::BODY)));
    }

    /**
     * Set the prerequisites of idempotency configs
     *
     * @param array $config
     * @param string $hashMethod
     * @param string $routeName
     * @return $this
     */
    public function prepareSignature(array $config, string $hashMethod, string $routeName)
    {
        $this->setRequestRouteName($routeName);

        $this->setHashMethod($hashMethod);

        $this->setBody($config);

        $this->setHeaders($config);

        $this->setServerParams($config);

        $this->setMoreParams($config);

        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getSignatureStructure(string $key)
    {
        if (!array_key_exists($key, $this->signatureStructure)) {
            throw new InvalidArgumentException("Signature $key is not valid!");
        }
        return $this->signatureStructure[$key];
    }

    /**
     * @return mixed
     */
    public function getHashMethod()
    {
        return $this->hashMethod;
    }

    /**
     * Set Server keywords based on idempotency config
     *
     * @param $config
     */
    public function setServerParams($config) : void
    {
        $value = $this->provideValidData($config, self::SERVER_PARAMS);
        $this->signatureStructure[self::SERVER_PARAMS] = $value;
    }

    /**
     * Set more params based on idempotency config
     *
     * @param $config
     */
    public function setMoreParams($config) : void
    {
        $value = $this->provideValidData($config, self::MORE_PARAMS);
        $this->signatureStructure[self::MORE_PARAMS] = $value;
    }

    /**
     * Set hash method
     *
     * @param $hashMethod
     */
    protected function setHashMethod($hashMethod): void
    {
        $this->hashMethod = $hashMethod;
    }

    /**
     * Set body keywords based on idempotency config
     *
     * @param $config
     * @return mixed
     */
    protected function setBody($config) : void
    {
        if (!isset($config[self::BODY]) || empty($config[self::BODY])) {
            throw new InvalidArgumentException('The body keywords cannot be empty or not set!');
        }
        $this->signatureStructure['body'] = $config[self::BODY];
    }

    /**
     * Set header keywords based on idempotency config
     *
     * @param $config
     */
    protected function setHeaders($config): void
    {
        $value = $this->provideValidData($config, self::HEADERS);
        $this->signatureStructure[self::HEADERS] = $value;
    }


    protected function setRequestRouteName(string $routeName) : void
    {
        $this->prefix = $routeName . '_';
    }

    /**
     * Provide the valid information set in the idempotency config file
     *
     * @param $config
     * @param string $key
     * @return null|array
     */
    protected function provideValidData($config, string $key)
    {
        if (isset($config[$key]) && empty($config[$key])) {
            throw new InvalidArgumentException($key . ' cannot be null!');
        } else if (isset($config[$key])) {
            return $config[$key];
        } else {
            return null;
        }
    }
}
