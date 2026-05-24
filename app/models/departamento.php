<?php

class Departamento
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getActive(): array
    {
        $sql = "
            SELECT id_departamento, nombre_departamento
            FROM departamentos
            WHERE estado = 'activo'
            ORDER BY nombre_departamento ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function existsActive(int $id_departamento): bool
    {
        $sql = "
            SELECT id_departamento
            FROM departamentos
            WHERE id_departamento = :id_departamento
            AND estado = 'activo'
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_departamento', $id_departamento, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetch();
    }
}