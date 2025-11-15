<?php

namespace App\Services;

use App\Models\ButirComment;
use App\Models\PengisianButir;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ButirCommentService
{
    /**
     * Get all comments for a pengisian butir with nested replies
     *
     * @param int $pengisianButirId
     * @return Collection
     */
    public function getComments(int $pengisianButirId): Collection
    {
        return ButirComment::where('pengisian_butir_id', $pengisianButirId)
            ->rootComments()
            ->with(['user', 'replies.user'])
            ->get();
    }

    /**
     * Get comment statistics
     *
     * @param int $pengisianButirId
     * @return array
     */
    public function getStatistics(int $pengisianButirId): array
    {
        $comments = ButirComment::where('pengisian_butir_id', $pengisianButirId);

        return [
            'total' => $comments->count(),
            'resolved' => $comments->clone()->where('is_resolved', true)->count(),
            'unresolved' => $comments->clone()->where('is_resolved', false)->count(),
            'threads' => $comments->clone()->whereNull('parent_id')->count(),
        ];
    }

    /**
     * Create a new comment
     *
     * @param int $pengisianButirId
     * @param array $data
     * @return ButirComment
     */
    public function createComment(int $pengisianButirId, array $data): ButirComment
    {
        DB::beginTransaction();

        try {
            // Verify pengisian butir exists
            $pengisianButir = PengisianButir::findOrFail($pengisianButirId);

            // Extract mentioned users from comment text (@username pattern)
            $mentionedUsers = $this->extractMentions($data['comment']);

            $comment = ButirComment::create([
                'pengisian_butir_id' => $pengisianButirId,
                'user_id' => Auth::id(),
                'parent_id' => $data['parent_id'] ?? null,
                'comment' => $data['comment'],
                'is_resolved' => false,
                'mentioned_users' => $mentionedUsers,
            ]);

            DB::commit();

            Log::info('Comment created', [
                'comment_id' => $comment->id,
                'pengisian_butir_id' => $pengisianButirId,
                'user_id' => Auth::id(),
                'has_mentions' => !empty($mentionedUsers),
            ]);

            // TODO: Send notifications to mentioned users
            // $this->notifyMentionedUsers($comment, $mentionedUsers);

            return $comment->load(['user', 'replies.user']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create comment', [
                'error' => $e->getMessage(),
                'pengisian_butir_id' => $pengisianButirId,
            ]);
            throw $e;
        }
    }

    /**
     * Update a comment
     *
     * @param int $commentId
     * @param array $data
     * @return ButirComment
     */
    public function updateComment(int $commentId, array $data): ButirComment
    {
        DB::beginTransaction();

        try {
            $comment = ButirComment::findOrFail($commentId);

            // Check if user is the owner
            if ($comment->user_id !== Auth::id()) {
                throw new \Exception('Anda tidak memiliki izin untuk mengubah komentar ini');
            }

            // Extract new mentions
            $mentionedUsers = $this->extractMentions($data['comment']);

            $comment->update([
                'comment' => $data['comment'],
                'mentioned_users' => $mentionedUsers,
            ]);

            DB::commit();

            Log::info('Comment updated', [
                'comment_id' => $commentId,
                'user_id' => Auth::id(),
            ]);

            return $comment->load(['user', 'replies.user']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update comment', [
                'error' => $e->getMessage(),
                'comment_id' => $commentId,
            ]);
            throw $e;
        }
    }

    /**
     * Delete a comment
     *
     * @param int $commentId
     * @return bool
     */
    public function deleteComment(int $commentId): bool
    {
        DB::beginTransaction();

        try {
            $comment = ButirComment::findOrFail($commentId);

            // Check if user is the owner
            if ($comment->user_id !== Auth::id()) {
                throw new \Exception('Anda tidak memiliki izin untuk menghapus komentar ini');
            }

            // Soft delete (will cascade to replies via model events)
            $comment->delete();

            DB::commit();

            Log::info('Comment deleted', [
                'comment_id' => $commentId,
                'user_id' => Auth::id(),
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete comment', [
                'error' => $e->getMessage(),
                'comment_id' => $commentId,
            ]);
            throw $e;
        }
    }

    /**
     * Resolve a comment thread
     *
     * @param int $commentId
     * @return ButirComment
     */
    public function resolveComment(int $commentId): ButirComment
    {
        DB::beginTransaction();

        try {
            $comment = ButirComment::findOrFail($commentId);

            // Only root comments can be resolved
            if ($comment->parent_id !== null) {
                throw new \Exception('Hanya thread utama yang dapat di-resolve');
            }

            $comment->update(['is_resolved' => true]);

            DB::commit();

            Log::info('Comment thread resolved', [
                'comment_id' => $commentId,
                'user_id' => Auth::id(),
            ]);

            return $comment->load(['user', 'replies.user']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to resolve comment', [
                'error' => $e->getMessage(),
                'comment_id' => $commentId,
            ]);
            throw $e;
        }
    }

    /**
     * Unresolve a comment thread
     *
     * @param int $commentId
     * @return ButirComment
     */
    public function unresolveComment(int $commentId): ButirComment
    {
        DB::beginTransaction();

        try {
            $comment = ButirComment::findOrFail($commentId);

            // Only root comments can be unresolved
            if ($comment->parent_id !== null) {
                throw new \Exception('Hanya thread utama yang dapat di-unresolve');
            }

            $comment->update(['is_resolved' => false]);

            DB::commit();

            Log::info('Comment thread unresolved', [
                'comment_id' => $commentId,
                'user_id' => Auth::id(),
            ]);

            return $comment->load(['user', 'replies.user']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to unresolve comment', [
                'error' => $e->getMessage(),
                'comment_id' => $commentId,
            ]);
            throw $e;
        }
    }

    /**
     * Extract @mentions from comment text
     *
     * @param string $text
     * @return array
     */
    protected function extractMentions(string $text): array
    {
        preg_match_all('/@(\w+)/', $text, $matches);

        if (empty($matches[1])) {
            return [];
        }

        // Get user IDs for mentioned usernames
        $usernames = array_unique($matches[1]);
        $users = User::whereIn('username', $usernames)
            ->orWhereIn('name', $usernames)
            ->get(['id', 'name', 'username']);

        return $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username ?? $user->name,
            ];
        })->toArray();
    }
}
