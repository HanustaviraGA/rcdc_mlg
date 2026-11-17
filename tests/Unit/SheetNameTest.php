<?php

namespace Tests\Unit;

use App\Services\Imports\SheetName;
use PHPUnit\Framework\TestCase;

class SheetNameTest extends TestCase
{
    public function test_exact_name_wins_then_the_one_sheet_with_the_same_trimmed_name(): void
    {
        $this->assertSame(1, SheetName::find(['Data All ', 'Malang ', 'Sheet1'], 'Malang '));
        $this->assertSame(1, SheetName::find(['Data All ', 'Malang ', 'Sheet1'], 'Malang'));
        $this->assertSame(0, SheetName::find(['Malang', 'Malang '], 'Malang'));
        $this->assertSame(2, SheetName::find([2 => "\u{00A0}KPI\t"], 'KPI'));
    }

    public function test_missing_or_ambiguous_trimmed_names_are_not_found(): void
    {
        $this->assertFalse(SheetName::find(['Malang ', 'Sheet1'], 'malang'));
        $this->assertFalse(SheetName::find(['Malang ', ' Malang'], 'Malang'));
        $this->assertFalse(SheetName::find(['Malang '], 'Detail'));
    }
}
