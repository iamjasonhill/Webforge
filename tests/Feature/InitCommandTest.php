<?php

describe('Init Command', function () {
    it('displays the init command help', function () {
        $this->artisan('init --help')
            ->assertExitCode(0);
    });

    it('has required options', function () {
        $this->artisan('init --help')
            ->assertExitCode(0)
            ->expectsOutputToContain('--platform')
            ->expectsOutputToContain('--name')
            ->expectsOutputToContain('--with-brain')
            ->expectsOutputToContain('--with-seo');
    });
});
