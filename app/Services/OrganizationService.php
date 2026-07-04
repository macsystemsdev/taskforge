<?php

namespace App\Services;

use App\Data\Organizations\CreateOrganizationData;
use App\Events\OrganizationCreated;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

class OrganizationService
{


    public function create(CreateOrganizationData $data): Organization
    {
        return DB::transaction(function () use ($data) {

        $slug = $data->slug ?? str($data->name)->slug();
            $organization = Organization::create([
                'owner_id' => $data->owner_id,
                'name' => $data->name,
                'slug' => $slug,
            ]);

            event(new OrganizationCreated($organization));

            return $organization;
        });
    }
}
