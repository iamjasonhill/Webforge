<?php

describe('Audit Command', function () {
    it('displays the audit command help', function () {
        $this->artisan('audit --help')
            ->assertExitCode(0)
            ->expectsOutputToContain('Audit a project');
    });

    it('can audit a path', function () {
        $this->artisan('audit', ['path' => base_path()])
            ->assertExitCode(0)
            ->expectsOutputToContain('WebForge Audit')
            ->expectsOutputToContain('Score:');
    });

    it('detects node project type for webforge itself', function () {
        $this->artisan('audit', ['path' => base_path()])
            ->assertExitCode(0)
            ->expectsOutputToContain('node');
    });

    it('supports seo-only audit flag', function () {
        $this->artisan('audit', ['path' => base_path(), '--seo' => true])
            ->assertExitCode(0)
            ->expectsOutputToContain('Score:');
    });

    it('supports performance-only audit flag', function () {
        $this->artisan('audit', ['path' => base_path(), '--performance' => true])
            ->assertExitCode(0)
            ->expectsOutputToContain('Score:');
    });

    it('supports accessibility-only audit flag', function () {
        $this->artisan('audit', ['path' => base_path(), '--accessibility' => true])
            ->assertExitCode(0)
            ->expectsOutputToContain('Score:');
    });
});
