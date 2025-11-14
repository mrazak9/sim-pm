<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ButirAkreditasi;

echo "=== BUTIR AKREDITASI DATABASE CHECK ===\n\n";

echo "Total: " . ButirAkreditasi::count() . " butir\n";
echo "Templates: " . ButirAkreditasi::templatesOnly()->count() . " butir\n\n";

echo "Per Instrumen:\n";
echo "- BANPT 4.0: " . ButirAkreditasi::where('instrumen', '4.0')->count() . " butir\n";
echo "- BANPT 9.0: " . ButirAkreditasi::where('instrumen', '9.0')->count() . " butir\n";
echo "- LAMEMBA: " . ButirAkreditasi::where('instrumen', 'LAMEMBA')->count() . " butir\n";
echo "- LAMINFOKOM: " . ButirAkreditasi::where('instrumen', 'LAMINFOKOM')->count() . " butir\n\n";

echo "Sample LAMEMBA (5 first):\n";
$lamemba = ButirAkreditasi::where('instrumen', 'LAMEMBA')->take(5)->get();
foreach ($lamemba as $butir) {
    echo "  - [{$butir->kode}] {$butir->nama}\n";
}

echo "\nSample LAMINFOKOM (5 first):\n";
$laminfokom = ButirAkreditasi::where('instrumen', 'LAMINFOKOM')->take(5)->get();
foreach ($laminfokom as $butir) {
    echo "  - [{$butir->kode}] {$butir->nama}\n";
}

echo "\n=== END ===\n";
