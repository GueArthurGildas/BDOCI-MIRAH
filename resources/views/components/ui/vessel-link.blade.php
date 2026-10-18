@props(['vessel'])

@if (Route::has('vessels.show') && auth()->user()?->can(App\Support\Authorization\Permission::VesselsView->value))
    <a href="{{ route('vessels.show', $vessel) }}" {{ $attributes }}>{{ $vessel->name }}</a>
@else
    <span {{ $attributes }}>{{ $vessel->name }}</span>
@endif
<div class="text-muted f-12">{{ $vessel->registration_number }}</div>
