<?php

namespace Tests\Unit\Concerns;

use App\Concerns\CanFormatCount;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CanFormatCountTest extends TestCase
{
    private TestFormatter $formatter;

    /**
     * @return array<string, array{int, string}>
     */
    public static function countProvider(): array
    {
        return [
            'zero' => [0, '0'],
            'under 1k' => [999, '999'],
            'exactly 1k' => [1000, '1K'],
            '1.2k' => [1234, '1.2K'],
            '999.9k edge case' => [999999, '999.9K'],
            'exactly 1M' => [1000000, '1M'],
            '999.9M edge case' => [999999999, '999.9M'],
            'negative number' => [-5678, '-5.7K'], // Assuming fix is applied
            'large number' => [1234567890, '1.2B'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new TestFormatter;
    }

    #[DataProvider('countProvider')]
    public function test_it_formats_counts_correctly(int $count, string $expected): void
    {
        $this->assertEquals($expected, $this->formatter->format($count));
    }
}

class TestFormatter
{
    use CanFormatCount;

    public function format(int $count): string
    {
        return $this->formatCount($count);
    }
}
