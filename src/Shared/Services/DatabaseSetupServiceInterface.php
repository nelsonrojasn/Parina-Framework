<?php
declare(strict_types=1);
namespace Parina\Shared\Services;

interface DatabaseSetupServiceInterface
{
    public function setupDatabase(): void;
}
