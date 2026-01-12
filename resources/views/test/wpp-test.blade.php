<!DOCTYPE html>
<html>
<head>
    <title>Test Koneksi WPPConnect</title>
    <style>
        body { font-family: Arial; margin: 30px; }
        input, textarea { width: 100%; padding: 10px; margin-top: 6px; }
        button { padding: 10px 20px; cursor: pointer; margin-top: 10px; }
        .box { padding: 15px; margin-top: 15px; border-radius: 5px; }
        .success { background: #d4edda; }
        .error { background: #f8d7da; }
    </style>
</head>
<body>

<h2>Test Koneksi WPPConnect</h2>

@if(session('result'))
    @php
        $r = session('result');
    @endphp

    <div class="box {{ isset($r['status']) && $r['status'] ? 'success' : 'error' }}">
        <strong>Response:</strong>
        <pre>{{ print_r($r, true) }}</pre>
    </div>
@endif

<form method="POST" action="{{ route('wpp.test.send') }}">
    @csrf

    <label>Nomor WhatsApp (628xxxx):</label>
    <input type="text" name="number" required placeholder="62812xxxxxx">

    <label>Pesan:</label>
    <textarea name="message" required>Halo ini test dari Laravel</textarea>

    <button type="submit">Kirim Test</button>
</form>

</body>
</html>
