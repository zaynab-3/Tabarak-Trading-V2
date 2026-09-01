<?php

namespace App\Console\Commands;

use App\Actions\Media\OptimizeStoredMedia;
use App\Models\Media;
use Illuminate\Console\Command;

class OptimizeMedia extends Command
{
    protected $signature = 'media:optimize';

    protected $description = 'Optimize catalogue media that was stored before automatic optimization was enabled';

    public function handle(OptimizeStoredMedia $optimize): int
    {
        $processed = 0;
        $optimized = 0;
        $bytesSaved = 0;

        Media::query()->whereNull('optimized_at')->orderBy('id')->eachById(
            function (Media $media) use ($optimize, &$processed, &$optimized, &$bytesSaved): void {
                $result = $optimize->handle($media);
                $processed += $result['processed'] ? 1 : 0;
                $optimized += $result['optimized'] ? 1 : 0;
                $bytesSaved += $result['bytes_saved'];
            },
            count: 25,
        );

        $this->info(sprintf(
            'Processed %d images; reduced %d files; saved %s.',
            $processed,
            $optimized,
            $this->formatBytes($bytesSaved),
        ));

        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) return $bytes.' B';
        if ($bytes < 1024 * 1024) return number_format($bytes / 1024, 1).' KB';

        return number_format($bytes / (1024 * 1024), 1).' MB';
    }
}
