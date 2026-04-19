<?php

/**
 * Star Ratings — Contensio plugin.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Ratings\Http\Controllers\Frontend;

use Contensio\Models\Content;
use Contensio\Ratings\Models\ContentRating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RateController extends Controller
{
    /**
     * Submit or update a rating.
     * POST /ratings/{contentId}
     * Body: { rating: 1-5 }
     */
    public function rate(Request $request, int $contentId): JsonResponse
    {
        // Verify the content item exists and is publicly accessible
        $content = Content::find($contentId);

        if (! $content || $content->status !== 'published') {
            return response()->json(['error' => 'Content not found.'], 404);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        ContentRating::submitFor($contentId, (int) $request->input('rating'), $request);

        return response()->json([
            'success' => true,
            ...ContentRating::summaryFor($contentId, $request),
        ]);
    }

    /**
     * Get the current rating summary for a content item.
     * GET /ratings/{contentId}
     */
    public function summary(Request $request, int $contentId): JsonResponse
    {
        $content = Content::find($contentId);

        if (! $content || $content->status !== 'published') {
            return response()->json(['error' => 'Content not found.'], 404);
        }

        return response()->json(ContentRating::summaryFor($contentId, $request));
    }
}
