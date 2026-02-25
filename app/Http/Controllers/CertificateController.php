<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function show(Request $request, string $uuid)
    {
        $certificate = Certificate::where('uuid', $uuid)
            ->with(['user', 'course', 'enrollment'])
            ->firstOrFail();

        $this->authorize('view', $certificate);

        return view('certificates.show', compact('certificate'));
    }
}
