<?php

namespace App\Services;

use App\Models\Rating;
use App\Models\Konsultasi;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk mengelola rating konsultasi
 */
class RatingService
{
    /**
     * Create new rating (with concurrent access control)
     * 
     * @param int $konsultasiId
     * @param array $data
     * @return Rating
     */
    public function createRating(int $konsultasiId, array $data)
    {
        return DB::transaction(function () use ($konsultasiId, $data) {
            // Lock konsultasi untuk concurrent access control
            $konsultasi = Konsultasi::lockForUpdate()->findOrFail($konsultasiId);
            
            // Check duplicate rating doesn't exist
            $existingRating = Rating::where('konsultasi_id', $konsultasiId)
                ->lockForUpdate()
                ->exists();
            
            if ($existingRating) {
                throw new \Exception('Rating untuk konsultasi ini sudah dibuat');
            }

            $rating = Rating::create([
                'konsultasi_id' => $konsultasiId,
                'rating' => $data['rating'],
                'komentar' => $data['komentar'] ?? null,
            ]);

            // Update consultation rating atomically
            $konsultasi->update(['rating' => $data['rating']]);

            return $rating->load('konsultasi');
        });
    }

    /**
     * Get rating by ID
     * 
     * @param int $id
     * @return Rating|null
     */
    public function getRatingById(int $id)
    {
        return Rating::with('konsultasi')->find($id);
    }

    /**
     * Update rating (with concurrent access control)
     * 
     * @param Rating $rating
     * @param array $data
     * @return Rating
     */
    public function updateRating(Rating $rating, array $data)
    {
        return DB::transaction(function () use ($rating, $data) {
            // Lock rating for update
            $rating = Rating::lockForUpdate()->findOrFail($rating->id);
            $oldRating = $rating->rating;

            $rating->update([
                'rating' => $data['rating'] ?? $oldRating,
                'komentar' => $data['komentar'] ?? $rating->komentar,
            ]);

            // Update consultation rating if changed
            if (($data['rating'] ?? $oldRating) !== $oldRating) {
                $konsultasi = Konsultasi::lockForUpdate()->find($rating->konsultasi_id);
                if ($konsultasi) {
                    $konsultasi->update(['rating' => $data['rating']]);
                }
            }

            return $rating->fresh(['konsultasi']);
        });
    }

    /**
     * Delete rating
     * 
     * @param Rating $rating
     * @return bool
     */
    public function deleteRating(Rating $rating)
    {
        return DB::transaction(function () use ($rating) {
            // Reset consultation rating
            $konsultasi = $rating->konsultasi;
            if ($konsultasi) {
                $konsultasi->update(['rating' => null]);
            }

            return $rating->delete();
        });
    }

    /**
     * Get ratings for a consultation
     * 
     * @param int $konsultasiId
     * @return Rating|null
     */
    public function getRatingByConsultation(int $konsultasiId)
    {
        return Rating::where('konsultasi_id', $konsultasiId)
            ->with('konsultasi')
            ->first();
    }

    /**
     * Get doctor ratings with statistics
     * 
     * @param int $dokterId
     * @return array
     */
    public function getDoctorRatingStats(int $dokterId)
    {
        $ratings = Rating::whereHas('konsultasi', function ($query) use ($dokterId) {
            $query->where('dokter_id', $dokterId);
        })->get();

        $count = $ratings->count();
        $avgRating = $count > 0 ? $ratings->avg('rating') : 0;

        return [
            'total_ratings' => $count,
            'average_rating' => round($avgRating, 2),
            'distribution' => [
                '5' => $ratings->where('rating', 5)->count(),
                '4' => $ratings->where('rating', 4)->count(),
                '3' => $ratings->where('rating', 3)->count(),
                '2' => $ratings->where('rating', 2)->count(),
                '1' => $ratings->where('rating', 1)->count(),
            ],
        ];
    }
}
