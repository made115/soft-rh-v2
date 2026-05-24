<?php

class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findByUsername(string $nombre_usuario): ?array
    {
        $sql = "
            SELECT 
                u.id_usuario,
                u.id_rol,
                r.nombre_rol,
                u.nombre,
                u.nombre_usuario,
                u.contrasena_hash,
                u.estado,
                u.requiere_cambio_contrasena,
                u.ultimo_acceso
            FROM usuarios u
            INNER JOIN roles r ON r.id_rol = u.id_rol
            WHERE u.nombre_usuario = :nombre_usuario
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nombre_usuario', $nombre_usuario);
        $stmt->execute();

        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function updateLastAccess(int $id_usuario): bool
    {
        $sql = "
            UPDATE usuarios
            SET ultimo_acceso = NOW()
            WHERE id_usuario = :id_usuario
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getAll(): array
    {
        $sql = "
            SELECT
                u.id_usuario,
                u.nombre,
                u.nombre_usuario,
                u.estado,
                u.requiere_cambio_contrasena,
                u.ultimo_acceso,
                u.fecha_registro,
                r.nombre_rol
            FROM usuarios u
            INNER JOIN roles r ON r.id_rol = u.id_rol
            ORDER BY u.id_usuario ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function usernameExists(string $nombre_usuario): bool
    {
        $sql = "
            SELECT id_usuario
            FROM usuarios
            WHERE nombre_usuario = :nombre_usuario
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nombre_usuario', $nombre_usuario);
        $stmt->execute();

        return (bool) $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $sql = "
            INSERT INTO usuarios (
                id_rol,
                nombre,
                nombre_usuario,
                contrasena_hash,
                estado,
                requiere_cambio_contrasena
            ) VALUES (
                :id_rol,
                :nombre,
                :nombre_usuario,
                :contrasena_hash,
                :estado,
                :requiere_cambio_contrasena
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':id_rol', $data['id_rol'], PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $data['nombre']);
        $stmt->bindValue(':nombre_usuario', $data['nombre_usuario']);
        $stmt->bindValue(':contrasena_hash', $data['contrasena_hash']);
        $stmt->bindValue(':estado', $data['estado']);
        $stmt->bindValue(':requiere_cambio_contrasena', $data['requiere_cambio_contrasena'], PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function findById(int $id_usuario): ?array
    {
        $sql = "
            SELECT
                u.id_usuario,
                u.id_rol,
                r.nombre_rol,
                u.nombre,
                u.nombre_usuario,
                u.estado,
                u.requiere_cambio_contrasena,
                u.ultimo_acceso,
                u.fecha_registro
            FROM usuarios u
            INNER JOIN roles r ON r.id_rol = u.id_rol
            WHERE u.id_usuario = :id_usuario
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function usernameExistsExcept(string $nombre_usuario, int $id_usuario): bool
    {
        $sql = "
            SELECT id_usuario
            FROM usuarios
            WHERE nombre_usuario = :nombre_usuario
            AND id_usuario <> :id_usuario
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nombre_usuario', $nombre_usuario);
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetch();
    }

    public function update(int $id_usuario, array $data): bool
    {
        $sql = "
            UPDATE usuarios
            SET
                id_rol = :id_rol,
                nombre = :nombre,
                nombre_usuario = :nombre_usuario,
                estado = :estado
            WHERE id_usuario = :id_usuario
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':id_rol', $data['id_rol'], PDO::PARAM_INT);
        $stmt->bindValue(':nombre', $data['nombre']);
        $stmt->bindValue(':nombre_usuario', $data['nombre_usuario']);
        $stmt->bindValue(':estado', $data['estado']);
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function updateStatus(int $id_usuario, string $estado): bool
    {
        $sql = "
            UPDATE usuarios
            SET estado = :estado
            WHERE id_usuario = :id_usuario
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':estado', $estado);
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function updatePassword(int $id_usuario, string $contrasena_hash): bool
    {
        $sql = "
            UPDATE usuarios
            SET 
                contrasena_hash = :contrasena_hash,
                requiere_cambio_contrasena = 1
            WHERE id_usuario = :id_usuario
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':contrasena_hash', $contrasena_hash);
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function updateOwnPassword(int $id_usuario, string $contrasena_hash): bool
    {
        $sql = "
            UPDATE usuarios
            SET 
                contrasena_hash = :contrasena_hash,
                requiere_cambio_contrasena = 0
            WHERE id_usuario = :id_usuario
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':contrasena_hash', $contrasena_hash);
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);

        return $stmt->execute();
    }
}