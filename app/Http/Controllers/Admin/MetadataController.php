<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Metadata\UpdateMetadataRequest;
use Illuminate\Http\Request;

class MetadataController
{
    public function update(UpdateMetadataRequest $request)
    {
        staff_aborts_permission('admin.manage_metadata');
        $model = $request->model;
        $modelId = $request->model_id;
        $metadata = $request->validated();
        if (class_exists($model)) {
            $model = $model::find($modelId);
            if ($model && method_exists($model, 'syncMetadata')) {
                $model->syncMetadata(array_combine($metadata['metadata_key'] ?? [], $metadata['metadata_value'] ?? []));
                return back()->with('success',__('admin.metadata.updated'));
            }
            return back()->with('error','Model not found');
        }
    }
}
