<?php

declare(strict_types=1);

namespace Amaresh\CommerceHealthCheck\Model\Alert;

class AlertSubjectResolver
{
    private const SUBJECT_MAP = [
        'redis' => 'Redis Down',
        'cron' => 'Cron Stopped',
        'disk' => 'Disk Full',
        'integration' => 'Integration Failed',
        'database' => 'Database Failed',
        'opensearch' => 'OpenSearch Failed',
        'consumer' => 'Queue Consumers Stopped',
        'indexer' => 'Indexers Invalid',
    ];

    /**
     * @param array<int, array{key: string, component: string, message: string}> $failures
     */
    public function resolveSubject(array $failures): string
    {
        if (count($failures) === 1) {
            return self::SUBJECT_MAP[$failures[0]['key']] ?? 'Commerce Health Alert';
        }

        return 'Commerce Health Alert';
    }

    /**
     * @param array<int, array{key: string, component: string, message: string}> $failures
     */
    public function resolveSlackLines(array $failures): array
    {
        $lines = [];

        foreach ($failures as $failure) {
            $subject = self::SUBJECT_MAP[$failure['key']] ?? $failure['component'];
            $lines[] = sprintf('*%s* — %s', $subject, $failure['message']);
        }

        return $lines;
    }
}
