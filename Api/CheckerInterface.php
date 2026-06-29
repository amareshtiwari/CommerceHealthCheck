<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Api;

interface CheckerInterface
{
    /**
     * Run a single health check.
     *
     * @return array{status: bool, message: string}
     */
    public function execute(): array;
}
