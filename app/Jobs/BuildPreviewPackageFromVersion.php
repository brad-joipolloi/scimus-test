<?php

namespace App\Jobs;

use App\PackageVersionPreview;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BuildPreviewPackageFromVersion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int Only try to run the job once
     */
    public $tries = 1;

    /**
     * @var PackageVersionPreview
     */
    protected $packageVersionPreview;

    /**
     * Create a new job instance.
     *
     * @param PackageVersionPreview $packageVersionPreview
     */
    public function __construct(PackageVersionPreview $packageVersionPreview)
    {
        $this->packageVersionPreview = $packageVersionPreview;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $packageVersion = $this->packageVersionPreview->package_version;

        $buildJob = new BuildPackageFromVersion($packageVersion, null);
        $buildJob->handle();

        $previewPath = Str::random(16);

        $this->packageVersionPreview->update([
            'preview_path' => $previewPath,
        ]);

        $builtPackage = Storage::disk(config('filesystems.packages'))
            ->get($packageVersion->archive_path);

        Storage::disk('local')
            ->makeDirectory('public/previews/'.$previewPath);

        Storage::disk('local')
            ->put('public/previews/'.$previewPath.'/package.tar.gz', $builtPackage);

        $packageVersion->update([
            'status' => 'draft',
            'progress' => 0,
        ]);

        $process = new Process('tar -zxf package.tar.gz', storage_path('app/public/previews/'.$previewPath));
        $process->run();

        $this->packageVersionPreview->update([
            'build_complete' => true,
        ]);
    }
}
