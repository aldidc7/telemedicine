<?php

namespace App\Services;

use App\Models\Dokter;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Doctor Search Service
 * 
 * Handle advanced search dan filtering untuk dokter
 */
class DoctorSearchService
{
    /**
     * Search dokter dengan berbagai filter
     * 
     * Parameters:
     * - q: search keyword (name, specialization)
     * - specialization: filter by specialization
     * - available: filter by availability (true/false)
     * - min_rating: filter by minimum rating
     * - verified_only: hanya dokter verified (true/false)
     * - page: pagination page
     * - per_page: items per page
     * - sort: sort by field (name, rating, availability)
     * - order: asc or desc
     */
    public static function search(array $params = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Dokter::query()
            ->with('user', 'ratings')
            ->where('is_verified', true);  // Only show verified doctors

        // Search by name or specialization
        if (!empty($params['q'])) {
            $search = $params['q'];
            $query->where(function (Builder $q) use ($search) {
                $q->whereHas('user', function (Builder $userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        // Filter by specialization
        if (!empty($params['specialization'])) {
            $query->where('specialization', $params['specialization']);
        }

        // Filter by availability
        if (isset($params['available'])) {
            $query->where('is_available', filter_var($params['available'], FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by minimum rating
        if (!empty($params['min_rating'])) {
            $minRating = (float) $params['min_rating'];
            $query->whereHas('ratings', function (Builder $q) use ($minRating) {
                $q->selectRaw('COUNT(*) as count, AVG(rating) as avg_rating')
                    ->havingRaw('AVG(rating) >= ?', [$minRating]);
            }, '>=', 1);
        }

        // Sorting
        $sort = $params['sort'] ?? 'name';
        $order = strtoupper($params['order'] ?? 'asc') === 'DESC' ? 'desc' : 'asc';

        switch ($sort) {
            case 'rating':
                $query->leftJoin('ratings', 'doctors.id', '=', 'ratings.doctor_id')
                    ->selectRaw('doctors.*, AVG(ratings.rating) as avg_rating')
                    ->groupBy('doctors.id')
                    ->orderBy('avg_rating', $order);
                break;
            case 'availability':
                $query->orderBy('is_available', $order);
                break;
            case 'name':
            default:
                $query->join('users', 'doctors.user_id', '=', 'users.id')
                    ->orderBy('users.name', $order)
                    ->select('doctors.*');
                break;
        }

        // Pagination
        $perPage = min((int) ($params['per_page'] ?? 10), 100);  // Max 100 per page
        $page = max(1, (int) ($params['page'] ?? 1));

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all available specializations
     */
    public static function getSpecializations(): array
    {
        return Dokter::distinct()
            ->where('is_verified', true)
            ->pluck('specialization')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get doctor with full details
     */
    public static function getDoctorDetail(int $doctorId): ?array
    {
        $doctor = Dokter::with('user', 'ratings')
            ->where('is_verified', true)
            ->find($doctorId);

        if (!$doctor) {
            return null;
        }

        $avgRating = $doctor->ratings->avg('rating') ?? 0;
        $totalRatings = $doctor->ratings->count();

        return [
            'id' => $doctor->id,
            'name' => $doctor->user->name,
            'email' => $doctor->user->email,
            'phone' => $doctor->phone_number,
            'specialization' => $doctor->specialization,
            'license_number' => $doctor->license_number,
            'is_available' => $doctor->is_available,
            'max_concurrent_consultations' => $doctor->max_concurrent_consultations,
            'rating' => round($avgRating, 1),
            'total_ratings' => $totalRatings,
            'address' => $doctor->address,
            'gender' => $doctor->gender,
            'place_of_birth' => $doctor->place_of_birth,
            'birthplace_city' => $doctor->birthplace_city,
            'profile_photo' => $doctor->profile_photo,
            'verified_at' => $doctor->verified_at,
        ];
    }

    /**
     * Get doctors count by specialization
     */
    public static function getSpecializationStats(): array
    {
        return Dokter::select('specialization')
            ->where('is_verified', true)
            ->groupBy('specialization')
            ->selectRaw('specialization, COUNT(*) as count')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->specialization => $item->count];
            })
            ->toArray();
    }

    /**
     * Get top-rated doctors
     */
    public static function getTopRated(int $limit = 10): array
    {
        return Dokter::where('is_verified', true)
            ->with(['user', 'ratings'])
            ->get()
            ->map(function ($doctor) {
                $avgRating = $doctor->ratings->avg('rating') ?? 0;
                $doctor->avg_rating = round($avgRating, 1);
                $doctor->rating_count = $doctor->ratings->count();
                return $doctor;
            })
            ->sortByDesc('avg_rating')
            ->take($limit)
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name,
                    'specialization' => $doctor->specialization,
                    'rating' => $doctor->avg_rating,
                    'rating_count' => $doctor->rating_count,
                    'is_available' => $doctor->is_available,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Get available doctors count
     */
    public static function getAvailableCount(): int
    {
        return Dokter::where('is_verified', true)
            ->where('is_available', true)
            ->count();
    }
}
