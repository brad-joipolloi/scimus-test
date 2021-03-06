<?php

namespace App\Http\Controllers\Api;

use App\Filters\ClientOutdatedKioskFilter;
use App\Filters\UnregisteredKioskFilter;
use App\Filters\UnseenKioskLogErrorFilter;
use App\Http\Requests\KioskDestroyRequest;
use App\Http\Requests\KioskHealthCheckRequest;
use App\Http\Requests\KioskIndexRequest;
use App\Http\Requests\KioskPackageDownloadRequest;
use App\Http\Requests\KioskRegisterRequest;
use App\Http\Requests\KioskShowLogsRequest;
use App\Http\Requests\KioskShowRequest;
use App\Http\Requests\KioskUpdateRequest;
use App\Http\Resources\KioskLogsResource;
use App\Http\Resources\KioskResource;
use App\Jobs\ProcessKioskLogs;
use App\Kiosk;
use App\KioskLog;
use App\Package;
use App\PackageVersion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\Filter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Class KioskController
 * @package App\Http\Controllers\Api
 * @resource Kiosk
 */
class KioskController extends Controller
{
    /**
     * @param KioskIndexRequest $request
     * @return mixed
     */
    public function index(KioskIndexRequest $request) : ResourceCollection
    {
        $kiosks = QueryBuilder::for(Kiosk::class)
            ->orderByDesc('last_seen_at')
            ->allowedFilters([
                'name',
                'location',
                'asset_tag',
                'current_package',
                Filter::custom('registered', UnregisteredKioskFilter::class),
                Filter::custom('unseen_errors', UnseenKioskLogErrorFilter::class),
                Filter::custom('client_outdated', ClientOutdatedKioskFilter::class),
            ]);

        if(isset($request->showAll)) { 
            $kiosks = $kiosks->jsonPaginate(500, 500);
        } else {
            $kiosks = $kiosks->jsonPaginate();
        }

        return KioskResource::collection($kiosks);
    }

    /**
     * @param KioskShowRequest $request
     * @param Kiosk $kiosk
     * @return KioskResource
     */
    public function show(KioskShowRequest $request, Kiosk $kiosk) : KioskResource
    {
        return new KioskResource($kiosk);
    }

    /**
     * @param KioskShowLogsRequest $request
     * @param Kiosk $kiosk
     * @return ResourceCollection
     */
    public function showLogs(KioskShowLogsRequest $request, Kiosk $kiosk) : ResourceCollection
    {
        $kioskLogs = QueryBuilder::for(KioskLog::class)
            ->where('kiosk_id', '=', $kiosk->id)
            ->orderByDesc('timestamp')
            ->allowedFilters([
                'level',
                'timestamp',
            ])
            ->jsonPaginate()
        ;

        $kioskLogs->each(function (KioskLog $kioskLog) use ($request) {
            if (! $kioskLog->seen_by_user) {
                $kioskLog->seen_by_user()->associate($request->user());
                $kioskLog->save();
            }
        });

        return KioskLogsResource::collection($kioskLogs);
    }

    /**
     * @param KioskUpdateRequest $request
     * @param Kiosk $kiosk
     * @return KioskResource
     */
    public function update(KioskUpdateRequest $request, Kiosk $kiosk) : KioskResource
    {
        $kiosk->update([
            'name' => $request->input('name'),
            'location' => $request->input('location'),
            'asset_tag' => $request->input('asset_tag'),
        ]);

        if (
            $request->has('assigned_package_version')
            && $request->user()->can('deploy packages to all kiosks')
        ) {
            if ($request->input('assigned_package_version')) {
                $kiosk->assigned_package_version()->associate(PackageVersion::find($request->input('assigned_package_version')));
            } else {
                $kiosk->assigned_package_version()->dissociate();
            }
        }

        if ($request->has('manually_set')) {
            $kiosk->manually_set_at = $request->input('manually_set');
        }

        $kiosk->save();

        return new KioskResource($kiosk);
    }

    /**
     * @param KioskDestroyRequest $request
     * @param Kiosk $kiosk
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(KioskDestroyRequest $request, Kiosk $kiosk)
    {
        $kiosk->delete();

        return response('', 204);
    }

    /**
     * @param KioskHealthCheckRequest $request
     * @return KioskResource
     */
    public function healthCheck(KioskHealthCheckRequest $request) : KioskResource
    {
        // version check
        if(preg_replace('/\./', '',  $request->input('client.version')) < 77) {
            die();
        }
        $kiosk = $this->getKioskFromRequest($request);

        /**
         * Set the manually set timestamp to null if:
         *  request manually set timestamp is null
         * OR
         *  the server side updated_at is more recent than the request
         *  manually_set timestamp and the server side manually_set
         *  timestamp is null
         */
        if ($request->input('running_package.manually_set') === null) {
            $kiosk->manually_set_at = null;
        }

        if (
            $kiosk->manually_set_at === null &&
            $kiosk->updated_at->timestamp > $request->input('running_package.manually_set')
        ) {
            $kiosk->manually_set_at = null;
        } else {
            $kiosk->manually_set_at = $request->input('running_package.manually_set');
        }

        $kiosk->last_seen_at = now();
        $kiosk->client_version = $request->input('client.version');
        $kiosk->current_package = $request->input('running_package.name') . '@' . $request->input('running_package.version');

        $kiosk->save();

        // queue the logs for insertion to reduce load
        if ($request->input('logs')) {      
            $this->dispatch(new ProcessKioskLogs($kiosk, $request->input('logs')));
        }

        return new KioskResource($kiosk);
    }

    /**
     * @param KioskRegisterRequest $request
     * @return KioskResource
     */
    public function register(KioskRegisterRequest $request) : KioskResource
    {
        $kiosk = Kiosk::create([
            'identifier' => $request->input('identifier'),
            'last_seen_at' => now(),
            'client_version' => $request->input('client.version'),
        ]);

        return new KioskResource($kiosk);
    }

    public function download(KioskPackageDownloadRequest $request, Package $package, PackageVersion $packageVersion)
    {
        $kiosk = $this->getKioskFromRequest($request);

        if ($packageVersion->archive_path_exists) {
            return Storage::disk(config('filesystems.packages'))->download($packageVersion->archive_path);
        }

        return abort(404);
    }

    /**
     * @param Request $request
     * @return Kiosk
     */
    private function getKioskFromRequest(Request $request) : Kiosk
    {
        $kiosk = Kiosk::whereIdentifier($request->input('identifier'))
            ->firstOrFail()
        ;

        return $kiosk;
    }
}
