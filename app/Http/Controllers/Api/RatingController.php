<?php

namespace App\Http\Controllers\Api;

use App\Models\Rating;
use App\Models\Konsultasi;
use Illuminate\Http\Request;
use App\Http\Requests\RatingRequest;
use App\Services\RatingService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

/**
 * ============================================
 * RATING CONTROLLER - CONSULTATION RATINGS
 * ============================================
 * 
 * @OA\Tag(
 *     name="Rating",
 *     description="Consultation rating and review endpoints"
 * )
 */
class RatingController extends Controller
{
    use ApiResponse;

    protected RatingService $ratingService;

    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }

    /**
     * Get ratings for a specific dokter
     * 
     * @OA\Get(
     *     path="/api/v1/ratings/dokter/{dokter_id}",
     *     summary="Get doctor ratings",
     *     description="Get all ratings dan statistics untuk dokter tertentu",
     *     tags={"Rating"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="dokter_id",
     *         in="path",
     *         required=true,
     *         schema={"type":"integer"}
     *     ),
     *     @OA\Response(response=200, description="Rating statistics retrieved successfully"),
     *     @OA\Response(response=404, description="Doctor not found")
     * )
     */
    public function getDokterRatings($dokter_id)
    {
        $stats = $this->ratingService->getDoctorRatingStats($dokter_id);
        return $this->successResponse($stats);
    }

    /**
     * Get ratings for a specific konsultasi
     * 
     * @OA\Get(
     *     path="/api/v1/ratings/konsultasi/{konsultasi_id}",
     *     summary="Get consultation rating",
     *     description="Get rating untuk konsultasi tertentu",
     *     tags={"Rating"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="konsultasi_id",
     *         in="path",
     *         required=true,
     *         schema={"type":"integer"}
     *     ),
     *     @OA\Response(response=200, description="Rating retrieved successfully"),
     *     @OA\Response(response=404, description="Rating not found")
     * )
     */
    public function getKonsultasiRating($konsultasi_id)
    {
        $rating = Rating::where('konsultasi_id', $konsultasi_id)
            ->with(['user', 'dokter', 'konsultasi'])
            ->first();

        if (!$rating) {
            return $this->notFoundResponse();
        }

        return $this->successResponse(['data' => $rating]);
    }

    /**
     * Create a new rating
     * 
     * @OA\Post(
     *     path="/api/v1/ratings",
     *     summary="Create consultation rating",
     *     description="Create rating untuk konsultasi yang telah selesai",
     *     tags={"Rating"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"konsultasi_id", "bintang"},
     *             @OA\Property(property="konsultasi_id", type="integer"),
     *             @OA\Property(property="bintang", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="komentar", type="string")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Rating created successfully"),
     *     @OA\Response(response=404, description="Consultation not found"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(RatingRequest $request)
    {
        $validated = $request->validated();

        // Get konsultasi to verify it exists and authorize
        $konsultasi = Konsultasi::find($validated['konsultasi_id']);
        if (!$konsultasi) {
            return $this->notFoundResponse();
        }

        // Check authorization: user must be pasien of this consultation
        if ($konsultasi->user_id !== Auth::id()) {
            return $this->unauthorizedResponse();
        }

        $rating = $this->ratingService->createRating($validated['konsultasi_id'], $validated);

        return $this->createdResponse(['data' => $rating->load(['user', 'dokter', 'konsultasi'])]);
    }

    /**
     * Update a rating
     * 
     * @OA\Put(
     *     path="/api/v1/ratings/{id}",
     *     summary="Update consultation rating",
     *     description="Update rating yang sudah dibuat",
     *     tags={"Rating"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         schema={"type":"integer"}
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="bintang", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="komentar", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Rating updated successfully"),
     *     @OA\Response(response=404, description="Rating not found"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function update(RatingRequest $request, $id)
    {
        $rating = Rating::find($id);
        if (!$rating) {
            return $this->notFoundResponse();
        }

        // Check authorization: owner (pasien who created it) or admin only
        if ($rating->user_id !== Auth::id()) {
            return $this->unauthorizedResponse();
        }

        $validated = $request->validated();
        $updatedRating = $this->ratingService->updateRating($rating, $validated);

        return $this->successResponse(['data' => $updatedRating->load(['user', 'dokter', 'konsultasi'])]);
    }

    /**
     * Delete a rating
     * 
     * @OA\Delete(
     *     path="/api/v1/ratings/{id}",
     *     summary="Delete consultation rating",
     *     description="Delete rating yang sudah dibuat",
     *     tags={"Rating"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         schema={"type":"integer"}
     *     ),
     *     @OA\Response(response=200, description="Rating deleted successfully"),
     *     @OA\Response(response=404, description="Rating not found"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function destroy($id)
    {
        $rating = Rating::find($id);
        if (!$rating) {
            return $this->notFoundResponse();
        }

        // Check authorization: owner or admin only
        if ($rating->user_id !== Auth::id()) {
            return $this->unauthorizedResponse();
        }

        $this->ratingService->deleteRating($rating);

        return $this->successResponse(['message' => 'Rating berhasil dihapus']);
    }
}
?>
