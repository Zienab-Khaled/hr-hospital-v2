<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * بديل آمن عندما exec/symlink معطّلين على الاستضافة (مثل Hostinger).
 * تشغيل: php artisan storage:link-safe — يوضح أن المشروع يخدم الملفات عبر رووت ولا يحتاج تشغيل storage:link.
 */
class StorageLinkCommand extends Command
{
    protected $signature = 'storage:link-safe';

    protected $description = 'Show storage fallback info (use this on server instead of storage:link when symlink/exec are disabled)';

    public function handle(Filesystem $files): int
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (file_exists($link)) {
            $this->info('The [public/storage] link already exists.');
            return self::SUCCESS;
        }

        if (! is_dir($target)) {
            $files->makeDirectory($target, 0755, true);
        }

        $canSymlink = function_exists('symlink');
        $canExec = function_exists('exec');
        if (! $canSymlink && ! $canExec) {
            $this->warn('Symlink and exec() are disabled on this server (e.g. Hostinger).');
            $this->line('This project serves storage files via a route — no symlink needed.');
            $this->line('Ensure storage/app/public exists and has correct permissions (e.g. 755).');
            $this->newLine();
            $this->info('OK — you can ignore this command. Uploaded files (signatures, logo) will work.');
            return self::SUCCESS;
        }

        try {
            $files->link($target, $link);
            $this->info('The [public/storage] link has been connected to [storage/app/public].');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->warn('Could not create symlink: ' . $e->getMessage());
            $this->line('This project can serve storage via route instead — see docs/STORAGE-LINK-HOSTINGER.md');
            return self::SUCCESS;
        }
    }
}
