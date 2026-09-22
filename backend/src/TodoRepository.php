<?php

/**
 * TodoRepository.php
 *
 * Bertanggung jawab untuk semua query ke tabel `todos`.
 * Semua query menggunakan prepared statement (parameter binding)
 * untuk mencegah SQL Injection.
 */

class TodoRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Mengambil seluruh todo, diurutkan berdasarkan id.
     */
    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM todos ORDER BY id ASC');
        return $stmt->fetchAll();
    }

    /**
     * Mengambil satu todo berdasarkan id.
     * Mengembalikan null jika tidak ditemukan.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM todos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $todo = $stmt->fetch();

        return $todo === false ? null : $todo;
    }

    /**
     * Membuat todo baru dan mengembalikan data yang tersimpan.
     */
    public function create(string $title): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO todos (title, completed, created_at, updated_at)
             VALUES (:title, FALSE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
             RETURNING *'
        );
        $stmt->execute(['title' => $title]);

        return $stmt->fetch();
    }

    /**
     * Mengubah sebagian data todo (title dan/atau completed).
     * Field yang tidak dikirim (null) tidak akan diubah.
     * Mengembalikan null jika todo tidak ditemukan.
     */
    public function update(int $id, ?string $title, ?bool $completed): ?array
    {
        $existing = $this->findById($id);
        if ($existing === null) {
            return null;
        }

        $newTitle     = $title ?? $existing['title'];
        $newCompleted = $completed ?? $this->toBool($existing['completed']);

        $stmt = $this->db->prepare(
            'UPDATE todos
             SET title = :title, completed = :completed, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
             RETURNING *'
        );
        $stmt->bindValue('title', $newTitle, PDO::PARAM_STR);
        $stmt->bindValue('completed', $newCompleted, PDO::PARAM_BOOL);
        $stmt->bindValue('id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Menormalkan nilai completed (bisa berupa PHP bool atau string 't'/'f'
     * dari PostgreSQL) menjadi PHP bool murni.
     *
     * Diperlukan karena tanpa normalisasi ini, PDO PostgreSQL dengan
     * ATTR_EMULATE_PREPARES = false akan mengirim PHP `false` sebagai
     * string kosong (""), yang ditolak Postgres dengan error
     * "invalid input syntax for type boolean". Ini yang menyebabkan
     * uncentang (set completed = false) gagal sebelumnya.
     */
    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return $value === 't' || $value === 'true' || $value === '1' || $value === 1;
    }

    /**
     * Menghapus todo berdasarkan id.
     * Mengembalikan true jika berhasil dihapus, false jika tidak ditemukan.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM todos WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}