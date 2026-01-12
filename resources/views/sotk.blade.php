<!DOCTYPE html>
<html>

<head>
    <title>Struktur Organisasi</title>
    <script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>

    <script>
        mermaid.initialize({
            startOnLoad: true
        });
    </script>

    <style>
        body {
            font-family: Arial;
            padding: 20px;
        }
    </style>
</head>

<body>

    <h2><b>Bagan Struktur Organisasi</b></h2>

    @php
        function idFormat($text)
        {
            return preg_replace('/[^A-Za-z0-9]/', '_', $text);
        }
    @endphp

    <div class="mermaid">
        graph TD

        %% ============================
        %% 1. CETAK NODE DULU
        %% ============================
        @foreach ($positions as $p)
            @php
                $id = idFormat($p->name);
                $user = optional(optional($p->users)->first())->name ?? '';
                $label = $p->name . ($user ? "<br>$user" : '');
            @endphp

            {{ $id }}["{!! $label !!}"]
        @endforeach


        %% ============================
        %% 2. CETAK RELASI (PANAH)
        %% ============================
        @foreach ($positions as $p)
            @if ($p->parent_id && $p->parent)
                {{ idFormat($p->parent->name) }} --> {{ idFormat($p->name) }}
            @endif
        @endforeach

    </div>

</body>

</html>
