<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model;

class HealthScoreCalculator
{
    private const BASE_SCORE = 100;
    private const FAILURE_PENALTY = 10;

    /**
     * @param array<int, array{status: bool, message: string}> $results
     */
    public function calculate(array $results): int
    {
        $failures = 0;

        foreach ($results as $result) {
            if (!($result['status'] ?? false)) {
                $failures++;
            }
        }

        $score = self::BASE_SCORE - ($failures * self::FAILURE_PENALTY);

        return max(0, min(self::BASE_SCORE, $score));
    }
}
