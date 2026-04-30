<?php

abstract class SqliteRepository
{
    private ?PDO $pdo = null;

    protected function connection(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        if (!class_exists('PDO')) {
            $message = 'PDO extension is required';
            log_message('ERROR', $message);
            throw new RuntimeException($message);
        }

        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $message = 'pdo_sqlite extension is required';
            log_message('ERROR', $message);
            throw new RuntimeException($message);
        }

        $databasePath = appDatabasePath();
        $directory = dirname($databasePath);

        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            $message = 'Failed to create SQLite directory: ' . $directory;
            log_message('ERROR', $message);
            throw new RuntimeException($message);
        }

        try {
            $pdo = new PDO('sqlite:' . $databasePath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->initializeSchema($pdo);
            $this->pdo = $pdo;
        } catch (Throwable $exception) {
            $message = 'Failed to initialize: ' . $exception->getMessage();
            log_message('ERROR', $message);
            throw new RuntimeException($message, 0, $exception);
        }

        return $this->pdo;
    }

    abstract protected function initializeSchema(PDO $pdo): void;

}
