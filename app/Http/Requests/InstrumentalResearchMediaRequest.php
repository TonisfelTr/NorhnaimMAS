<?php

namespace App\Http\Requests;

use App\Models\InstrumentalResearch;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class InstrumentalResearchMediaRequest extends InstrumentalResearchPatientRequest
{
    public function authorize(): bool
    {
        if (!parent::authorize()) {
            return false;
        }

        $instrumentalResearch = $this->route('instrumentalResearch');

        $media = $this->route('media');

        if (!$instrumentalResearch instanceof InstrumentalResearch || !$media instanceof Media) {
            return false;
        }

        return $media->model_type === $instrumentalResearch->getMorphClass()
            && (int) $media->model_id === (int) $instrumentalResearch->id
            && $media->collection_name === InstrumentalResearch::MEDIA_COLLECTION_RESULTS;
    }

    public function rules(): array
    {
        return [];
    }
}
