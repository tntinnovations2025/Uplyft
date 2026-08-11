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
        $file      = $request->file('qualifications_file');

        $result = $this->onboardingService->onboard($validated, $file);

        return response()->json([
            'message'     => 'Teacher onboarding registration completed successfully.',
            'data'        => $result['teacher'],
            'credentials' => $result['credentials'],
        ], 201);
    }
}
