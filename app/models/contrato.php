<?php

class Contrato
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getResumenPorEmpleado(): array
    {
        $sql = "
            SELECT
                e.id_empleado,
                e.nombre_empleado,
                e.apellido_pat_empleado,
                e.apellido_mat_empleado,
                e.estado_laboral,
                p.nombre_puesto,
                d.nombre_departamento,
                c.id_contrato,
                c.fecha_inicio,
                c.fecha_fin,
                c.duracion_meses,
                c.sueldo_diario,
                c.periodicidad_pago,
                c.estado_contrato,
                DATEDIFF(c.fecha_fin, CURDATE()) AS dias_restantes
            FROM empleados e
            INNER JOIN puestos p ON p.id_puesto = e.id_puesto
            INNER JOIN departamentos d ON d.id_departamento = p.id_departamento
            LEFT JOIN contratos c ON c.id_contrato = (
                SELECT c2.id_contrato
                FROM contratos c2
                WHERE c2.id_empleado = e.id_empleado
                AND c2.estado_contrato = 'vigente'
                ORDER BY c2.fecha_inicio DESC, c2.id_contrato DESC
                LIMIT 1
            )
            ORDER BY e.estado_laboral ASC, e.id_empleado ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getEmpleadoParaContrato(int $id_empleado): ?array
    {
        $sql = "
            SELECT
                e.id_empleado,
                e.nombre_empleado,
                e.apellido_pat_empleado,
                e.apellido_mat_empleado,
                e.curp,
                e.rfc,
                e.nss,
                e.numero_preafiliacion_imss,
                e.fecha_ingreso,
                e.estado_laboral,
                p.nombre_puesto,
                d.nombre_departamento
            FROM empleados e
            INNER JOIN puestos p ON p.id_puesto = e.id_puesto
            INNER JOIN departamentos d ON d.id_departamento = p.id_departamento
            WHERE e.id_empleado = :id_empleado
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $stmt->execute();

        $empleado = $stmt->fetch();

        return $empleado ?: null;
    }

    public function getById(int $id_contrato): ?array
    {
        $sql = "
            SELECT
                c.id_contrato,
                c.id_empleado,
                c.id_usuario_registro,
                c.fecha_inicio,
                c.fecha_fin,
                c.duracion_meses,
                c.sueldo_diario,
                c.periodicidad_pago,
                c.observaciones,
                c.estado_contrato,
                c.fecha_registro,
                c.fecha_actualizacion,
                e.nombre_empleado,
                e.apellido_pat_empleado,
                e.apellido_mat_empleado,
                e.curp,
                e.rfc,
                e.nss,
                e.fecha_ingreso,
                e.estado_laboral,
                p.nombre_puesto,
                d.nombre_departamento,
                u.nombre AS usuario_registro
            FROM contratos c
            INNER JOIN empleados e ON e.id_empleado = c.id_empleado
            INNER JOIN puestos p ON p.id_puesto = e.id_puesto
            INNER JOIN departamentos d ON d.id_departamento = p.id_departamento
            INNER JOIN usuarios u ON u.id_usuario = c.id_usuario_registro
            WHERE c.id_contrato = :id_contrato
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_contrato', $id_contrato, PDO::PARAM_INT);
        $stmt->execute();

        $contrato = $stmt->fetch();

        return $contrato ?: null;
    }

    public function getVigenteByEmpleado(int $id_empleado): ?array
    {
        $sql = "
            SELECT
                id_contrato,
                id_empleado,
                fecha_inicio,
                fecha_fin,
                duracion_meses,
                sueldo_diario,
                periodicidad_pago,
                observaciones,
                estado_contrato
            FROM contratos
            WHERE id_empleado = :id_empleado
            AND estado_contrato = 'vigente'
            ORDER BY fecha_inicio DESC, id_contrato DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $stmt->execute();

        $contrato = $stmt->fetch();

        return $contrato ?: null;
    }

    public function create(array $data, int $id_usuario_accion): int
    {
        $this->db->beginTransaction();

        try {
            $contratoVigente = $this->getVigenteByEmpleado((int) $data['id_empleado']);

            if ($contratoVigente) {
                $stmtRenovar = $this->db->prepare("
                    UPDATE contratos
                    SET estado_contrato = 'renovado'
                    WHERE id_contrato = :id_contrato
                    LIMIT 1
                ");
                $stmtRenovar->bindValue(':id_contrato', $contratoVigente['id_contrato'], PDO::PARAM_INT);
                $stmtRenovar->execute();

                $this->registrarBitacora((int) $contratoVigente['id_contrato'], $id_usuario_accion, 'renovacion');
            }

            $sql = "
                INSERT INTO contratos (
                    id_empleado,
                    id_usuario_registro,
                    fecha_inicio,
                    fecha_fin,
                    duracion_meses,
                    sueldo_diario,
                    periodicidad_pago,
                    observaciones,
                    estado_contrato
                ) VALUES (
                    :id_empleado,
                    :id_usuario_registro,
                    :fecha_inicio,
                    :fecha_fin,
                    :duracion_meses,
                    :sueldo_diario,
                    :periodicidad_pago,
                    :observaciones,
                    'vigente'
                )
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_empleado', $data['id_empleado'], PDO::PARAM_INT);
            $stmt->bindValue(':id_usuario_registro', $id_usuario_accion, PDO::PARAM_INT);
            $stmt->bindValue(':fecha_inicio', $data['fecha_inicio']);
            $stmt->bindValue(':fecha_fin', $data['fecha_fin']);
            $stmt->bindValue(':duracion_meses', $data['duracion_meses'], PDO::PARAM_INT);
            $stmt->bindValue(':sueldo_diario', $data['sueldo_diario']);
            $stmt->bindValue(':periodicidad_pago', $data['periodicidad_pago']);
            $stmt->bindValue(':observaciones', $data['observaciones']);
            $stmt->execute();

            $id_contrato = (int) $this->db->lastInsertId();
            $accion = $contratoVigente ? 'registro_renovacion' : 'registro';

            $this->registrarBitacora($id_contrato, $id_usuario_accion, $accion);

            $this->db->commit();

            return $id_contrato;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update(array $data, int $id_usuario_accion): bool
    {
        $this->db->beginTransaction();

        try {
            $sql = "
                UPDATE contratos
                SET
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    duracion_meses = :duracion_meses,
                    sueldo_diario = :sueldo_diario,
                    periodicidad_pago = :periodicidad_pago,
                    observaciones = :observaciones
                WHERE id_contrato = :id_contrato
                AND estado_contrato = 'vigente'
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':fecha_inicio', $data['fecha_inicio']);
            $stmt->bindValue(':fecha_fin', $data['fecha_fin']);
            $stmt->bindValue(':duracion_meses', $data['duracion_meses'], PDO::PARAM_INT);
            $stmt->bindValue(':sueldo_diario', $data['sueldo_diario']);
            $stmt->bindValue(':periodicidad_pago', $data['periodicidad_pago']);
            $stmt->bindValue(':observaciones', $data['observaciones']);
            $stmt->bindValue(':id_contrato', $data['id_contrato'], PDO::PARAM_INT);
            $stmt->execute();

            $this->registrarBitacora((int) $data['id_contrato'], $id_usuario_accion, 'edicion');

            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getHistorialByEmpleado(int $id_empleado): array
    {
        $sql = "
            SELECT
                c.id_contrato,
                c.fecha_inicio,
                c.fecha_fin,
                c.duracion_meses,
                c.sueldo_diario,
                c.periodicidad_pago,
                c.observaciones,
                c.estado_contrato,
                c.fecha_registro,
                u.nombre AS usuario_registro,
                pdf.id_pdf_contrato,
                pdf.nombre_archivo
            FROM contratos c
            INNER JOIN usuarios u ON u.id_usuario = c.id_usuario_registro
            LEFT JOIN pdf_contratos pdf ON pdf.id_pdf_contrato = (
                SELECT pdf2.id_pdf_contrato
                FROM pdf_contratos pdf2
                WHERE pdf2.id_contrato = c.id_contrato
                AND pdf2.estado = 'activo'
                ORDER BY pdf2.version_pdf DESC, pdf2.id_pdf_contrato DESC
                LIMIT 1
            )
            WHERE c.id_empleado = :id_empleado
            ORDER BY c.fecha_inicio DESC, c.id_contrato DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getBitacoraByEmpleado(int $id_empleado): array
    {
        $sql = "
            SELECT
                b.id_bitacora_contrato,
                b.id_contrato,
                b.accion,
                b.fecha_accion,
                u.nombre AS usuario_accion
            FROM bitacora_contratos b
            INNER JOIN contratos c ON c.id_contrato = b.id_contrato
            INNER JOIN usuarios u ON u.id_usuario = b.id_usuario
            WHERE c.id_empleado = :id_empleado
            ORDER BY b.fecha_accion DESC, b.id_bitacora_contrato DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getBitacoraByContrato(int $id_contrato): array
    {
        $sql = "
            SELECT
                b.id_bitacora_contrato,
                b.id_contrato,
                b.accion,
                b.fecha_accion,
                u.nombre AS usuario_accion
            FROM bitacora_contratos b
            INNER JOIN usuarios u ON u.id_usuario = b.id_usuario
            WHERE b.id_contrato = :id_contrato
            ORDER BY b.fecha_accion DESC, b.id_bitacora_contrato DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_contrato', $id_contrato, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function hasPdfActivo(int $id_contrato): bool
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM pdf_contratos
            WHERE id_contrato = :id_contrato
            AND estado = 'activo'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_contrato', $id_contrato, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetch();

        return $resultado && (int) $resultado['total'] > 0;
    }

    private function registrarBitacora(int $id_contrato, int $id_usuario, string $accion): void
    {
        $sql = "
            INSERT INTO bitacora_contratos (
                id_contrato,
                id_usuario,
                accion
            ) VALUES (
                :id_contrato,
                :id_usuario,
                :accion
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_contrato', $id_contrato, PDO::PARAM_INT);
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->bindValue(':accion', $accion);
        $stmt->execute();
    }
}