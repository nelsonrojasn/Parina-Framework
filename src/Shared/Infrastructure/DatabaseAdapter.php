<?php
declare(strict_types=1);
namespace Parina\Shared\Infrastructure;

interface DatabaseAdapter
{
    public function query(string $sql, array $params = []): \PDOStatement;

    public function exec(string $sql): int;
    
    /**
     * Ejemplo de directiva específica: Generar SQL para paginación 
     * que varía entre motores (MySQL vs Oracle/SQLServer).
     */
    public function getLimitSql(int $limit, int $offset = 0): string;
}