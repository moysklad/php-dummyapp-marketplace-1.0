<?php

require_once __DIR__ . '/repo.php';

class JwtSqliteRepository extends SqliteRepository
{
    // Время хранения старых записей в таблице: 24 часа
    private const JTI_TTL_SECONDS = 86400;

    // Вероятность запуска очистки (на production лучше установить <= 5)
    private const DELETE_EXPIRED_CHANCE_PERCENT = 50;

    public function register(string $jti): bool
    {
        $this->deleteExpiredMaybe();

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

    public function deleteExpired(): void
    {
        $stmt = $this->connection()->prepare(
            'DELETE FROM jwt
            WHERE create_time < :expired_before'
        );

        $stmt->execute([
            ':expired_before' => gmdate('c', time() - self::JTI_TTL_SECONDS),
        ]);
    }

    private function deleteExpiredMaybe(): void
    {
        // Для оптимизации чтобы не выполнять очистку при каждой регистрации, делаем ее только иногда
        if (mt_rand(1, 100) > self::DELETE_EXPIRED_CHANCE_PERCENT) {
            return;
        }

        $this->deleteExpired();
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
