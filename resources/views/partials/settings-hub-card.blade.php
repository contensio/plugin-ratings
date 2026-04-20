@php
    use Contensio\Ratings\Models\ContentRating;
    try { $ratingCount = ContentRating::count(); } catch (\Throwable) { $ratingCount = null; }
@endphp
<a href="{{ route('contensio-ratings.index') }}"
   class="block bg-white border border-gray-200 rounded-xl p-5 hover:border-ember-400 hover:shadow-sm transition-all group">
    <div class="flex items-start justify-between gap-3">
        <div class="w-10 h-10 rounded-lg bg-ember-500/10 text-ember-600 flex items-center justify-center text-xl shrink-0">
            <i class="bi bi-star-half"></i>
        </div>
        @if($ratingCount !== null)
        <span class="text-sm font-semibold text-gray-400">{{ number_format($ratingCount) }} rating{{ $ratingCount !== 1 ? 's' : '' }}</span>
        @endif
    </div>
    <p class="mt-3 font-semibold text-gray-900 group-hover:text-ember-600 transition-colors">Ratings</p>
    <p class="mt-0.5 text-sm text-gray-500">Star ratings on posts and pages.</p>
</a>
