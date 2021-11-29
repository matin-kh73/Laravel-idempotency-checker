<?php

namespace Mtkh\IdempotencyHandler\RedisUtils;

use Closure;
use RedisClient\ClientFactory;
use RedisLock\Exception\InvalidArgumentException;

class ThrottleSimultaneousRequests
{
    const IN_PROGRESS = 'in_progress';

    /**
     * The current user's signature.
     *
     * @var string
     */
    protected $signature;

    /**
     * @var \RedisClient\Client\Version\RedisClient2x6|\RedisClient\Client\Version\RedisClient2x8|\RedisClient\Client\Version\RedisClient3x0|\RedisClient\Client\Version\RedisClient3x2|\RedisClient\Client\Version\RedisClient4x0|\RedisClient\Client\Version\RedisClient5x0|\RedisClient\RedisClient
     */
    private $redis;

    /**
     * @var IdempotencyRedisLock
     */
    private $lock;

    /**
     * @var RequestResolver
     */
    private $requestResolver;

    public function __construct(RequestResolver $requestResolver)
    {
        $this->redis = ClientFactory::create([
            'server' => config('idempotency.redis.host'). ':' . config('idempotency.redis.port'),
            'password' => config('idempotency.redis.password'),
        ]);
        $this->requestResolver = $requestResolver;
        $this->signature = $requestResolver->createSignature();
        try {
            $this->lock = new IdempotencyRedisLock($this->redis, $this->signature, IdempotencyRedisLock::FLAG_DO_NOT_THROW_EXCEPTIONS);
        } catch (InvalidArgumentException $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \RedisLock\Exception\InvalidArgumentException
     * @throws \RedisLock\Exception\LockHasAcquiredAlreadyException
     */
    public function handle($request, Closure $next)
    {
        $result = $this->redis->get($this->signature);
        if ($result && $result != self::IN_PROGRESS) {
            return unserialize($result);
        }

        $timeout = $this->requestResolver->getTimeout();

        if ($this->lock->setToken(self::IN_PROGRESS)->acquire($timeout)) {
            $response = $next($request);
            $this->lock->updateToken($this->signature, serialize($response));
            return $response;
        }
        return response()->json($this->requestResolver->getResponse());
    }
}
