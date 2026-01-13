<?php

use App\Commands\AuditCommand;

describe('AuditCommand Unit Tests', function () {
    beforeEach(function () {
        $this->command = new AuditCommand;
    });

    it('detects laravel projects by artisan file', function () {
        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('detectProjectType');
        $method->setAccessible(true);

        // Create a temp directory with artisan file
        $tempDir = sys_get_temp_dir().'/webforge-test-'.uniqid();
        mkdir($tempDir);
        file_put_contents($tempDir.'/artisan', '<?php');

        $result = $method->invoke($this->command, $tempDir);
        expect($result)->toBe('laravel');

        // Cleanup
        unlink($tempDir.'/artisan');
        rmdir($tempDir);
    });

    it('detects wordpress projects by wp-config', function () {
        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('detectProjectType');
        $method->setAccessible(true);

        $tempDir = sys_get_temp_dir().'/webforge-test-'.uniqid();
        mkdir($tempDir);
        file_put_contents($tempDir.'/wp-config.php', '<?php');

        $result = $method->invoke($this->command, $tempDir);
        expect($result)->toBe('wordpress');

        unlink($tempDir.'/wp-config.php');
        rmdir($tempDir);
    });

    it('detects astro projects by config file', function () {
        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('detectProjectType');
        $method->setAccessible(true);

        $tempDir = sys_get_temp_dir().'/webforge-test-'.uniqid();
        mkdir($tempDir);
        file_put_contents($tempDir.'/astro.config.mjs', 'export default {};');

        $result = $method->invoke($this->command, $tempDir);
        expect($result)->toBe('astro');

        unlink($tempDir.'/astro.config.mjs');
        rmdir($tempDir);
    });

    it('returns unknown for unrecognized projects', function () {
        $reflection = new ReflectionClass($this->command);
        $method = $reflection->getMethod('detectProjectType');
        $method->setAccessible(true);

        $tempDir = sys_get_temp_dir().'/webforge-test-'.uniqid();
        mkdir($tempDir);

        $result = $method->invoke($this->command, $tempDir);
        expect($result)->toBe('unknown');

        rmdir($tempDir);
    });
});
