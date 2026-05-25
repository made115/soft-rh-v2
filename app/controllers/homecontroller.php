<?php

class HomeController extends Controller
{
    public function index(): void
    {
        require_auth();

        $this->view('home/index', [
            'title' => 'Inicio',
            'totalUsuarios' => $this->countRows('usuarios'),
            'totalEmpleados' => $this->countRows('empleados'),
            'totalContratos' => $this->countRows('contratos'),
            'contratosProximosVencer' => $this->countContratosProximosVencer(),

            // Datos simulados para presentación porque estos módulos aún no están programados.
            'vacacionesProximasIniciar' => 2,
            'vacacionesProximasTerminar' => 1,
            'seguimientosSinTerminar' => 3
        ]);
    }

    private function countRows(string $table): int
    {
        $allowedTables = ['usuarios', 'empleados', 'contratos'];

        if (!in_array($table, $allowedTables, true)) {
            return 0;
        }

        $db = Database::connect();
        $stmt = $db->query("SELECT COUNT(*) AS total FROM {$table}");
        $result = $stmt->fetch();

        return $result ? (int) $result['total'] : 0;
    }

    private function countContratosProximosVencer(): int
    {
        $db = Database::connect();

        $sql = "
            SELECT COUNT(*) AS total
            FROM contratos
            WHERE estado_contrato = 'vigente'
            AND fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();

        return $result ? (int) $result['total'] : 0;
    }
}