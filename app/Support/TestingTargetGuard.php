<?php

namespace App\Support;

use Illuminate\Database\Connection;
use RuntimeException;

final class TestingTargetGuard
{
    public const EXPECTED = ['APP_ENV'=>'testing','MYSQL_TEST_ENABLED'=>'true','DB_CONNECTION'=>'mysql','DB_HOST'=>'127.0.0.1','DB_PORT'=>'13306','DB_DATABASE'=>'kaizen_partners_test','MYSQL_TEST_DATABASE'=>'kaizen_partners_test','DB_USERNAME'=>'kaizen_test'];

    public static function validateEnvironment(?callable $read = null): void
    {
        $read ??= static fn (string $key): string => (string) (getenv($key) !== false ? getenv($key) : ($_ENV[$key] ?? $_SERVER[$key] ?? ''));
        foreach (self::EXPECTED as $key => $expected) if ($read($key) !== $expected) throw new RuntimeException("Invalid isolated test configuration: {$key}");
        foreach (['DB_URL','DB_SOCKET','DB_CACHE_CONNECTION','DB_CACHE_LOCK_CONNECTION'] as $key) if ($read($key) !== '') throw new RuntimeException("Unsupported test connection override: {$key}");
    }

    public static function validateConnection(Connection $connection): void
    {
        self::validateEnvironment();
        if ($connection->getDriverName() !== 'mysql') throw new RuntimeException('The effective test driver is not mysql.');
        $row = $connection->selectOne('SELECT DATABASE() AS db, CURRENT_USER() AS user, @@port AS port, @@server_uuid AS uuid');
        if (($row->db ?? null) !== self::EXPECTED['DB_DATABASE'] || ($row->user ?? null) !== 'kaizen_test@%' || (string) ($row->port ?? '') !== '3306' || empty($row->uuid)) throw new RuntimeException('Effective MySQL test identity is not approved.');
        $grants = $connection->select('SHOW GRANTS FOR CURRENT_USER()');
        $text = implode("\n", array_map(static fn (object $grant): string => implode(' ', (array) $grant), $grants));
        $normalized = str_replace('\\', '', $text);
        if (stripos($normalized, '`kaizen_partners_test`') === false || stripos($normalized, 'GRANT ALL') === false) throw new RuntimeException('Test user grant is not database-scoped as expected.');
    }
}
