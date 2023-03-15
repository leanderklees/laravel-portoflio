<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\UploadController;

class ClearTemporaryFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:tempfiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old temporary files from filesystem';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $uploadController = new UploadController();
        $uploadController->clearTemporaryFiles();
    }
}
