<?php

namespace App\Console\Commands\Automation;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-cache {folder} {url?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear cached HTML files. Provide a folder (e.g. match_html) and optionally a URL to clear only that cache.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $folder = $this->argument('folder');
        $url = $this->argument('url');

        $cachePath = storage_path("app/cached/automation/{$folder}");

        if (!File::exists($cachePath)) {
            $this->error("Cache folder does not exist: $cachePath");
            return 1;
        }

        if ($url) {
            // Generate base key for this URL
            $baseKey = md5($url);
            $files = glob("{$cachePath}/{$baseKey}_exp*.html");

            if (empty($files)) {
                $this->info("No cache found for URL: $url in {$folder}");
                return 1;
            }

            foreach ($files as $file) {
                File::delete($file);
                $this->info("Deleted cache file: $file");
            }
        } else {
            File::cleanDirectory($cachePath);
            $this->info("All cache cleared from: $cachePath");
        }

        return 0;
    }
}
