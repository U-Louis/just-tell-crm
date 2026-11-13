<?php

namespace Tests\Feature\Architecture;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class ControllersDoNotQueryTest extends TestCase
{
    public function test_controllers_do_not_query_eloquent_or_the_database(): void
    {
        $forbidden = [
            'Illuminate\\Database\\Eloquent',
            'DB::',
            '::query(',
        ];

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path('Http/Controllers')));
        $checked = 0;

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $checked++;
            $contents = file_get_contents($file->getPathname());
            $this->assertIsString($contents);
            foreach ($forbidden as $import) {
                $this->assertStringNotContainsString(
                    $import,
                    $contents,
                    $file->getPathname().' contains '.$import.'. Call the action.',
                );
            }
        }

        $this->assertGreaterThan(0, $checked);
    }
}
