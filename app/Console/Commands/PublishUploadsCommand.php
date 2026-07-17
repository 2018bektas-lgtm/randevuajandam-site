<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * storage/app/public/uploads → public/uploads kopyala (symlink olmadan 404 çözümü).
 *
 *   php artisan uploads:publish
 *   php artisan uploads:publish --force
 */
class PublishUploadsCommand extends Command
{
    protected $signature = 'uploads:publish
        {--force : Hedefte dosya varsa üzerine yaz}
        {--dry-run : Sadece raporla, kopyalama}';

    protected $description = 'Eski storage/app/public/uploads dosyalarını public/uploads altına kopyalar (profil 404 fix)';

    public function handle(): int
    {
        $from = storage_path('app/public/uploads');
        $to = public_path('uploads');

        if (! is_dir($from)) {
            $this->warn("Kaynak yok: {$from}");
            $this->line('Yeni yüklemeler zaten public/uploads altına gidecek (disk root = public).');

            return self::SUCCESS;
        }

        // public/uploads bir symlink ise kaldır (bozuk link 404 yapar)
        if (is_link($to) || (file_exists($to) && ! is_dir($to))) {
            $this->warn("public/uploads link/dosya kaldırılıyor: {$to}");
            if (! $this->option('dry-run')) {
                @unlink($to);
            }
        }

        if (! is_dir($to) && ! $this->option('dry-run')) {
            File::makeDirectory($to, 0755, true);
        }

        $copied = 0;
        $skipped = 0;
        $missing = 0;

        $files = File::allFiles($from);
        $this->info('Kaynak dosya sayısı: '.count($files));

        foreach ($files as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $dest = $to.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);

            if (is_file($dest) && ! $this->option('force')) {
                $skipped++;
                continue;
            }

            if ($this->option('dry-run')) {
                $this->line("  [dry-run] {$relative}");
                $copied++;
                continue;
            }

            $dir = dirname($dest);
            if (! is_dir($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            if (@copy($file->getPathname(), $dest)) {
                $copied++;
            } else {
                $missing++;
                $this->error("Kopyalanamadı: {$relative}");
            }
        }

        $this->table(
            ['Sonuç', 'Adet'],
            [
                ['Kopyalandı / planlandı', $copied],
                ['Atlandı (zaten var)', $skipped],
                ['Hata', $missing],
            ]
        );

        $this->info('Hedef: '.$to);
        $this->line('Test: '.rtrim((string) config('app.url'), '/').'/uploads/profil/...');

        return $missing > 0 ? self::FAILURE : self::SUCCESS;
    }
}
