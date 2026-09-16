<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Applicant;
use App\Models\ExamScore;
use App\Models\ExamSchedule;
use App\Services\Admission\AdmissionEventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EntryEaseController extends Controller
{
    private function debugLog(string $hypothesisId, string $location, string $message, array $data = []): void
    {
        try {
            $payload = json_encode([
                'sessionId' => '0cc008',
                'runId' => 'run4',
                'hypothesisId' => $hypothesisId,
                'location' => $location,
                'message' => $message,
                'data' => $data,
                'timestamp' => (int) floor(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES);

            if ($payload === false) {
                return;
            }

            file_put_contents('C:/xampp/htdocs/deoris/debug-0cc008.log', $payload . PHP_EOL, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // Ignore debug logging failures to avoid affecting SSO flow.
        }
    }

    public function ssoExchange(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token'    => 'required|string|max:500',
            'embedded' => 'sometimes|boolean',
        ]);
        // #region agent log
        $this->debugLog('H8', 'EntryEaseController::ssoExchange:entry', 'module ssoExchange called', [
            'hasToken' => !empty($validated['token']),
            'embedded' => (bool) ($validated['embedded'] ?? false),
            'sessionId' => $request->session()->getId(),
        ]);
        // #endregion

        $portalUrl = rtrim((string) config('app.portal_url', 'https://deoris.test'), '/');
        $response = Http::withHeaders([
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $validated['token'],
        ])->post($portalUrl . '/api/v1/sso/exchange', [
            'token' => $validated['token'],
        ]);
        // #region agent log
        $this->debugLog('H8', 'EntryEaseController::ssoExchange:portalResponse', 'portal exchange response received', [
            'status' => $response->status(),
            'ok' => $response->ok(),
        ]);
        // #endregion

        if (! $response->ok()) {
            // #region agent log
            $this->debugLog('H8', 'EntryEaseController::ssoExchange:invalidToken', 'module exchange rejected token', [
                'status' => $response->status(),
            ]);
            // #endregion
            return response()->json(['success' => false, 'message' => 'Invalid SSO token'], 401);
        }

        $payload = $response->json();
        $user = $payload['user'] ?? $payload['data']['user'] ?? null;
        if (!is_array($user) || empty($user['id'])) {
            // #region agent log
            $this->debugLog('H8', 'EntryEaseController::ssoExchange:invalidPayload', 'module exchange payload missing user id', [
                'hasUserArray' => is_array($user),
            ]);
            // #endregion
            return response()->json(['success' => false, 'message' => 'Invalid SSO response'], 401);
        }

        $newRole = $this->normalizeRole((string) ($user['role'] ?? 'student'));
        $name = (string) ($user['name'] ?? '');
        $email = (string) ($user['email'] ?? '');
        $id = (string) $user['id'];
        $embedded = (bool) ($validated['embedded'] ?? false);

        $request->session()->flush();
        $request->session()->put([
            'sso_id'               => $id,
            'sso_role'             => $newRole,
            'sso_name'             => $name,
            'sso_email'            => $email,
            'sso_embedded'         => $embedded,
            'user'                 => ['id' => $id, 'role' => $newRole, 'name' => $name, 'email' => $email],
            'sso_authenticated_at' => now()->timestamp,
        ]);
        // #region agent log
        $this->debugLog('H8', 'EntryEaseController::ssoExchange:sessionHydrated', 'module session hydrated after exchange', [
            'ssoId' => $id,
            'role' => $newRole,
            'sessionId' => $request->session()->getId(),
        ]);
        // #endregion

        $redirect = match ($newRole) {
            'admin'             => route('admin.dashboard', absolute: false),
            'admission_officer' => route('registrar.dashboard', absolute: false),
            default             => route('student.dashboard', absolute: false),
        };

        return response()->json([
            'success'  => true,
            'redirect' => $redirect,
            'role'     => $newRole,
            'user'     => ['id' => $id, 'role' => $newRole, 'name' => $name, 'email' => $email],
        ]);
    }

    public function index(Request $request)
    {
        // Only flush stale SSO fields if not already authenticated
        // DO NOT regenerate token - it breaks CSRF verification on form submission
        if (!$this->hasValidSsoSession($request)) {
            $request->session()->forget(['sso_role', 'sso_name', 'sso_email', 'sso_id', 'user']);
        }

        return view('entryease');
    }

    private function hasValidSsoSession(Request $request): bool
    {
        return $request->session()->has('sso_id')
            && $request->session()->has('sso_role')
            && $request->session()->has('sso_email');
    }

    public function logout(Request $request)
    {
        $embedded = $request->session()->get('sso_embedded', false);

        // Clear all session data
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('[SSO] User logged out', ['embedded' => $embedded]);

        // Get redirect URL or default to portal
        $redirectUrl = $request->input('redirect') ?? config('app.portal_url', 'https://deoris.test');

        // If in embedded context, return JSON response (module-bridge.js will handle it)
        if ($embedded || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
                'redirect' => $redirectUrl,
                'embedded' => $embedded,
            ]);
        }

        // Standalone mode - redirect to portal
        return redirect($redirectUrl);
    }

    /**
     * Legacy SSO redirect - handles old entryease.js flow
     *
     * @deprecated This method is replaced by the secure token exchange flow in SsoController.
     *
     * The old ssoRedirect flow accepted user identity directly from form parameters
     * after portal token exchange. This is a temporary bridge for legacy entryease.js
     * that hasn't been migrated to /api/sso/exchange yet.
     *
     * This method will be removed once all clients use the new secure token flow.
     *
     * Accepts form data:
     * - role: User role (student, admission_officer, admin)
     * - name: User name
     * - email: User email
     * - id: DEORIS user ID
     * - embedded: Whether in embedded mode
     */
    public function ssoRedirect(Request $request)
    {
        // Log the legacy call
        Log::info('[SSO] Legacy ssoRedirect called - migrate to /api/sso/exchange', [
            'referer' => $request->header('referer'),
            'role' => $request->input('role'),
            'id' => $request->input('id'),
            'accept' => $request->header('accept'),
        ]);

        // Extract and normalize role from form submission
        $newRole = $this->normalizeRole($request->input('role', 'student'));
        $name = $request->input('name', '');
        $email = $request->input('email', '');
        $id = $request->input('id', '');
        $embedded = $request->input('embedded') === '1';
        $wantsJson = $request->wantsJson() || str_contains($request->header('accept') ?? '', 'application/json');
        
        // Check if this is a role change (existing session with different role)
        $previousRole = $request->session()->get('sso_role');
        $isRoleChange = $previousRole && $previousRole !== $newRole;
        
        // Get the referrer URL for role change redirect
        $referrer = $request->header('referer');

        // Create session with SSO context
        $request->session()->put([
            'sso_id' => $id,
            'sso_role' => $newRole,
            'sso_name' => $name,
            'sso_email' => $email,
            'sso_embedded' => $embedded,
            'user' => [
                'id' => $id,
                'role' => $newRole,
                'name' => $name,
                'email' => $email,
            ],
            'sso_authenticated_at' => now()->timestamp,
        ]);

        Log::info('[SSO] Legacy session created', [
            'sso_id' => $id,
            'role' => $newRole,
            'embedded' => $embedded,
            'is_role_change' => $isRoleChange,
        ]);

        // Prepare user data for all responses
        $userData = [
            'id' => $id,
            'role' => $newRole,
            'name' => $name,
            'email' => $email,
        ];

        // If this is a role change and we have a referrer, try to redirect back
        if ($isRoleChange && $referrer) {
            // Parse the referrer URL to ensure it's safe (same host)
            $referrerHost = parse_url($referrer, PHP_URL_HOST);
            $currentHost = $request->getHost();
            
            if ($referrerHost === $currentHost) {
                // Return redirect response for redirecting back to previous page
                return redirect()->away($referrer)->with('message', 'Role changed successfully');
            }
        }

        // Return appropriate dashboard view directly for all roles
        // This avoids middleware session persistence issues with redirects in embedded iframe context
        
        $userData = [
            'id' => $id,
            'role' => $newRole,
            'name' => $name,
            'email' => $email,
        ];

        if ($newRole === 'student') {
            if ($wantsJson) {
                Log::info('[SSO] Returning JSON response for student');
                return response()->json([
                    'redirect' => route('student.dashboard', absolute: false),
                    'role' => $newRole,
                    'user' => $userData,
                ]);
            }

            $applications = Applicant::where('deoris_user_id', $id)
                ->with(['examSchedule.activeQuestions', 'examScore', 'latestExamAttempt'])
                ->latest()
                ->get();

            return view('admission.student.dashboard', [
                'student' => (object) $userData,
                'applications' => $applications
            ]);
        }

        if ($newRole === 'admission_officer') {
            if ($wantsJson) {
                Log::info('[SSO] Returning JSON response for admission_officer');
                return response()->json([
                    'redirect' => route('registrar.dashboard', absolute: false),
                    'role' => $newRole,
                    'user' => $userData,
                ]);
            }

            $stats = [
                'total'          => Applicant::count(),
                'pending'        => Applicant::pending()->count(),
                'under_review'   => Applicant::underReview()->count(),
                'approved'       => Applicant::approved()->count(),
                'rejected'       => Applicant::rejected()->count(),
                'scheduled'      => Applicant::whereNotNull('exam_schedule_id')->count(),
                'scored'         => ExamScore::count(),
                'total_students' => Applicant::distinct('deoris_user_id')->count(),
            ];

            $applications = Applicant::with(['examSchedule', 'examScore'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $examSchedules = ExamSchedule::withCount(['questions', 'applicants'])
                ->withCount(['questions as active_questions_count' => fn($q) => $q->where('is_active', true)])
                ->orderBy('exam_date')
                ->get();

            $userContext = [
                'id'    => $id,
                'name'  => $name,
                'email' => $email,
                'role'  => $newRole,
            ];

            return view('admission.registrar.dashboard', compact(
                'stats', 'applications', 'examSchedules', 'userContext'
            ));
        }

        if ($newRole === 'admin') {
            if ($wantsJson) {
                Log::info('[SSO] Returning JSON response for admin');
                return response()->json([
                    'redirect' => route('admin.dashboard', absolute: false),
                    'role' => $newRole,
                    'user' => $userData,
                ]);
            }

            $stats = [
                'total'          => Applicant::count(),
                'pending'        => Applicant::pending()->count(),
                'under_review'   => Applicant::underReview()->count(),
                'approved'       => Applicant::approved()->count(),
                'rejected'       => Applicant::rejected()->count(),
                'total_students' => Applicant::distinct('deoris_user_id')->count(),
                'exam_schedules' => ExamSchedule::count(),
                'upcoming_exams' => ExamSchedule::upcoming()->count(),
            ];

            $recentActivity = Applicant::with(['examSchedule', 'examScore'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $monthlyData = Applicant::selectRaw("DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as count")
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupByRaw("DATE_FORMAT(created_at, '%b %Y'), DATE_FORMAT(created_at, '%Y%m')")
                ->orderByRaw("DATE_FORMAT(created_at, '%Y%m')")
                ->get()
                ->toArray();

            $approvalRatio = [
                'approved' => $stats['approved'],
                'rejected' => $stats['rejected'],
                'pending'  => $stats['pending'],
            ];

            $userContext = [
                'id'    => $id,
                'name'  => $name,
                'email' => $email,
                'role'  => $newRole,
            ];

            return view('admission.admin.dashboard', compact(
                'stats',
                'recentActivity',
                'monthlyData',
                'approvalRatio',
                'userContext'
            ));
        }

        // Fallback for unknown roles
        Log::warning('[SSO] Unknown role in legacy ssoRedirect', ['role' => $newRole]);
        
        if ($wantsJson) {
            return response()->json(['error' => 'Unknown role'], 403);
        }
        
        return response('Unknown role', 403);
    }

    private function redirectByRole(string $role)
    {
        return match ($role) {
            'admin'             => redirect()->route('admin.dashboard', absolute: false),
            'admission_officer' => redirect()->route('registrar.dashboard', absolute: false),
            default             => redirect()->route('student.dashboard', absolute: false),
        };
    }

    private function normalizeRole(?string $role): string
    {
        return match ($role) {
            'hr', 'registrar', 'admission_officer' => 'admission_officer',
            'admin' => 'admin',
            default => 'student',
        };
    }

    private function getRole(Request $request): string
    {
        return $this->normalizeRole(session('sso_role') ?? 'student');
    }

    private function canEdit(string $role): bool   { return $role === 'admission_officer'; }
    private function canDelete(string $role): bool  { return $role === 'admission_officer'; }
    private function canApprove(string $role): bool { return $role === 'admission_officer'; }

    private function admissionStatusFor(?string $status): string
    {
        return Applicant::admissionStatusFor($status ?? Applicant::STATUS_PENDING);
    }

    public function bootstrap(Request $request): JsonResponse
    {
        $role = $this->getRole($request);
        return response()->json([
            'role'        => $role,
            'applicants'  => Applicant::orderByDesc('created_at')->get()->map(fn($a) => [
                'id'            => $a->id,
                'student_id'    => $a->deoris_user_id,
                'student_name'  => $a->student?->name,
                'student_email' => $a->student?->email,
                'grade_level'   => $a->grade_level,
                'status'        => $a->status,
                'admin_notes'   => $a->admin_notes,
                'created_at'    => $a->created_at?->toIso8601String(),
                'updated_at'    => $a->updated_at?->toIso8601String(),
            ]),
            'activityLog' => ActivityLog::orderByDesc('at')->limit(25)->get()->map(fn($l) => [
                'message' => $l->message,
                'type'    => $l->type,
                'at'      => $l->at?->toIso8601String(),
            ]),
        ]);
    }

    public function listApplicants(Request $request): JsonResponse
    {
        return response()->json(['applicants' => Applicant::orderByDesc('created_at')->get()->map(fn($a) => [
            'id'            => $a->id,
            'student_id'    => $a->deoris_user_id,
            'student_name'  => $a->student?->name,
            'student_email' => $a->student?->email,
            'grade_level'   => $a->grade_level,
            'status'        => $a->status,
            'admin_notes'   => $a->admin_notes,
            'created_at'    => $a->created_at?->toIso8601String(),
            'updated_at'    => $a->updated_at?->toIso8601String(),
        ])]);
    }

    public function getApplicant(Request $request, Applicant $applicant): JsonResponse
    {
        return response()->json([
            'id'              => $applicant->id,
            'student_id'      => $applicant->deoris_user_id,
            'student_name'    => $applicant->student?->name,
            'student_email'   => $applicant->student?->email,
            'grade_level'     => $applicant->grade_level,
            'status'          => $applicant->status,
            'admin_notes'     => $applicant->admin_notes,
            'additional_info' => $applicant->additional_info,
            'created_at'      => $applicant->created_at?->toIso8601String(),
            'updated_at'      => $applicant->updated_at?->toIso8601String(),
        ]);
    }

    public function storeApplicant(Request $request): JsonResponse
    {
        $role = $this->getRole($request);
        if (!$this->canEdit($role)) return response()->json(['message' => 'Unauthorized'], 403);

        $validated = $request->validate([
            'student_id'      => 'required|exists:students,id',
            'grade_level'     => 'required|string|in:Grade 7',
            'additional_info' => 'nullable|string',
            'status'          => 'nullable|in:Pending,Under Review',
        ]);

        $initialStatus = $validated['status'] ?? Applicant::STATUS_PENDING;
        unset($validated['status']);
        $validated['admission_status'] = Applicant::admissionStatusFor($initialStatus);
        $validated['status'] = $initialStatus;

        $applicant = Applicant::create($validated);
        ActivityLog::record("New applicant created: {$applicant->id}", 'blue');
        return response()->json(['message' => 'Applicant created successfully', 'applicant' => $applicant], 201);
    }

    public function updateApplicant(Request $request, Applicant $applicant): JsonResponse
    {
        $role = $this->getRole($request);
        if (!$this->canEdit($role)) return response()->json(['message' => 'Unauthorized'], 403);

        $validated = $request->validate([
            'grade_level'     => 'nullable|string|in:Grade 7',
            'additional_info' => 'nullable|string',
            'admin_notes'     => 'nullable|string',
            'status'          => empty($applicant->nextStatuses())
                ? 'prohibited'
                : 'nullable|in:' . implode(',', $applicant->nextStatuses()),
        ]);

        $oldStatus = $applicant->status;

        if (! empty($validated['status'] ?? null)) {
            try {
                $applicant->transitionTo($validated['status'], (string) session('sso_id'));
            } catch (\InvalidArgumentException $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        }

        unset($validated['status']);

        if (! empty($validated)) {
            $applicant->update($validated);
        }

        $fresh = $applicant->fresh();

        if ($oldStatus !== $fresh->status) {
            ActivityLog::record("Applicant {$fresh->id} status changed to {$fresh->status}", 'amber');

            app(AdmissionEventService::class)->statusChanged(
                $fresh,
                $oldStatus,
                (string) session('sso_id'),
            );
        }

        return response()->json(['message' => 'Applicant updated successfully', 'applicant' => $fresh]);
    }

    public function deleteApplicant(Request $request, Applicant $applicant): JsonResponse
    {
        $role = $this->getRole($request);
        if (!$this->canDelete($role)) return response()->json(['message' => 'Unauthorized'], 403);

        $id = $applicant->id;
        $applicant->delete();
        ActivityLog::record("Applicant {$id} deleted", 'red');
        return response()->json(['message' => 'Applicant deleted successfully']);
    }

    public function getActivityLog(Request $request): JsonResponse
    {
        $log = ActivityLog::orderByDesc('at')->limit((int) $request->query('limit', 25))->get()->map(fn($l) => [
            'id'      => $l->id,
            'message' => $l->message,
            'type'    => $l->type,
            'at'      => $l->at?->toIso8601String(),
        ]);
        return response()->json(['log' => $log]);
    }
}
