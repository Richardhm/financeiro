@props(['user', 'size' => 34])
@php
    // Iniciais do primeiro e do ultimo nome: "Jordanna Goncalves" -> "JG"
    $partes = preg_split('/\s+/', trim($user->name ?? ''), -1, PREG_SPLIT_NO_EMPTY);
    $iniciais = strtoupper(
        mb_substr($partes[0] ?? '?', 0, 1) .
        (count($partes) > 1 ? mb_substr(end($partes), 0, 1) : '')
    );
    // So mostra a foto se o arquivo realmente existe (evita imagem quebrada)
    $temImagem = !empty($user->image) && file_exists(public_path($user->image));
@endphp
@if($temImagem)
    <img src="{{ asset($user->image) }}" alt="{{ $user->name }}"
         style="width:{{ $size }}px;height:{{ $size }}px;border-radius:50%;object-fit:cover;flex-shrink:0;">
@else
    <div style="width:{{ $size }}px;height:{{ $size }}px;border-radius:50%;background:#1a3a5c;border:1px solid #2a3d55;display:flex;align-items:center;justify-content:center;color:#7eb8f7;font-weight:700;font-size:{{ round($size * 0.38) }}px;letter-spacing:0.5px;flex-shrink:0;">{{ $iniciais }}</div>
@endif
