@php
    use Contensio\Ratings\Models\ContentRating;

    if (! isset($contentId)) { return; }

    $contentId  = (int) $contentId;
    $average    = ContentRating::averageFor($contentId);
    $count      = ContentRating::countFor($contentId);
    $userRating = ContentRating::userRatingFor($contentId, request());
    $canRate    = isset($allowGuests) ? $allowGuests : true;
    // If caller passes allowGuests=false, only logged-in users may rate
    if (! $canRate && ! auth()->check()) {
        $canRate = false;
    } else {
        $canRate = true;
    }
@endphp

<div class="ratings-widget"
     x-data="ratingWidget({{ $contentId }}, {{ $average }}, {{ $count }}, {{ $userRating ?? 'null' }}, {{ $canRate ? 'true' : 'false' }})">

    {{-- Stars row --}}
    <div class="flex items-center gap-3 flex-wrap">

        {{-- Interactive stars --}}
        <div class="flex items-center gap-0.5">
            @for($s = 1; $s <= 5; $s++)
            <button type="button"
                    @mouseenter="hovered = {{ $s }}"
                    @mouseleave="hovered = 0"
                    @click="rate({{ $s }})"
                    :disabled="loading || !canRate"
                    :title="canRate ? 'Rate {{ $s }} star{{ $s !== 1 ? 's' : '' }}' : 'Log in to rate'"
                    class="text-2xl leading-none transition-colors focus:outline-none disabled:cursor-default"
                    :class="{
                        'text-amber-400': {{ $s }} <= (hovered || userRating || 0),
                        'text-gray-300':  {{ $s }} >  (hovered || userRating || 0),
                        'hover:scale-110 cursor-pointer': canRate,
                    }">
                <i class="bi bi-star-fill"></i>
            </button>
            @endfor
        </div>

        {{-- Average + count --}}
        <div class="flex items-center gap-2 text-sm">
            <span x-show="count > 0">
                <strong class="text-gray-900 font-semibold" x-text="average.toFixed(1)"></strong>
                <span class="text-gray-400">/ 5</span>
            </span>
            <span class="text-gray-400" x-show="count > 0">·</span>
            <span class="text-gray-500">
                <span x-text="count"></span>
                <span x-show="count !== 1"> ratings</span>
                <span x-show="count === 1"> rating</span>
            </span>
            <span x-show="count === 0" class="text-gray-400">No ratings yet</span>
        </div>
    </div>

    {{-- Feedback messages --}}
    <p x-show="justRated" x-cloak class="mt-2 text-sm text-green-600 font-medium">
        Thanks for your rating!
    </p>
    <p x-show="error" x-cloak x-text="error" class="mt-2 text-sm text-red-600"></p>

    {{-- Login prompt --}}
    @if(! auth()->check() && (isset($requireLogin) && $requireLogin))
    <p class="mt-2 text-sm text-gray-500">
        <a href="{{ route('login') }}" class="text-ember-600 underline underline-offset-2">Log in</a> to rate.
    </p>
    @endif

</div>

<script>
function ratingWidget(contentId, initialAverage, initialCount, initialUserRating, canRate) {
    return {
        contentId,
        average:    initialAverage,
        count:      initialCount,
        userRating: initialUserRating,
        canRate,
        hovered:    0,
        loading:    false,
        justRated:  false,
        error:      null,

        async rate(star) {
            if (! this.canRate || this.loading) return;
            if (this.userRating === star) return; // already same rating, no-op

            this.loading   = true;
            this.error     = null;
            this.justRated = false;

            try {
                const resp = await fetch('/ratings/' + this.contentId, {
                    method:  'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ rating: star }),
                });

                const data = await resp.json();

                if (data.success) {
                    this.average    = data.average;
                    this.count      = data.count;
                    this.userRating = data.your_rating;
                    this.justRated  = true;
                } else {
                    this.error = data.error ?? 'Something went wrong. Please try again.';
                }
            } catch {
                this.error = 'Could not submit your rating. Please check your connection.';
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
