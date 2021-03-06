<?php

namespace App\Jobs;

use App\PackageVersionPreview;
use Exception;
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
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function handle()
    {
        $packageVersion = $this->packageVersionPreview->package_version;
        $buildingFromDraft = !in_array($packageVersion->status, ['pending', 'approved'])
            && $packageVersion->progress !== 100;

        $archiveFilename = $packageVersion->package->getFileFriendlyName() . '_' . $packageVersion->version . '.package';
        $exists = Storage::disk('build-temp')->exists($archiveFilename);

        \Log::info('Attempting preview of: ' . $packageVersion->id . ' | checking for file: ' . $archiveFilename);

        if ($buildingFromDraft || !$exists) {
            \Log::info('Filename does not exist or building from draft: ' . $exists . ' | ' . $buildingFromDraft);
            try {
                $buildJob = new BuildPackageFromVersion($packageVersion, null, true);
                $buildJob->handle();
            } catch (Exception $exception) {
                $packageVersion->update([
                    'status' => 'draft',
                    'progress' => 0,
                ]);

                return;
            }
        }

        $previewPath = Str::random(16);

        $this->packageVersionPreview->update([
            'preview_path' => $previewPath,
        ]);

        Storage::disk('local')
            ->makeDirectory('public/previews/'.$previewPath);

        $stream = Storage::disk('build-temp')
            ->getDriver()
            ->readStream($archiveFilename);

        file_put_contents(
            storage_path('app/public/previews/'.$previewPath.'/package.tar.gz'),
            stream_get_contents($stream),
            FILE_APPEND
        );

        if ($buildingFromDraft) {
            $packageVersion->update([
                'status' => 'draft',
                'progress' => 0,
            ]);
        }

        $process = new Process('tar -xf package.tar.gz', storage_path('app/public/previews/'.$previewPath));
        $process->run();

        $this->packageVersionPreview->update([
            'build_complete' => true,
        ]);
    }
}
