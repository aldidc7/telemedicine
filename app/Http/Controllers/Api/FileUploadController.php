<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\FileUploadException;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;

class FileUploadController extends Controller
{
    public function __construct(private FileUploadService $uploadService)
    {
    }

    /**
     * Upload single file dengan batasan ukuran
     * 
     * @OA\Post(
     *     path="/api/files/upload",
     *     summary="Upload file dengan validasi ukuran",
     *     description="Upload file dengan batasan ukuran ketat agar tidak memenuhi storage",
     *     tags={"Files"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="multipart/form-data",
     *                 schema={
     *                     @OA\Schema(
     *                         type="object",
     *                         required={"file","category"},
     *                         @OA\Property(
     *                             property="file",
     *                             type="string",
     *                             format="binary",
     *                             description="File untuk upload"
     *                         ),
     *                         @OA\Property(
     *                             property="category",
     *                             type="string",
     *                             enum={"profile_photo","medical_document","medical_image","prescription","consultation_file","message_attachment"},
     *                             description="Kategori file"
     *                         )
     *                     )
     *                 }
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File berhasil diupload",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File berhasil diupload"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="path", type="string", example="uploads/profile_photo/1/2025/12/19/photo.jpg"),
     *                 @OA\Property(property="url", type="string", example="/storage/uploads/profile_photo/1/2025/12/19/photo.jpg"),
     *                 @OA\Property(property="filename", type="string", example="photo.jpg"),
     *                 @OA\Property(property="size", type="integer", example=2048000),
     *                 @OA\Property(property="mime_type", type="string", example="image/jpeg"),
     *                 @OA\Property(property="uploaded_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal atau file terlalu besar",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="File terlalu besar")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function upload(FileUploadRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = auth()->user();
            
            $result = $this->uploadService->uploadFile(
                $request->file('file'),
                $request->input('category'),
                $user
            );

            return response()->json([
                'success' => true,
                'message' => 'File berhasil diupload',
                'data' => $result,
            ], 200);
        } catch (FileUploadException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?? 'File upload gagal',
                'error_code' => 'FILE_UPLOAD_ERROR',
            ], 422);
        } catch (\Exception $e) {
            \Log::error('File upload error', [
                'user_id' => auth()->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat upload file',
                'error_code' => 'FILE_UPLOAD_FAILED',
            ], 500);
        }
    }

    /**
     * Get storage usage info untuk user
     * 
     * @OA\Get(
     *     path="/api/files/storage-info",
     *     summary="Get storage usage info",
     *     description="Mendapatkan informasi penggunaan storage untuk user",
     *     tags={"Files"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Storage info berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="max_storage", type="integer", example=524288000),
     *                 @OA\Property(property="current_usage", type="integer", example=262144000),
     *                 @OA\Property(property="remaining_storage", type="integer", example=262144000),
     *                 @OA\Property(property="usage_percent", type="number", example=50.0),
     *                 @OA\Property(property="max_storage_formatted", type="string", example="500 MB"),
     *                 @OA\Property(property="current_usage_formatted", type="string", example="250 MB"),
     *                 @OA\Property(property="remaining_storage_formatted", type="string", example="250 MB")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getStorageInfo(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = auth()->user();
            
            $info = $this->uploadService->getUserStorageInfo($user);

            return response()->json([
                'success' => true,
                'data' => $info,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Get storage info error', [
                'user_id' => auth()->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan informasi storage',
            ], 500);
        }
    }

    /**
     * Delete file (soft delete - pindah ke trash)
     * 
     * @OA\Delete(
     *     path="/api/files/{id}",
     *     summary="Delete file (soft delete)",
     *     description="Menghapus file ke trash (dapat dipulihkan dalam 30 hari)",
     *     tags={"Files"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="File path/ID",
     *         schema={@OA\Schema(type="string")}
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="File berhasil dihapus")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File tidak ditemukan"
     *     )
     * )
     */
    public function delete(string $filePath): JsonResponse
    {
        try {
            /** @var User $user */
            $user = auth()->user();
            
            $this->uploadService->softDeleteFile($user, $filePath);

            return response()->json([
                'success' => true,
                'message' => 'File berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('File delete error', [
                'user_id' => auth()->id() ?? null,
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus file',
            ], 500);
        }
    }

    /**
     * Get size limits untuk setiap kategori
     */
    public function getSizeLimits(): JsonResponse
    {
        $limits = config('file-upload.max_upload_sizes');
        
        // Convert bytes to human-readable format
        $formatted = [];
        foreach ($limits as $category => $bytes) {
            $formatted[$category] = [
                'bytes' => $bytes,
                'formatted' => $this->formatBytes($bytes),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ], 200);
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
