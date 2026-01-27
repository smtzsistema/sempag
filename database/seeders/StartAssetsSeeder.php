<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class StartAssetsSeeder extends Seeder
{
    public function run(): void
    {
        $src = resource_path('start/demo20252');
        $dst = storage_path('app/public/demo20252');

        if (!File::exists($src)) {
            $this->command?->warn("Start assets não encontrados em: {$src}");
            return;
        }

        File::ensureDirectoryExists($dst);
        File::copyDirectory($src, $dst);

        $this->command?->info("Assets iniciais copiados: {$src} -> {$dst}");
    }
}
