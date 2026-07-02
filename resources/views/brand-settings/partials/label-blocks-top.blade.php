<section class="label-top">
    <div class="label-logo">
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $brandName }}">
        @else
            <span>{{ strtoupper(substr($brandName, 0, 2)) }}</span>
        @endif
    </div>
    <div class="label-sender">
        <p class="label-company">{{ $brandName }}</p>
        <p>{{ $demo['sender_address'] }}</p>
        <p>{{ $demo['sender_zone'] }}</p>
        <p>{{ $brandPhone }}</p>
        <p class="label-message">{{ $brandMessage }}</p>
    </div>
</section>