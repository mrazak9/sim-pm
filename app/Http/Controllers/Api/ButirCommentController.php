<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ButirCommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ButirCommentController extends Controller
{
    protected ButirCommentService $commentService;

    public function __construct(ButirCommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Get all comments for a pengisian butir
     *
     * @param int $pengisianButirId
     * @return JsonResponse
     */
    public function index(int $pengisianButirId): JsonResponse
    {
        try {
            $comments = $this->commentService->getComments($pengisianButirId);
            $statistics = $this->commentService->getStatistics($pengisianButirId);

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil diambil',
                'data' => $comments,
                'statistics' => $statistics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil komentar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new comment
     *
     * @param Request $request
     * @param int $pengisianButirId
     * @return JsonResponse
     */
    public function store(Request $request, int $pengisianButirId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|min:1',
            'parent_id' => 'nullable|exists:butir_comments,id',
        ], [
            'comment.required' => 'Komentar harus diisi',
            'comment.min' => 'Komentar minimal 1 karakter',
            'parent_id.exists' => 'Komentar parent tidak ditemukan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $comment = $this->commentService->createComment(
                $pengisianButirId,
                $validator->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dibuat',
                'data' => $comment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat komentar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a comment
     *
     * @param Request $request
     * @param int $commentId
     * @return JsonResponse
     */
    public function update(Request $request, int $commentId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|min:1',
        ], [
            'comment.required' => 'Komentar harus diisi',
            'comment.min' => 'Komentar minimal 1 karakter',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $comment = $this->commentService->updateComment(
                $commentId,
                $validator->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil diupdate',
                'data' => $comment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate komentar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a comment
     *
     * @param int $commentId
     * @return JsonResponse
     */
    public function destroy(int $commentId): JsonResponse
    {
        try {
            $this->commentService->deleteComment($commentId);

            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus komentar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resolve a comment thread
     *
     * @param int $commentId
     * @return JsonResponse
     */
    public function resolve(int $commentId): JsonResponse
    {
        try {
            $comment = $this->commentService->resolveComment($commentId);

            return response()->json([
                'success' => true,
                'message' => 'Thread berhasil di-resolve',
                'data' => $comment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal me-resolve thread',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unresolve a comment thread
     *
     * @param int $commentId
     * @return JsonResponse
     */
    public function unresolve(int $commentId): JsonResponse
    {
        try {
            $comment = $this->commentService->unresolveComment($commentId);

            return response()->json([
                'success' => true,
                'message' => 'Thread berhasil di-unresolve',
                'data' => $comment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal me-unresolve thread',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
