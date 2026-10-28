@props(['active'])

<div class="m-b-20">
    @foreach (['messages' => 'communications::communications.tabs.messages', 'notes' => 'communications::communications.tabs.notes', 'templates' => 'communications::communications.tabs.templates', 'rules' => 'communications::communications.tabs.rules', 'channels' => 'communications::communications.tabs.channels'] as $section => $label)
        <a href="{{ route("communications.{$section}.index") }}" @class(['btn btn-sm m-r-5', 'btn-primary' => $active === $section, 'btn-outline-primary' => $active !== $section])>{{ __($label) }}</a>
    @endforeach
</div>
