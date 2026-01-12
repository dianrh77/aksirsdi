<div class="mermaid">
    graph TD

    @foreach ($positions as $p)
        @php
            $user = $p->users->first();
            $label = $p->name;
            if ($user) {
                $label .= '<br>' . $user->name;
            }
        @endphp

        @if ($p->parent)
            {{ Str::slug($p->parent->name, '_') }}["{!! $p->parent->name !!}<br>{{ optional($p->parent->users->first())->name }}"]
            -->
            {{ Str::slug($p->name, '_') }}["{!! $label !!}"]
        @else
            {{ Str::slug($p->name, '_') }}["{!! $label !!}"]
        @endif
    @endforeach
</div>
