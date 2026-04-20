<?php

/**
 * Star Ratings - Contensio plugin.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Ratings\Http\Controllers\Admin;

use Contensio\Models\Content;
use Contensio\Ratings\Models\ContentRating;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class RatingsController extends Controller
{
    /**
     * List all content items that have at least one rating.
     */
    public function index()
    {
        $rows = ContentRating::selectRaw('content_id, AVG(rating) as avg_rating, COUNT(*) as total_ratings')
            ->groupBy('content_id')
            ->orderByDesc('total_ratings')
            ->paginate(30);

        // Eager-load matching Content records (title, slug, type)
        $contentIds = $rows->pluck('content_id')->all();
        $contentMap = Content::whereIn('id', $contentIds)
            ->get(['id', 'title', 'slug', 'type'])
            ->keyBy('id');

        return view('contensio-ratings::admin.index', compact('rows', 'contentMap'));
    }

    /**
     * Delete all ratings for a single content item.
     */
    public function reset(int $contentId)
    {
        ContentRating::where('content_id', $contentId)->delete();

        return back()->with('success', 'Ratings have been reset.');
    }
}
