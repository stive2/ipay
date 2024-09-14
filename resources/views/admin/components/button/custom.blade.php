@isset($permission)
    @if (admin_permission_by_name($permission))
        <button type="{{ $type ?? "button" }}" class="btn {{ $class ?? "" }} btn--base ">@isset($icon)<i class="{{ $icon }}"></i>@endisset {{ __($text ?? "") }}</button>
    @endif
@else
    <button type="{{ $type ?? "button" }}" class="btn {{ $class ?? "" }} btn--base ">@isset($icon)<i class="{{ $icon }}"></i>@endisset {{ __($text ?? "") }}</button>
@endisset
