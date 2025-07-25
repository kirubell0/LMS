<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class InstallPdfFonts extends Command
{
    protected $signature = 'pdf:install-fonts';
    protected $description = 'Install additional fonts for PDF generation with better language support';

    public function handle()
    {
        $this->info('Installing PDF fonts for better language support...');
        
        // Create fonts directory if it doesn't exist
        $fontsPath = storage_path('fonts');
        if (!file_exists($fontsPath)) {
            mkdir($fontsPath, 0755, true);
            $this->info('Created fonts directory: ' . $fontsPath);
        }

        // Download and install Noto Sans fonts for better Unicode support
        $this->installNotoFonts($fontsPath);
        
        $this->info('Font installation completed!');
        $this->info('You can now use fonts like "Noto Sans" in your PDF templates for better language support.');
    }

    private function installNotoFonts($fontsPath)
    {
        $fonts = [
            'NotoSans-Regular.ttf' => 'https://github.com/googlefonts/noto-fonts/raw/main/hinted/ttf/NotoSans/NotoSans-Regular.ttf',
            'NotoSans-Bold.ttf' => 'https://github.com/googlefonts/noto-fonts/raw/main/hinted/ttf/NotoSans/NotoSans-Bold.ttf',
        ];

        foreach ($fonts as $filename => $url) {
            $fontPath = $fontsPath . '/' . $filename;
            
            if (file_exists($fontPath)) {
                $this->info("Font {$filename} already exists, skipping...");
                continue;
            }

            $this->info("Downloading {$filename}...");
            
            try {
                $fontContent = file_get_contents($url);
                if ($fontContent !== false) {
                    file_put_contents($fontPath, $fontContent);
                    $this->info("Successfully installed {$filename}");
                } else {
                    $this->error("Failed to download {$filename}");
                }
            } catch (\Exception $e) {
                $this->error("Error downloading {$filename}: " . $e->getMessage());
            }
        }
    }
}