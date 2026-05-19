@props(['label', 'value', 'sub' => null, 'tone' => 'blue'])

<article class="metric">
    <span>
        <small>{{ $label }}</small>
        <strong>{{ $value }}</strong>
        @if ($sub)<em>{{ $sub }}</em>@endif
    </span>
    <i class="{{ $tone }}">{{ strtoupper(substr($label, 0, 1)) }}</i>
</article>
