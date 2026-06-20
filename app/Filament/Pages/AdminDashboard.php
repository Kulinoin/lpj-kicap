<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;
use Illuminate\Contracts\Support\Htmlable;

class AdminDashboard extends Dashboard
{
    protected static ?string $title = 'Dashboard Kicap Event';

    protected static ?string $navigationLabel = 'Dashboard';

    public function getSubheading(): string | Htmlable | null
    {
        return 'Kontrol ringkas untuk event berjalan, review, dana kegiatan, petugas, dan dokumen LPJ.';
    }

    public function getColumns(): int | array
    {
        return 1;
    }
}
