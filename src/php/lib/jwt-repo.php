<?php

require_once __DIR__ . '/repo.php';

class JwtSqliteRepository extends SqliteRepository
{
    public function register(string $jti): bool
    {
        $stmt = $this->connection()->prepare(
            'INSERT OR IGNORE INTO jwt (
                jti,
                create_time
            ) VALUES (
                :jti,
                :create_time
            )'
        );

        $stmt->execute([
            ':jti' => $jti,
            ':create_time' => gmdate('c'),
        ]);

        return $stmt->rowCount() === 1;
    }

    protected function initializeSchema(PDO $pdo): void
    {
        $pdo->exec('PRAGMA journal_mode=WAL');
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS jwt (
                jti TEXT NOT NULL,
                create_time TEXT NOT NULL,
                PRIMARY KEY (jti)
            )'
        );
    }
}
