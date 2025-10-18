<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\ProviderRequest;
use App\Models\ServiceRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

/**
 * ValidatePackageStep Middleware
 * 
 * Ensures that users cannot proceed to the next step without completing prerequisites
 */
class ValidatePackageStep
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $step): Response
    {
        $packageId = $request->route('package') ? $request->route('package')->id : $request->get('package_id');
        
        Log::info('ValidatePackageStep: Starting validation', [
            'step' => $step,
            'packageId' => $packageId,
            'url' => $request->url(),
            'method' => $request->method(),
            'user_id' => auth()->id(),
        ]);
        
        // Skip validation for create routes without existing package
        if (!$packageId) {
            Log::info('ValidatePackageStep: No package ID found, skipping validation');
            return $next($request);
        }
        
        $package = Package::find($packageId);
        if (!$package) {
            Log::error('ValidatePackageStep: Package not found', ['packageId' => $packageId]);
            return redirect()->route('b2b.travel-agent.packages.index')
                ->with('error', 'Package not found.');
        }
        
        Log::info('ValidatePackageStep: Package found', [
            'package_name' => $package->name,
            'package_id' => $package->id,
            'creator_id' => $package->creator_id
        ]);
        
        switch ($step) {
            case 'step-2':
                Log::info('ValidatePackageStep: Validating step-2 (basic info)');
                // Step 2: Basic info must be complete
                if (!$this->validateStep1($package)) {
                    Log::warning('ValidatePackageStep: Step 1 validation failed', [
                        'name' => $package->name,
                        'type' => $package->type,
                        'duration' => $package->duration,
                        'max_participants' => $package->max_participants
                    ]);
                    return redirect()->route('b2b.travel-agent.packages.create')
                        ->with('error', 'Please complete Step 1 first.');
                }
                Log::info('ValidatePackageStep: Step 1 validation passed');
                break;
                
            case 'step-3':
                Log::info('ValidatePackageStep: Validating step-3 (itinerary)');
                // Step 3: Itinerary must be complete
                if (!$this->validateStep2($package)) {
                    Log::warning('ValidatePackageStep: Step 2 validation failed', [
                        'has_activities' => !empty($package->activities) && is_array($package->activities) && count($package->activities) > 0,
                        'description_length' => strlen($package->description ?? '')
                    ]);
                    return redirect()->route('b2b.travel-agent.packages.create-step2')
                        ->with('error', 'Please complete Step 2 first.');
                }
                Log::info('ValidatePackageStep: Step 2 validation passed');
                break;
                
            case 'step-4':
                Log::info('ValidatePackageStep: Validating step-4 (provider approvals)', [
                    'user_id' => auth()->id(),
                    'package_id' => $package->id
                ]);
                // Step 4: Providers must be selected and approved
                if (!$this->validateStep3($package)) {
                    Log::warning('ValidatePackageStep: Step 3 validation failed - redirecting back to step 3');
                    return redirect()->route('b2b.travel-agent.packages.create-step3')
                        ->with('error', 'You must have at least one approved provider before proceeding.');
                }
                Log::info('ValidatePackageStep: Step 3 validation passed - allowing access to step 4');
                break;
                
            case 'step-5':
                Log::info('ValidatePackageStep: Validating step-5 (pricing)');
                // Step 5: Pricing must be set
                if (!$this->validateStep4($package)) {
                    Log::warning('ValidatePackageStep: Step 4 validation failed', [
                        'base_price' => $package->base_price
                    ]);
                    return redirect()->route('b2b.travel-agent.packages.create-step4')
                        ->with('error', 'Please complete Step 4 first.');
                }
                Log::info('ValidatePackageStep: Step 4 validation passed');
                break;
        }
        
        Log::info('ValidatePackageStep: All validations passed, proceeding to next middleware/controller');
        return $next($request);
    }
    
    /**
     * Validate Step 1: Basic package information
     */
    private function validateStep1(Package $package): bool
    {
        return $package->name && 
               $package->type && 
               $package->duration && 
               $package->max_participants;
    }
    
    /**
     * Validate Step 2: Itinerary completion
     */
    private function validateStep2(Package $package): bool
    {
        $hasActivities = !empty($package->activities) && is_array($package->activities) && count($package->activities) > 0;
        $hasDetailedDescription = !empty($package->description) && strlen($package->description) > 50;
        
        return $hasActivities || $hasDetailedDescription;
    }
    
    /**
     * Validate Step 3: Provider approval requirements
     */
    private function validateStep3(Package $package): bool
    {
        $userId = auth()->id();
        Log::info('ValidatePackageStep: validateStep3 starting', [
            'package_id' => $package->id,
            'user_id' => $userId
        ]);
        
        // First check new ServiceRequest system
        $serviceRequests = ServiceRequest::where('package_id', $package->id)
            ->where('agent_id', $userId)
            ->get();
        
        Log::info('ValidatePackageStep: ServiceRequest query results', [
            'total_service_requests' => $serviceRequests->count(),
            'service_requests' => $serviceRequests->map(function($sr) {
                return [
                    'id' => $sr->id,
                    'status' => $sr->status,
                    'provider_type' => $sr->provider_type,
                    'agent_id' => $sr->agent_id
                ];
            })->toArray()
        ]);
        
        if ($serviceRequests->isNotEmpty()) {
            // Using new ServiceRequest system
            $approvedServiceRequests = $serviceRequests->where('status', ServiceRequest::STATUS_APPROVED);
            $pendingServiceRequests = $serviceRequests->where('status', ServiceRequest::STATUS_PENDING);
            $rejectedServiceRequests = $serviceRequests->where('status', ServiceRequest::STATUS_REJECTED);
            
            Log::info('ValidatePackageStep: ServiceRequest breakdown', [
                'approved_count' => $approvedServiceRequests->count(),
                'pending_count' => $pendingServiceRequests->count(),
                'rejected_count' => $rejectedServiceRequests->count(),
                'approved_requests' => $approvedServiceRequests->pluck('id')->toArray()
            ]);
            
            if ($approvedServiceRequests->isEmpty()) {
                Log::warning('ValidatePackageStep: No approved service requests found');
                return false; // No approved service providers
            }
            
            // Allow progression if we have at least one approval, even if some are pending
            $result = $approvedServiceRequests->count() > 0;
            Log::info('ValidatePackageStep: ServiceRequest validation result', ['result' => $result]);
            return $result;
        }
        
        // Fallback to old ProviderRequest system for backward compatibility
        $requests = ProviderRequest::where('package_id', $package->id)
            ->where('travel_agent_id', $userId)
            ->get();
        
        Log::info('ValidatePackageStep: ProviderRequest query results', [
            'total_provider_requests' => $requests->count(),
            'provider_requests' => $requests->map(function($pr) {
                return [
                    'id' => $pr->id,
                    'status' => $pr->status,
                    'service_type' => $pr->service_type,
                    'travel_agent_id' => $pr->travel_agent_id
                ];
            })->toArray()
        ]);
        
        if ($requests->isEmpty()) {
            Log::warning('ValidatePackageStep: No providers selected (both ServiceRequest and ProviderRequest are empty)');
            return false; // No providers selected
        }
        
        // Check if at least one request is approved
        $approvedRequests = $requests->where('status', ProviderRequest::STATUS_APPROVED);
        $pendingRequests = $requests->where('status', ProviderRequest::STATUS_PENDING);
        
        Log::info('ValidatePackageStep: ProviderRequest breakdown', [
            'approved_count' => $approvedRequests->count(),
            'pending_count' => $pendingRequests->count(),
            'approved_requests' => $approvedRequests->pluck('id')->toArray()
        ]);
        
        if ($approvedRequests->isEmpty()) {
            Log::warning('ValidatePackageStep: No approved provider requests found');
            return false; // No approved providers
        }
        
        // Allow progression if we have at least one approval, even if some are pending
        // This is configurable business logic - could be changed to require ALL approvals
        $result = $approvedRequests->count() > 0;
        Log::info('ValidatePackageStep: ProviderRequest validation result', ['result' => $result]);
        return $result;
    }
    
    /**
     * Validate Step 4: Pricing completion
     */
    private function validateStep4(Package $package): bool
    {
        return $package->base_price !== null && $package->base_price > 0;
    }
    
    /**
     * Check if package can proceed to publication
     */
    public static function canPublishPackage(Package $package): array
    {
        $validation = [
            'can_publish' => true,
            'errors' => [],
            'warnings' => []
        ];
        
        // Required fields check
        if (!$package->name) {
            $validation['errors'][] = 'Package name is required';
        }
        
        if (!$package->type) {
            $validation['errors'][] = 'Package type is required';
        }
        
        if (!$package->duration || $package->duration <= 0) {
            $validation['errors'][] = 'Package duration must be specified';
        }
        
        if (!$package->base_price || $package->base_price <= 0) {
            $validation['errors'][] = 'Base price must be set';
        }
        
        if (!$package->max_participants || $package->max_participants <= 0) {
            $validation['errors'][] = 'Maximum participants must be specified';
        }
        
        // Provider approval check - Check both new and old systems
        $serviceRequests = ServiceRequest::where('package_id', $package->id)
            ->where('agent_id', $package->travel_agent_id ?? $package->creator_id)
            ->get();
        
        if ($serviceRequests->isNotEmpty()) {
            // Using new ServiceRequest system
            $approvedRequests = $serviceRequests->where('status', ServiceRequest::STATUS_APPROVED);
            $pendingRequests = $serviceRequests->where('status', ServiceRequest::STATUS_PENDING);
            
            if ($approvedRequests->isEmpty()) {
                $validation['errors'][] = 'At least one service provider must approve your request';
            }
            
            if ($pendingRequests->count() > 0) {
                $validation['warnings'][] = "You have {$pendingRequests->count()} pending service requests";
            }
        } else {
            // Fallback to old ProviderRequest system
            $providerRequests = ProviderRequest::where('package_id', $package->id)
                ->where('travel_agent_id', $package->travel_agent_id ?? $package->creator_id)
                ->get();
            
            if ($providerRequests->isEmpty()) {
                $validation['errors'][] = 'At least one service provider must be selected';
            } else {
                $approvedRequests = $providerRequests->where('status', ProviderRequest::STATUS_APPROVED);
                $pendingRequests = $providerRequests->where('status', ProviderRequest::STATUS_PENDING);
                
                if ($approvedRequests->isEmpty()) {
                    $validation['errors'][] = 'At least one provider must approve your request';
                }
                
                if ($pendingRequests->count() > 0) {
                    $validation['warnings'][] = "You have {$pendingRequests->count()} pending provider requests";
                }
            }
        }
        
        // Content completeness check
        if (!$package->description || strlen($package->description) < 100) {
            $validation['warnings'][] = 'Consider adding a more detailed package description';
        }
        
        $hasActivities = !empty($package->activities) && is_array($package->activities) && count($package->activities) > 0;
        if (!$hasActivities) {
            $validation['warnings'][] = 'Consider adding activities to your package itinerary';
        }
        
        if (!$package->main_image) {
            $validation['warnings'][] = 'Adding a main image will make your package more attractive';
        }
        
        // Set overall status
        $validation['can_publish'] = empty($validation['errors']);
        
        return $validation;
    }
    
    /**
     * Debug helper - Get detailed validation info for a package step
     */
    public static function getStepValidationDebug(Package $package, string $step): array
    {
        $middleware = new self();
        $debug = [
            'step' => $step,
            'package_id' => $package->id,
            'package_name' => $package->name,
            'validation_result' => false,
            'details' => []
        ];
        
        switch ($step) {
            case 'step-2':
                $debug['validation_result'] = $middleware->validateStep1($package);
                $debug['details']['basic_info'] = [
                    'name' => !empty($package->name),
                    'type' => !empty($package->type),
                    'duration' => $package->duration > 0,
                    'max_participants' => $package->max_participants > 0
                ];
                break;
                
            case 'step-3':
                $debug['validation_result'] = $middleware->validateStep2($package);
                $debug['details']['itinerary'] = [
                    'has_activities' => !empty($package->activities) && is_array($package->activities) && count($package->activities) > 0,
                    'has_description' => !empty($package->description) && strlen($package->description) > 50
                ];
                break;
                
            case 'step-4':
                $debug['validation_result'] = $middleware->validateStep3($package);
                
                // Check ServiceRequest system (don't filter by agent_id in debug)
                $serviceRequests = ServiceRequest::where('package_id', $package->id)->get();
                
                if ($serviceRequests->isNotEmpty()) {
                    $debug['details']['service_requests'] = [
                        'system' => 'ServiceRequest',
                        'total' => $serviceRequests->count(),
                        'approved' => $serviceRequests->where('status', ServiceRequest::STATUS_APPROVED)->count(),
                        'pending' => $serviceRequests->where('status', ServiceRequest::STATUS_PENDING)->count(),
                        'rejected' => $serviceRequests->where('status', ServiceRequest::STATUS_REJECTED)->count()
                    ];
                } else {
                    // Check old ProviderRequest system
                    $providerRequests = ProviderRequest::where('package_id', $package->id)->get();
                    
                    $debug['details']['provider_requests'] = [
                        'system' => 'ProviderRequest',
                        'total' => $providerRequests->count(),
                        'approved' => $providerRequests->where('status', ProviderRequest::STATUS_APPROVED)->count(),
                        'pending' => $providerRequests->where('status', ProviderRequest::STATUS_PENDING)->count(),
                        'rejected' => $providerRequests->where('status', ProviderRequest::STATUS_REJECTED)->count()
                    ];
                }
                break;
                
            case 'step-5':
                $debug['validation_result'] = $middleware->validateStep4($package);
                $debug['details']['pricing'] = [
                    'base_price_set' => $package->base_price !== null && $package->base_price > 0
                ];
                break;
        }
        
        return $debug;
    }
    
    /**
     * Get completion percentage for a package
     */
    public static function getCompletionPercentage(Package $package): array
    {
        $steps = [
            'basic_info' => 0,
            'itinerary' => 0, 
            'providers' => 0,
            'pricing' => 0,
            'review' => 0
        ];
        
        // Step 1: Basic Information (25%)
        $basicInfoFields = [
            'name' => !empty($package->name),
            'type' => !empty($package->type),
            'duration' => $package->duration > 0,
            'max_participants' => $package->max_participants > 0,
            'description' => !empty($package->description)
        ];
        $completedBasic = array_filter($basicInfoFields);
        $steps['basic_info'] = (count($completedBasic) / count($basicInfoFields)) * 25;
        
        // Step 2: Itinerary (25%)
        $hasActivities = !empty($package->activities) && is_array($package->activities) && count($package->activities) > 0;
        $hasDetailedDescription = $package->description && strlen($package->description) > 100;
        $steps['itinerary'] = ($hasActivities || $hasDetailedDescription) ? 25 : 0;
        
        // Step 3: Providers (25%)
        $serviceRequests = ServiceRequest::where('package_id', $package->id)->get();
        if ($serviceRequests->isNotEmpty()) {
            // Using new ServiceRequest system
            $approvedCount = $serviceRequests->where('status', ServiceRequest::STATUS_APPROVED)->count();
            $totalCount = $serviceRequests->count();
            $steps['providers'] = min(25, ($approvedCount / max($totalCount, 1)) * 25);
        } else {
            // Fallback to old ProviderRequest system
            $providerRequests = ProviderRequest::where('package_id', $package->id)->get();
            if ($providerRequests->isNotEmpty()) {
                $approvedCount = $providerRequests->where('status', ProviderRequest::STATUS_APPROVED)->count();
                $totalCount = $providerRequests->count();
                $steps['providers'] = min(25, ($approvedCount / max($totalCount, 1)) * 25);
            }
        }
        
        // Step 4: Pricing (15%)
        $steps['pricing'] = ($package->base_price && $package->base_price > 0) ? 15 : 0;
        
        // Step 5: Review (10%) 
        $validation = self::canPublishPackage($package);
        $steps['review'] = $validation['can_publish'] ? 10 : 5;
        
        $totalPercentage = array_sum($steps);
        
        return [
            'total' => round($totalPercentage),
            'steps' => $steps,
            'can_publish' => $validation['can_publish']
        ];
    }
}