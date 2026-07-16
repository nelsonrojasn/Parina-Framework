<?php
declare(strict_types=1);

namespace Parina\Shared\Services\SchemaCompilers;

abstract class AbstractSchemaCompiler
{
    protected array $creationOrder;
    protected array $dropOrder;
    protected array $tables;
    protected array $foreignKeys;
    protected bool $injectUserSeed;

    public function __construct(
        array $creationOrder,
        array $dropOrder,
        array $tables,
        array $foreignKeys,
        bool $injectUserSeed
    ) {
        $this->creationOrder = $creationOrder;
        $this->dropOrder = $dropOrder;
        $this->tables = $tables;
        $this->foreignKeys = $foreignKeys;
        $this->injectUserSeed = $injectUserSeed;
    }

    abstract public function compile(): string;

    protected function formatDefaultValue(?string $val, string $type): ?string
    {
        if ($val === null) {
            return null;
        }

        $upper = strtoupper($val);
        if ($upper === 'NULL' || $upper === 'CURRENT_TIMESTAMP') {
            return $upper;
        }

        // If numeric type and numeric default, keep as is
        $typeLower = strtolower($type);
        $baseType = preg_match('/^([a-z]+)/', $typeLower, $m) ? $m[1] : $typeLower;
        if (in_array($baseType, ['int', 'integer', 'tinyint', 'smallint', 'bigint', 'decimal', 'numeric', 'float', 'real']) && is_numeric($val)) {
            return $val;
        }

        // If already quoted, keep as is
        if ((str_starts_with($val, "'") && str_ends_with($val, "'")) || (str_starts_with($val, '"') && str_ends_with($val, '"'))) {
            return $val;
        }

        // Wrap string literals in single quotes
        return "'" . str_replace("'", "''", $val) . "'";
    }
}
