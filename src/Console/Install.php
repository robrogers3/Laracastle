<?php

namespace robrogers3\Laracastle\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laracastle:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automates the install of Laracastle';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('Warning! This clobbers the App\User class, App\ServicesProvidersAppServiceProvider class, and default layout file!');

        if (!$this->confirm('Do you wish to continue?')) {
            $this->info("Playing it safe with the manual install; makes sense.");
            return;
        }

        $tos = [];
        $from = __DIR__ . '/../../resources/stubs/User.php';
        $to = app_path('User.php');
        $tos[] = $to;
        File::copy($from,$to);
        $from = __DIR__ . '/../../resources/stubs/AppServiceProvider.php';
        $to = app_path('Providers/AppServiceProvider.php');
        $tos[] = $to;
        File::copy($from,$to);
        $from = __DIR__ . '/../../resources/stubs/app.blade.php';
        $to = resource_path('views/layouts/app.blade.php');
        $tos[] = $to;
        File::copy($from,$to);
        $this->comment('Copied these files: ' . collect($tos)->map('basename')->join(', ') . ' to your app.');
        $this->comment('Great Success!');
        $this->info('Note, you will still need to update your .env files');

    }
}
