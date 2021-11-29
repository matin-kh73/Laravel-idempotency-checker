<?php

namespace Mtkh\IdempotencyHandler\RedisUtils;


use Facade\Ignition\Exceptions\InvalidConfig;
use Illuminate\Http\Request;
use InvalidArgumentException;

class RequestResolver implements IdempotencyConfigInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var string|null
     */
    private $requestRouteName;

    /**
     * @var \Illuminate\Config\Repository
     */
    private $config;

    /**
     * @var SignatureService
     */
    private $signatureService;

    /**
     * RequestResolver constructor.
     * @param Request $request
     * @param SignatureService $signatureService
     * @throws InvalidConfig
     */
    public function __construct(Request $request, SignatureService $signatureService)
    {
        $this->request = $request;
        $this->requestRouteName = $request->route()->getName();
        $this->config = config('idempotency');
        $this->signatureService = $signatureService;
        if (!isset($this->config[self::ENTITIES][$this->requestRouteName])) {
            throw new InvalidConfig('The config is incorrect');
        }
    }

    /**
     * @return float|int
     */
    public function getTimeout()
    {
        if (isset($this->config[self::ENTITIES][$this->requestRouteName][self::TIMEOUT]) && is_int($this->config[self::ENTITIES][$this->requestRouteName][self::TIMEOUT])) {
            return $this->config[self::ENTITIES][$this->requestRouteName][self::TIMEOUT];
        }
        return self::DEFAULT_TIMEOUT;
    }

    public function getResponse()
    {
        if (!isset($this->config[self::ENTITIES][$this->requestRouteName][self::RESPONSE])){
            return self::DEFAULT_RESPONSE;
        }
        return $this->config[self::ENTITIES][$this->requestRouteName][self::RESPONSE];
    }

    /**
     * @return string
     */
    public function createSignature()
    {
        if (isset($this->config[self::ENTITIES][$this->requestRouteName][self::SIGNATURE])) {
            return $this->signatureService
                ->prepareSignature($this->config[self::ENTITIES][$this->requestRouteName][self::SIGNATURE], $this->config[self::HASH_METHOD] ?? self::DEFAULT_HASH, $this->requestRouteName)
                ->createSignatureRequest($this->request);
        }
            throw new InvalidArgumentException('The structure of idempotency config is not valid!');
    }
}
