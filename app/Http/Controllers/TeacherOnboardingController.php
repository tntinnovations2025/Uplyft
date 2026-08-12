<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherOnboardingRequest;
use App\Services\TeacherOnboardingService;
use Illuminate\Http\JsonResponse;

class TeacherOnboardingController extends Controller
{
    protected TeacherOnboardingService $onboardingService;

    public function __construct(TeacherOnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    /**
     * Handle teacher onboarding registration and secure file upload.
     *
     * @param StoreTeacherOnboardingRequest $request
     * @return JsonResponse
     */
    public function store(StoreTeacherOnboardingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $files = [];
        if ($request->hasFile('matriculation_cert')) $files['matriculation_cert'] = $request->file('matriculation_cert');
        if ($request->hasFile('intermediate_cert')) $files['intermediate_cert'] = $request->file('intermediate_cert');
        if ($request->hasFile('bachelors_cert')) $files['bachelors_cert'] = $request->file('bachelors_cert');
        if ($request->hasFile('masters_cert')) $files['masters_cert'] = $request->file('masters_cert');
        if ($request->hasFile('phd_cert')) $files['phd_cert'] = $request->file('phd_cert');

        $result = $this->onboardingService->onboard($validated, $files);

        return response()->json([
            'message'     => 'Teacher onboarding registration completed successfully.',
            'data'        => $result['teacher'],
            'credentials' => $result['credentials'],
        ], 201);
    }
}
