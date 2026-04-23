<?php

/**
 * @package     J2Commerce
 * @subpackage  com_j2commercemigrator
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace J2Commerce\Component\J2commercemigrator\Administrator\Service;

use J2Commerce\Component\J2commercemigrator\Administrator\Service\Reader\JoomlaSourceReader;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\Reader\PdoSourceReader;
use J2Commerce\Component\J2commercemigrator\Administrator\Service\Reader\SourceDatabaseReaderInterface;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

class ConnectionManager
{
    private const SESSION_KEY_SECRET = 'com_j2commercemigrator.source_conn';
    private const SESSION_KEY_META   = 'com_j2commercemigrator.source_conn.meta';
    private const LOG_CATEGORY       = 'com_j2commercemigrator';
    private const PREFIX_REGEX       = '/^[a-zA-Z0-9_]{1,32}$/';

    public function __construct(
        private CMSApplicationInterface $app,
        private DatabaseInterface $db,
        private string $probeTable = 'j2store_countries'
    ) {
        Log::addLogger(
            ['text_file' => 'com_j2commercemigrator.log'],
            Log::ALL,
            [self::LOG_CATEGORY]
        );
    }

    /** Validates credentials, opens a PDO connection, and probes for source tables. */
    public function verify(array $creds): array
    {
        $mode = $creds['mode'] ?? '';

        if (!in_array($mode, ['A', 'B', 'C'], true)) {
            return ['ok' => false, 'category' => 'invalid_input'];
        }

        if ($mode === 'A') {
            $verifiedAt = time();
            $meta       = $this->buildJoomlaMeta();
            $meta['verifiedAt'] = $verifiedAt;
            $this->store(['mode' => 'A', 'verifiedAt' => $verifiedAt], $meta);
            return ['ok' => true, 'mode' => 'A', 'meta' => $meta];
        }

        $database = trim($creds['database'] ?? '');
        $username = trim($creds['username'] ?? '');
        $password = $creds['password'] ?? '';
        $prefix   = trim($creds['prefix'] ?? 'jos_');
        $ssl      = (bool) ($creds['ssl'] ?? false);
        $sslCa    = trim($creds['ssl_ca'] ?? '');

        $host = $mode === 'C' ? trim($creds['host'] ?? '') : $this->getJoomlaHost();
        $port = $mode === 'C' ? (int) ($creds['port'] ?? 3306) : $this->getJoomlaPort();

        if ($database === '' || $username === '') {
            return ['ok' => false, 'category' => 'invalid_input'];
        }

        if ($mode === 'C' && $host === '') {
            return ['ok' => false, 'category' => 'invalid_input'];
        }

        if ($port < 1 || $port > 65535) {
            return ['ok' => false, 'category' => 'invalid_input'];
        }

        if (!preg_match(self::PREFIX_REGEX, $prefix)) {
            return ['ok' => false, 'category' => 'invalid_input'];
        }

        try {
            $pdo = $this->buildPdo($host, $port, $database, $username, $password, $ssl, $sslCa);
        } catch (\PDOException $e) {
            $category = $this->mapPdoException($e);
            $this->log(Log::WARNING, "verify() failed — category={$category}", $host, $database, $prefix);
            return ['ok' => false, 'category' => $category, 'details' => $this->describePdoException($e)];
        }

        try {
            $versionStmt = $pdo->query('SELECT VERSION()');
            $version     = $versionStmt ? (string) $versionStmt->fetchColumn() : '';
        } catch (\PDOException $e) {
            $category = $this->mapPdoException($e);
            return ['ok' => false, 'category' => $category, 'details' => $this->describePdoException($e)];
        }

        try {
            $tableName = $prefix . $this->probeTable;
            $checkStmt = $pdo->prepare(
                'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
            );
            $checkStmt->execute([$tableName]);
            $tableExists = (int) $checkStmt->fetchColumn();

            if (!$tableExists) {
                $this->log(Log::WARNING, "verify() no source tables found for prefix={$prefix}", $host, $database, $prefix);
                return ['ok' => false, 'category' => 'no_source_tables'];
            }
        } catch (\PDOException $e) {
            $category = $this->mapPdoException($e);
            return ['ok' => false, 'category' => $category, 'details' => $this->describePdoException($e)];
        }

        $verifiedAt = time();
        $secret     = compact('mode', 'host', 'port', 'database', 'username', 'password', 'prefix', 'ssl', 'sslCa', 'verifiedAt');
        $meta       = [
            'mode'       => $mode,
            'host'       => $host,
            'port'       => $port,
            'dbname'     => $database,
            'username'   => $username,
            'password'   => $password,
            'prefix'     => $prefix,
            'ssl'        => $ssl,
            'sslCa'      => $sslCa,
            'version'    => $version,
            'verifiedAt' => $verifiedAt,
        ];

        $this->store($secret, $meta);
        $this->log(Log::INFO, "verify() success", $host, $database, $prefix);

        return ['ok' => true, 'mode' => $mode, 'meta' => $meta];
    }

    public function store(array $secret, array $meta): void
    {
        $session = $this->app->getSession();
        $session->set(self::SESSION_KEY_SECRET, $secret);
        $session->set(self::SESSION_KEY_META, $meta);
    }

    public function clear(): void
    {
        $session = $this->app->getSession();
        $session->set(self::SESSION_KEY_SECRET, null);
        $session->set(self::SESSION_KEY_META, null);
    }

    public function hasConnection(): bool
    {
        $secret = $this->app->getSession()->get(self::SESSION_KEY_SECRET);
        return is_array($secret) && isset($secret['mode']);
    }

    public function isReady(): bool
    {
        $secret = $this->app->getSession()->get(self::SESSION_KEY_SECRET);

        if (!is_array($secret) || !isset($secret['mode'])) {
            return false;
        }

        if ($secret['mode'] === 'A') {
            return true;
        }

        return !empty($secret['database']) && !empty($secret['username']) && isset($secret['verifiedAt']);
    }

    public function getStatus(): array
    {
        $meta = $this->app->getSession()->get(self::SESSION_KEY_META);
        return is_array($meta) ? $meta : ['mode' => 'none'];
    }

    public function getReader(): SourceDatabaseReaderInterface
    {
        $secret = $this->app->getSession()->get(self::SESSION_KEY_SECRET);
        $mode   = is_array($secret) ? ($secret['mode'] ?? 'A') : 'A';

        if ($mode === 'A') {
            return new JoomlaSourceReader($this->db);
        }

        $pdo = $this->getPdo();

        if ($pdo === null) {
            return new JoomlaSourceReader($this->db);
        }

        return new PdoSourceReader(
            $pdo,
            $secret['prefix'] ?? 'jos_',
            $secret['database'] ?? ''
        );
    }

    public function getPdo(): ?\PDO
    {
        $secret = $this->app->getSession()->get(self::SESSION_KEY_SECRET);

        if (!is_array($secret) || ($secret['mode'] ?? '') === 'A') {
            return null;
        }

        try {
            return $this->buildPdo(
                $secret['host'] ?? '',
                (int) ($secret['port'] ?? 3306),
                $secret['database'] ?? '',
                $secret['username'] ?? '',
                $secret['password'] ?? '',
                (bool) ($secret['ssl'] ?? false),
                $secret['sslCa'] ?? ''
            );
        } catch (\PDOException) {
            return null;
        }
    }

    private function buildPdo(
        string $host,
        int $port,
        string $database,
        string $username,
        string $password,
        bool $ssl,
        string $sslCa
    ): \PDO {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $host,
            $port,
            $database
        );

        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
            \PDO::ATTR_TIMEOUT            => 10,
        ];

        if ($ssl) {
            if ($sslCa !== '') {
                $options[\PDO::MYSQL_ATTR_SSL_CA]                 = $sslCa;
                $options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
            } else {
                $options[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
            }
        }

        return new \PDO($dsn, $username, $password, $options);
    }

    private function mapPdoException(\PDOException $e): string
    {
        $code    = (int) $e->getCode();
        $message = strtolower($e->getMessage());

        if ($code === 1049 || str_contains($message, 'unknown database')) {
            return 'database_not_found';
        }

        if ($code === 1044 || (str_contains($message, 'access denied for user') && str_contains($message, 'to database'))) {
            return 'access_denied_db';
        }

        if ($code === 1045 || str_contains($message, 'access denied')) {
            return 'access_denied';
        }

        if ($code === 2054 || str_contains($message, 'authentication method') || str_contains($message, 'caching_sha2_password')) {
            return 'auth_method_unsupported';
        }

        if ($code === 2002 || str_contains($message, 'connection refused') || str_contains($message, 'no such file or directory')) {
            return 'connection_refused';
        }

        if ($code === 2003 || $code === 2013 || str_contains($message, 'timeout') || str_contains($message, "can't connect") || str_contains($message, 'lost connection')) {
            return 'timeout';
        }

        if ($code === 3159 || str_contains($message, 'ssl')) {
            return 'ssl_required';
        }

        if (str_contains($message, 'could not be resolved') || str_contains($message, 'getaddrinfo') || str_contains($message, 'name or service not known') || str_contains($message, 'unknown mysql server host') || str_contains($message, 'unknown host')) {
            return 'host_unknown';
        }

        return 'unknown';
    }

    private function describePdoException(\PDOException $e): string
    {
        $code = (string) $e->getCode();
        $msg  = trim($e->getMessage());

        return ($code === '' || $code === '0') ? $msg : '[' . $code . '] ' . $msg;
    }

    private function log(int $level, string $message, string $host, string $dbname, string $prefix): void
    {
        $context = ($host !== '' || $dbname !== '' || $prefix !== '')
            ? " [host={$host} db={$dbname} prefix={$prefix}]"
            : '';

        Log::add($message . $context, $level, self::LOG_CATEGORY);
    }

    private function getJoomlaHost(): string
    {
        try {
            return (string) ($this->app->get('config')?->get('host') ?? 'localhost');
        } catch (\Throwable) {
            return 'localhost';
        }
    }

    private function getJoomlaPort(): int
    {
        return 3306;
    }

    private function buildJoomlaMeta(): array
    {
        return [
            'mode'       => 'A',
            'host'       => $this->getJoomlaHost(),
            'dbname'     => (string) ($this->db->setQuery('SELECT DATABASE()')->loadResult() ?? ''),
            'prefix'     => $this->db->getPrefix(),
            'verifiedAt' => time(),
        ];
    }
}
