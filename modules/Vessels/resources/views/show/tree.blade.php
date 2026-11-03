<x-ui.card data-guide="tree" :title="__('vessels::vessels.folders.title')">
    <x-slot:actions>
        <ul class="list-unstyled card-option">
            <li><a href="#" data-gs-tree="open" data-bs-toggle="tooltip" data-bs-title="{{ __('vessels::vessels.folders.expand') }}"><i class="feather icon-plus-square"></i></a></li>
            <li><a href="#" data-gs-tree="close" data-bs-toggle="tooltip" data-bs-title="{{ __('vessels::vessels.folders.collapse') }}"><i class="feather icon-minus-square"></i></a></li>
        </ul>
    </x-slot:actions>

    <form method="GET" action="{{ route('vessels.index') }}" class="gs-tree-search">
        <input type="search" name="search" class="form-control form-control-sm" placeholder="{{ __('vessels::vessels.folders.search') }}" aria-label="{{ __('vessels::vessels.folders.search') }}">
    </form>

    <ul class="gs-tree" data-gs-tree-root>
        @foreach ($tree as $folder)
            @php($containsActive = collect($folder['leaves'])->contains(fn ($leaf) => $leaf['key'] === $pane))
            <li>
                <details @if ($containsActive) open @endif>
                    <summary>
                        <i class="feather {{ $folder['icon'] }}"></i>
                        <span>{{ __('vessels::vessels.folders.'.$folder['key']) }}</span>
                        @if ($folder['count'] > 0)
                            <span class="gs-tree-count">{{ $folder['count'] }}</span>
                        @endif
                    </summary>
                    <ul>
                        @foreach ($folder['leaves'] as $leaf)
                            <li>
                                @if ($leaf['items'] !== [])
                                    <details @if ($leaf['key'] === $pane) open @endif>
                                        <summary @class(['is-active' => $leaf['key'] === $pane])>
                                            <a href="{{ route('vessels.show', [$vessel, 'view' => 'folders', 'pane' => $leaf['key']]) }}">
                                                <i class="feather icon-folder"></i> {{ __('vessels::vessels.tabs.'.$leaf['key']) }}
                                            </a>
                                            <span class="gs-tree-count">{{ $leaf['count'] }}</span>
                                        </summary>
                                        <ul>
                                            @foreach ($leaf['items'] as $item)
                                                <li>
                                                    <a href="{{ route('vessels.show', [$vessel, 'view' => 'folders', 'pane' => $leaf['key']]) }}#item-{{ $leaf['key'] }}-{{ $item['id'] }}">
                                                        <i class="feather icon-file"></i> {{ $item['label'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @else
                                    <a href="{{ route('vessels.show', [$vessel, 'view' => 'folders', 'pane' => $leaf['key']]) }}" @class(['gs-tree-leaf', 'is-active' => $leaf['key'] === $pane])>
                                        <i class="feather icon-file-text"></i>
                                        <span>{{ __('vessels::vessels.tabs.'.$leaf['key']) }}</span>
                                        @if ($leaf['counted'])
                                            <span class="gs-tree-count">{{ $leaf['count'] }}</span>
                                        @endif
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </details>
            </li>
        @endforeach
    </ul>
</x-ui.card>
