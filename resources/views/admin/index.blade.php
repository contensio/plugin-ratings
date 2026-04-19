@extends('contensio::admin.layout')

@section('title', 'Star Ratings')

@section('content')
<div class="p-6">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Star Ratings</h1>
            <p class="mt-1 text-gray-500">Content items that have received ratings from visitors.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if($rows->isEmpty())
    <div class="bg-white border border-gray-200 rounded-xl py-16 text-center text-gray-400">
        <i class="bi bi-star text-4xl mb-3 block"></i>
        <p class="text-lg font-medium text-gray-500">No ratings yet</p>
        <p class="text-sm mt-1">Embed the rating widget in your theme and ratings will appear here.</p>
        <p class="mt-4 text-xs font-mono bg-gray-50 border border-gray-200 rounded-lg inline-block px-4 py-2 text-gray-600">
            @{{ include('ratings::partials.rating-widget', ['contentId' => $content->id]) }}
        </p>
    </div>
    @else
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wider text-gray-500">Content</th>
                    <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wider text-gray-500">Average</th>
                    <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wider text-gray-500">Ratings</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($rows as $row)
                @php
                    $content   = $contentMap[$row->content_id] ?? null;
                    $avg       = round((float) $row->avg_rating, 1);
                    $fullStars = (int) floor($avg);
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5">
                        @if($content)
                        <p class="font-medium text-gray-900">{{ $content->title }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ ucfirst($content->type ?? 'content') }} · ID {{ $content->id }}
                        </p>
                        @else
                        <p class="text-gray-400 italic">Content #{{ $row->content_id }} (deleted)</p>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-0.5">
                                @for($s = 1; $s <= 5; $s++)
                                <i class="bi {{ $s <= $fullStars ? 'bi-star-fill text-amber-400' : 'bi-star text-gray-300' }} text-base leading-none"></i>
                                @endfor
                            </div>
                            <span class="font-semibold text-gray-900">{{ number_format($avg, 1) }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-gray-700 font-semibold">
                        {{ number_format($row->total_ratings) }}
                    </td>
                    <td class="px-5 py-3.5">
                        <form method="POST" action="{{ route('ratings.reset', $row->content_id) }}"
                              onsubmit="return confirm('Delete all {{ $row->total_ratings }} rating(s) for this item? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600 hover:text-red-800 border border-red-200 hover:border-red-300 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                Reset
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($rows->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $rows->links() }}</div>
        @endif
    </div>
    @endif

</div>
@endsection
