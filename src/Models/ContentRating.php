<?php

/**
 * Star Ratings - Contensio plugin.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Ratings\Models;

use Contensio\Models\Content;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class ContentRating extends Model
{
    protected $table = 'contensio_content_ratings';

    protected $fillable = [
        'content_id',
        'user_id',
        'ip_address',
        'rating',
    ];

    protected $casts = [
        'content_id' => 'integer',
        'user_id'    => 'integer',
        'rating'     => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    // ── Static helpers ────────────────────────────────────────────────────────

    /**
     * Average rating for a content item, rounded to 1 decimal.
     * Returns 0.0 if no ratings exist.
     */
    public static function averageFor(int $contentId): float
    {
        $avg = static::where('content_id', $contentId)->avg('rating');

        return $avg ? round((float) $avg, 1) : 0.0;
    }

    /**
     * Total number of ratings for a content item.
     */
    public static function countFor(int $contentId): int
    {
        return static::where('content_id', $contentId)->count();
    }

    /**
     * The current request's rating for this content item, or null.
     */
    public static function userRatingFor(int $contentId, Request $request): ?int
    {
        $row = static::where('content_id', $contentId)
            ->when($request->user(), fn ($q) => $q->where('user_id', $request->user()->id),
                                     fn ($q) => $q->whereNull('user_id')->where('ip_address', $request->ip()))
            ->first();

        return $row?->rating;
    }

    /**
     * Whether the current request has already rated this content item.
     */
    public static function hasRated(int $contentId, Request $request): bool
    {
        return static::userRatingFor($contentId, $request) !== null;
    }

    /**
     * Submit or update a rating for the current request.
     * Returns the saved ContentRating instance.
     */
    public static function submitFor(int $contentId, int $rating, Request $request): static
    {
        $user = $request->user();

        if ($user) {
            $existing = static::where('content_id', $contentId)
                ->where('user_id', $user->id)
                ->first();
        } else {
            $existing = static::where('content_id', $contentId)
                ->whereNull('user_id')
                ->where('ip_address', $request->ip())
                ->first();
        }

        if ($existing) {
            $existing->update(['rating' => $rating]);
            return $existing->fresh();
        }

        return static::create([
            'content_id' => $contentId,
            'user_id'    => $user?->id,
            'ip_address' => $request->ip(),
            'rating'     => $rating,
        ]);
    }

    /**
     * Build the summary payload used by JSON responses and the widget.
     * Returns ['average', 'count', 'your_rating'].
     */
    public static function summaryFor(int $contentId, Request $request): array
    {
        return [
            'average'    => static::averageFor($contentId),
            'count'      => static::countFor($contentId),
            'your_rating' => static::userRatingFor($contentId, $request),
        ];
    }
}
