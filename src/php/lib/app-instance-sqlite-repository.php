<?php

class AppInstanceSqliteRepository
{
    private ?PDO $pdo = null;

    public function load(string $appId, string $accountId): AppInstance
    {
        $statement = $this->connection()->prepare(
            'SELECT status, access_token, info_message, store
            FROM account_application
            WHERE application_id = :application_id AND account_id = :account_id'
        );

        $statement->execute([
            ':application_id' => $appId,
            ':account_id' => $accountId,
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $app = new AppInstance($appId, $accountId);

        if ($row === false) {
            return $app;
        }

        $app->status = isset($row['status']) ? (int)$row['status'] : AppInstance::UNKNOWN;
        $app->accessToken = $row['access_token'] ?? null;
        $app->infoMessage = $row['info_message'] ?? null;
        $app->store = $row['store'] ?? null;

        return $app;
    }

    public function persist(AppInstance $app): void
    {
        $timestamp = gmdate('c');
        $statement = $this->connection()->prepare(
            'INSERT INTO account_application (
                account_id,
                application_id,
                status,
                access_token,
                info_message,
                store,
                created_at,
                updated_at
            ) VALUES (
                :account_id,
                :application_id,
                :status,
                :access_token,
                :info_message,
                :store,
                :created_at,
                :updated_at
            )
            ON CONFLICT(account_id, application_id) DO UPDATE SET
                status = excluded.status,
                access_token = excluded.access_token,
                info_message = excluded.info_message,
                store = excluded.store,
                updated_at = excluded.updated_at'
        );

        $statement->execute([
            ':account_id' => (string)$app->accountId,
            ':application_id' => (string)$app->appId,
            ':status' => (int)$app->status,
            ':access_token' => $this->normalizeNullableString($app->accessToken ?? null),
            ':info_message' => $this->normalizeNullableString($app->infoMessage ?? null),
            ':store' => $this->normalizeNullableString($app->store ?? null),
            ':created_at' => $timestamp,
            ':updated_at' => $timestamp,
        ]);
    }

    public function delete(string $appId, string $accountId): void
    {
        $statement = $this->connection()->prepare(
            'DELETE FROM account_application
            WHERE application_id = :application_id AND account_id = :account_id'
        );

        $statement->execute([
            ':application_id' => $appId,
            ':account_id' => $accountId,
        ]);
    }

    private function connection(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        if (!class_exists('PDO')) {
            $this->fail('PDO extension is required for application storage');
        }

        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->fail('pdo_sqlite extension is required for application storage');
        }

        $databasePath = appDatabasePath();
        $directory = dirname($databasePath);

        if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
            $this->fail('Failed to create SQLite directory: ' . $directory);
        }

        try {
            $pdo = new PDO('sqlite:' . $databasePath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->initializeSchema($pdo);
        } catch (Throwable $exception) {
            $this->fail('Failed to initialize SQLite storage: ' . $exception->getMessage(), $exception);
        }

        $this->pdo = $pdo;

        return $this->pdo;
    }

    private function initializeSchema(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS account_application (
                account_id TEXT NOT NULL,
                application_id TEXT NOT NULL,
                status INTEGER,
                access_token TEXT,
                info_message TEXT,
                store TEXT,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL,
                PRIMARY KEY (account_id, application_id)
            )'
        );
    }

    private function normalizeNullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);

        return $value === '' ? null : $value;
    }

    private function fail(string $message, ?Throwable $previous = null): void
    {
        log_message('ERROR', $message);

        throw new RuntimeException($message, 0, $previous);
    }
}
