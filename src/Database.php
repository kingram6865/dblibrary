<?php

declare(strict_types=1);

namespace Sdl\Database;

use InvalidArgumentException;
use PDO;
use PDOException;
use RuntimeException;

final class Database
{
  /**
   * @var array<string, array<string, mixed>>
   */
  private array $config;

  /**
   * @var array<string, PDO>
   */
  private array $connections = [];

  /**
   * @var array<int, mixed>
   */
  private array $pdoOptions;

  /**
   * @param array<string, array<string, mixed>> $config
   * @param array<int, mixed> $pdoOptions
   */
  public function __construct(array $config, array $pdoOptions = [])
  {
    $this->config = $config;
    $this->pdoOptions = $pdoOptions ?: self::defaultPdoOptions();
  }

  /**
   * Get a named PDO connection from the config map.
   * 
   * @throws InvalidArgumentException
   * @throws RuntimeException
   * @throws PDOException 
   */
  public function getConnection(string $name = 'default'): PDO
  {
    if (isset($this->connections[$name])) {
      return $this->connections[$name];
    }

    if (!array_key_exists($name, $this->config)) {
      throw new InvalidArgumentException("Unknown database connection: {$name}");
    }

    $connectionConfig = $this->config[$name];
    $this->validateConfig($name, $connectionConfig);
    $dsn = $this->buildDsn($connectionConfig);
    $this->connections[$name] = new PDO($dsn, (string) $connectionConfig['user'], (string) $connectionConfig['pass'], $this->pdoOptions);
    return $this->connections[$name];
  }

  /**
   * Build a one-off PDO from a single connection config.
   * 
   * @param array<string, mixed> $config
   * @param array<int, mixed> $pdoOptions
   * 
   * @throws RuntimeException
   * @throws PDOException
   */
  public static function createPdo(array $config, array $pdoOptions = []): PDO
  {
    $instance = new self(['default' => $config], $pdoOptions ?: self::defaultPdoOptions());
    return $instance->getConnection('default');
  }

  public function hasConnection(string $name): bool
  {
    return isset($this->connections[$name]); 
  }

  public function disconnect(string $name): void
  {
    unset($this->connections[$name]);
  }

  public function disconnectAll(): void
  {
    $this->connections = [];
  }

  /**
   * @return array<int, mixed>
   */
  public static function defaultPdoOptions(): array
  {
    return [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
      PDO::ATTR_EMULATE_PREPARES => false,
    ];
  }

  /**
   * @param array<string, mixed> $config
   * 
   * @throws RuntimeException
   */
  private function validateConfig(string $name, array $config): void
  {
    $required = ['driver', 'host', 'port', 'dbname', 'user', 'pass'];
    foreach ($required as $key) {
      if (!array_key_exists($key, $config)) {
        throw new RuntimeException("Missing required config key '{$key}' for connection '{$name}'");
      }

      if (is_string($config[$key]) && trim($config[$key]) === '') {
        throw new RuntimeException("Empty required config value '{$key}' for connection '{$name}'");
      }

      if (!is_int($config['port'])) {
        throw new RuntimeException("Config key 'port' must be an integer for connection '{$name}'");
      }
    }
  }

  /**
   * @param array<string, mixed> $config
   * 
   * @throws RuntimeException
   */
  private function buildDsn(array $config): string
  {
    $driver = (string) $config['driver'];
    return match ($driver) {
      'mysql' => $this->buildMysqlDsn($config),
      'pgsql' => $this->buildPgsqlDsn($config),
      default => throw new RuntimeException("Unsupported driver: {$driver}"),
    };
  }

  /**
   * @param array<string, mixed> $config
   */
  private function buildMysqlDsn(array $config): string
  {
    $charset = isset($config['charset']) && is_string($config['charset']) && trim($config['charset']) !== '' ? $config['charset'] : 'utf8mb4';
    return sprintf(
      'mysql:host=%s;port=%d;dbname=%s;charset=%s',
      $config['host'],
      $config['port'],
      $config['dbname'],
      $charset
    );
  }

  /**
   * @param array<string, mixed> $config
   */
  private function buildPgsqlDsn(array $config): string
  {
    return sprintf('pgsql:host=%s;port=%d;dbname=%s', $config['host'], $config['port'], $config['dbname']);
  }
}