<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test; 
use Tests\TestCase;

class SupabaseCompatibilityTest extends TestCase
{
    #[Test]
    public function it_avoids_mysql_only_advanced_sql_when_using_postgres(): void
    {
        $path = base_path('database/migrations/2026_05_18_000001_add_advanced_sql_features.php');
        $this->assertFileExists($path);

        $content = file_get_contents($path);

        $this->assertNotFalse($content);
        $this->assertStringContainsString("if (\$driver === 'pgsql')", $content);
        $this->assertStringContainsString("if (\$driver !== 'sqlite')", $content);
        $this->assertStringNotContainsString('DROP PROCEDURE IF EXISTS', $content);
        $this->assertStringNotContainsString('CREATE TRIGGER', $content);
    }
}
