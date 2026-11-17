<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Solutions\Support\Env;
use Atrium\Solutions\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Setting a variable in the project's .env.
 */
final class EnvTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        parent::setUp();

        $this->file = tempnam(sys_get_temp_dir(), 'env');
    }

    protected function tearDown(): void
    {
        chmod($this->file, 0600);
        unlink($this->file);

        parent::tearDown();
    }

    /**
     * @return array<string,array{string,string,string,string}>
     */
    public static function writes(): array
    {
        return [
            'a line of its own is replaced' => ["A=1\nKEY=old\nB=2\n", 'KEY', 'new', "A=1\nKEY=new\nB=2\n"],
            'only the whole name matches' => ["MY_KEY=1\nKEY_2=2\n", 'KEY', 'new', "MY_KEY=1\nKEY_2=2\nKEY=new\n"],
            'a missing line is added at the end' => ["A=1\n", 'KEY', 'new', "A=1\nKEY=new\n"],
            'blank lines at the end are not piled up' => ["A=1\n\n\n", 'KEY', 'new', "A=1\nKEY=new\n"],
            'an empty file gets the line alone' => ['', 'KEY', 'new', "KEY=new\n"],
            'a name with spaces is quoted' => ['', 'APP_NAME', 'Whitaker Brennan LLP', "APP_NAME=\"Whitaker Brennan LLP\"\n"],
            'a quote is escaped' => ['', 'APP_NAME', 'The "Best" firm', "APP_NAME=\"The \\\"Best\\\" firm\"\n"],
            'a backslash is escaped' => ['', 'APP_NAME', 'A\\B C', "APP_NAME=\"A\\\\B C\"\n"],
            'a dollar sign stays in a replaced line' => ["KEY=old\n", 'KEY', 'a$1 b', "KEY=\"a\$1 b\"\n"],
            'a backslash stays in a replaced line' => ["KEY=old\n", 'KEY', 'a\\1 b', "KEY=\"a\\\\1 b\"\n"],
            'plain characters are not quoted' => ['', 'KEY', 'a/b:c.d-e_f9', "KEY=a/b:c.d-e_f9\n"],
            'an empty value is not quoted' => ["KEY=old\n", 'KEY', '', "KEY=\n"],
            'Unicode is quoted and kept' => ['', 'KEY', 'Anwälte', "KEY=\"Anwälte\"\n"],
        ];
    }

    #[DataProvider('writes')]
    public function test_it_writes_the_line(string $before, string $key, string $value, string $after): void
    {
        file_put_contents($this->file, $before);

        $this->assertTrue(Env::set($key, $value, $this->file));
        $this->assertSame($after, file_get_contents($this->file));
    }

    public function test_without_a_file_given_it_writes_the_project_s_env(): void
    {
        file_put_contents($this->file, "A=1\n");
        $this->app->loadEnvironmentFrom(basename($this->file));
        $this->app->useEnvironmentPath(dirname($this->file));

        $this->assertTrue(Env::set('KEY', 'new'));
        $this->assertSame("A=1\nKEY=new\n", file_get_contents($this->file));
    }

    public function test_a_file_that_is_not_there_is_not_created(): void
    {
        $missing = $this->file.'.missing';

        $this->assertFalse(Env::set('KEY', 'new', $missing));
        $this->assertFileDoesNotExist($missing);
    }

    public function test_a_file_that_cannot_be_written_is_left_alone(): void
    {
        file_put_contents($this->file, "A=1\n");
        chmod($this->file, 0400);

        $this->assertFalse(Env::set('KEY', 'new', $this->file));
        $this->assertSame("A=1\n", file_get_contents($this->file));
    }

    public function test_a_directory_is_not_a_file(): void
    {
        $this->assertFalse(Env::set('KEY', 'new', sys_get_temp_dir()));
    }

    public function test_a_name_is_matched_as_written_and_not_as_a_pattern(): void
    {
        file_put_contents($this->file, "AxB=1\nA.B=2\n");

        $this->assertTrue(Env::set('A.B', 'new', $this->file));
        $this->assertSame("AxB=1\nA.B=new\n", file_get_contents($this->file));

        file_put_contents($this->file, "AxB=1\n");

        $this->assertTrue(Env::set('A.B', 'new', $this->file));
        $this->assertSame("AxB=1\nA.B=new\n", file_get_contents($this->file));
    }

    public function test_a_file_that_can_be_written_but_not_read_is_left_as_it_is(): void
    {
        file_put_contents($this->file, "A=1\n");
        chmod($this->file, 0200);

        $this->assertFalse(Env::set('KEY', 'new', $this->file));

        chmod($this->file, 0600);
        $this->assertSame("A=1\n", file_get_contents($this->file));
    }
}
