<?php

class Puesto
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getActiveWithDepartment(): array
    {
        $sql = "
            SELECT
                p.id_puesto,
                p.id_departamento,
                p.nombre_puesto,
                d.nombre_departamento
            FROM puestos p
            INNER JOIN departamentos d ON d.id_departamento = p.id_departamento
            WHERE p.estado = 'activo'
            AND d.estado = 'activo'
            ORDER BY d.nombre_departamento ASC, p.nombre_puesto ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function existsActiveInDepartment(int $id_puesto, int $id_departamento): bool
    {
        $sql = "
            SELECT p.id_puesto
            FROM puestos p
            INNER JOIN departamentos d ON d.id_departamento = p.id_departamento
            WHERE p.id_puesto = :id_puesto
            AND p.id_departamento = :id_departamento
            AND p.estado = 'activo'
            AND d.estado = 'activo'
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_puesto', $id_puesto, PDO::PARAM_INT);
        $stmt->bindValue(':id_departamento', $id_departamento, PDO::PARAM_INT);
        $stmt->execute();

        return (bool) $stmt->fetch();
    }
}