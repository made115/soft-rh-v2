<?php

class Rol
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getAll(): array
    {
        $sql = "
            SELECT id_rol, nombre_rol, descripcion
            FROM roles
            ORDER BY nombre_rol ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function exists(int $id_rol): bool
    {
        $sql = "
            SELECT id_rol
            FROM roles
            WHERE id_rol = :id_rol
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_rol', $id_rol, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetch();
    }
}