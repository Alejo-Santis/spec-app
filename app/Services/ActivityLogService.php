<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogService
{
    public function __construct(private readonly Request $request) {}

    public function log(
        string $action,
        string $module,
        string $description,
        ?int $subjectId = null,
        ?string $subjectLabel = null,
        ?array $properties = null,
    ): ActivityLog {
        return ActivityLog::create([
            'user_id'       => auth()->id(),
            'action'        => $action,
            'module'        => $module,
            'subject_id'    => $subjectId,
            'subject_label' => $subjectLabel,
            'description'   => $description,
            'properties'    => $properties,
            'ip_address'    => $this->request->ip(),
        ]);
    }
}
