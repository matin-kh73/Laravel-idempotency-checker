<?php


namespace Mtkh\IdempotencyHandler\RedisUtils;


interface IdempotencyConfigInterface
{
    const DEFAULT_TIMEOUT = 15 * 60;

    const DEFAULT_HASH = 'sha1';

    const BODY = 'body';

    const HEADERS = 'headers';

    const HASH_METHOD = 'hash';

    const ENTITIES = 'entities';

    const TIMEOUT = 'timeout';

    const SIGNATURE = 'signature';

    const MORE_PARAMS = 'more';

    const SERVER_PARAMS = 'server';

    const RESPONSE = 'response';

    const DEFAULT_RESPONSE = 'Request error, please try again later!';
}
