# phpdot/cache

PSR-16 cache with pluggable drivers. Redis, File, Array, APCu, Null. `remember()` pattern. Standalone.

## Install

```bash
composer require phpdot/cache
```

## Architecture

```mermaid
graph TD
    S[Store] -->|PSR-16| DI[DriverInterface]
    S -->|remember / rememberForever| DI

    DI --> RD[RedisDriver]
    DI --> FD[FileDriver]
    DI --> AD[ArrayDriver]
    DI --> APD[ApcuDriver]
    DI --> ND[NullDriver]
    DI --> CD[Custom Driver]

    RD -->|serialize| SER[Serializer]
    FD -->|serialize| SER

    style S fill:#2d3748,color:#fff
    style DI fill:#4a5568,color:#fff
    style RD fill:#718096,color:#fff
    style FD fill:#718096,color:#fff
    style AD fill:#718096,color:#fff
    style APD fill:#718096,color:#fff
    style ND fill:#718096,color:#fff
    style CD fill:#718096,color:#fff
    style SER fill:#718096,color:#fff
```

## Usage

### Basic

```php
use PHPdot\Cache\Store;
use PHPdot\Cache\Driver\RedisDriver;

$cache = new Store(new RedisDriver($redis, prefix: 'app:'));

$cache->set('user:1', $userData, 3600);
$user = $cache->get('user:1');
$cache->delete('user:1');
$cache->has('user:1'); // false
```

### Remember pattern

```php
$user = $cache->remember('user:1', 3600, function () use ($db) {
    return $db->table('users')->find(1);
});
// First call: queries DB, caches result
// Subsequent calls: returns from cache

$config = $cache->rememberForever('app:config', fn() => loadConfig());
```

### Swap backends

```php
$cache = new Store(new RedisDriver($redis, prefix: 'app:'));
$cache = new Store(new FileDriver('/var/cache/app'));
$cache = new Store(new ArrayDriver());
$cache = new Store(new ApcuDriver(prefix: 'app:'));
$cache = new Store(new NullDriver());
// Same API, different backend
```

### Batch operations

```php
$cache->setMultiple([
    'user:1' => $user1,
    'user:2' => $user2,
], ttl: 3600);

$users = $cache->getMultiple(['user:1', 'user:2', 'user:3'], default: null);
$cache->deleteMultiple(['user:1', 'user:2']);
```

### LRU eviction (ArrayDriver)

```php
$cache = new Store(new ArrayDriver(maxItems: 1000));
// Evicts oldest entry when full
```

### Custom driver

```php
use PHPdot\Cache\DriverInterface;

final class MongoDriver implements DriverInterface
{
    // Implement 8 methods: get, set, delete, clear, has,
    // getMultiple, setMultiple, deleteMultiple
}

$cache = new Store(new MongoDriver($collection));
```

## Drivers

| Driver | Backend | Serialization | Shared | Use case |
|--------|---------|---------------|--------|----------|
| `RedisDriver` | ext-redis | igbinary/serialize | Yes | Production, distributed |
| `FileDriver` | Filesystem | igbinary/serialize | Yes (disk) | Single server, no Redis |
| `ArrayDriver` | PHP array | None | No (per-worker) | Testing, short-lived |
| `ApcuDriver` | ext-apcu | None (SHM) | Yes (per-server) | Single server, fast reads |
| `NullDriver` | None | None | N/A | Testing, disabled cache |

## PSR-16 Compliance

Store implements `Psr\SimpleCache\CacheInterface`:
- Key validation: rejects `{}()/\@:` characters and empty strings
- TTL normalization: accepts `int`, `DateInterval`, or `null`
- Negative TTL treated as expired
- Throws `PHPdot\Cache\Exception\InvalidArgumentException` for invalid keys

## Requirements

- PHP >= 8.3
- psr/simple-cache ^3.0
- ext-redis (for RedisDriver)
- ext-apcu (for ApcuDriver)
- ext-igbinary (optional, faster serialization)

## License

MIT
