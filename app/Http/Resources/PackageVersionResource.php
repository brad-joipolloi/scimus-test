<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PackageVersionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $status = $this->status;

        if ($this->kiosks()->count()) {
            $status = 'deployed';
        }

        if (!$this->valid) {
            $status = 'invalid';
        }

        return [
            'id' => $this->id,
            'version' => $this->version,
            'download' => $this->archive_path_exists ? route('api.kiosk.package.download', [$this->package, $this]) : null,
            'status' => $status,
            'valid' => $this->valid,
            'progress' => $this->progress,
            'package_data' => $this->data,
            'package' => [
                'id' => $this->package->id,
                'name' => $this->package->name,
                'slug' => Str::kebab($this->name),
            ],
            'created_at' => (string) $this->created_at,
            'assets' => PackageVersionAssetResource::collection($this->media),
        ];
    }
}
