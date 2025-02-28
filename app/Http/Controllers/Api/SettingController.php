<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Settings",
 *     description="API Endpoints for managing website settings"
 * )
 */

class SettingController extends Controller
{
    /**
     * Get all settings
     *
     * @return JsonResponse
     * 
     * @OA\Get(
     *     path="/api/v1/settings",
     *     tags={"Settings"},
     *     summary="Get all website settings",
     *     description="Returns all website settings including site information, contact details, and configurations",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Settings retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Setting"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $settings = Setting::getSettings();
            return response()->json([
                'status' => 'success',
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Update settings
     *
     * @param Request $request
     * @param Setting $setting
     * @return JsonResponse
     * 
     * @OA\Patch(
     *     path="/api/v1/settings/{setting}",
     *     tags={"Settings"},
     *     summary="Update website settings",
     *     description="Update website settings including site information, contact details, and configurations",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="setting",
     *         in="path",
     *         required=true,
     *         description="Setting ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Setting")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Settings updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Settings updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Setting"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function update(Request $request, Setting $setting): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            // Site Information
            'site_name' => 'required|string|max:255',
            'site_title' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'site_logo' => 'nullable|url',
            'site_favicon' => 'nullable|url',

            // Contact Information
            'company_name' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string|max:255',
            'office_address' => 'required|string',

            // Social Media
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'linkedin_url' => 'nullable|url',

            // Real Estate Specific
            'currency_symbol' => 'required|string|max:10',
            'measurement_unit' => 'required|string|max:20',
            'properties_per_page' => 'required|integer|min:1',
            'show_featured_properties' => 'required|boolean',
            'enable_map' => 'required|boolean',
            'default_latitude' => 'nullable|string',
            'default_longitude' => 'nullable|string',

            // SEO Settings
            'google_analytics_id' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string',
            'meta_description' => 'nullable|string',

            // Email Settings
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|string|max:10',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|max:10',
            'mail_from_address' => 'nullable|email',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $setting->update($request->all());

            // Update mail configuration if email settings are provided
            if ($request->filled('smtp_host')) {
                config([
                    'mail.mailers.smtp.host' => $request->smtp_host,
                    'mail.mailers.smtp.port' => $request->smtp_port,
                    'mail.mailers.smtp.username' => $request->smtp_username,
                    'mail.mailers.smtp.password' => $request->smtp_password,
                    'mail.mailers.smtp.encryption' => $request->mail_encryption,
                    'mail.from.address' => $request->mail_from_address,
                    'mail.from.name' => $request->mail_from_name,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Settings updated successfully',
                'data' => $setting
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
