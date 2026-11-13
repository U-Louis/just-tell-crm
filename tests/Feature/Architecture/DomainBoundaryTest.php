<?php

namespace Tests\Feature\Architecture;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class DomainBoundaryTest extends TestCase
{
    public function test_domain_has_no_http_inertia_eloquent_or_facade_imports(): void
    {
        $forbidden = [
            'Illuminate\\Http',
            'Inertia\\',
            'Illuminate\\Database\\Eloquent',
            'Illuminate\\Support\\Facades',
        ];

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path('Domain')));
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
                    $file->getPathname().' imports '.$import.'. Move the call to app/Http or app/Infrastructure.',
                );
            }
        }

        $this->assertGreaterThan(0, $checked);
    }
}
