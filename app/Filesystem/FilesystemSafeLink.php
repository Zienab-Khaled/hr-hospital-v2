<?php

namespace App\Filesystem;

use Illuminate\Filesystem\Filesystem as BaseFilesystem;

/**
 * يتجنب استدعاء exec() عندما تكون معطّلة على الاستضافة (مثل Hostinger).
 * عند تشغيل storage:link إن لم يكن symlink() ولا exec() متاحين، نرجع true بدل استدعاء exec() فيسبب خطأ.
 */
class FilesystemSafeLink extends BaseFilesystem
{
    /**
     * @param  string  $target
     * @param  string  $link
     * @return bool|null
     */
    public function link($target, $link)
    {
        if (! windows_os()) {
            if (function_exists('symlink')) {
                return symlink($target, $link);
            }
            if (! function_exists('exec')) {
                return true;
            }
            return exec('ln -s '.escapeshellarg($target).' '.escapeshellarg($link)) !== false;
        }

        $mode = $this->isDirectory($target) ? 'J' : 'H';
        if (function_exists('exec')) {
            exec("mklink /{$mode} ".escapeshellarg($link).' '.escapeshellarg($target));
        }
        return null;
    }
}
