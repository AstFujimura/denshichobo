@php
    $catalog = config('tenancy.features', []);
    $order = ['tameru', 'flow', 'schedule', 'card', 'transcribe'];
    $groups = [];
    $apps = [];
    $childrenOf = [];

    foreach ($catalog as $key => $meta) {
        $parent = $meta['parent'] ?? null;
        if ($parent) {
            $childrenOf[$parent][] = ['key' => $key] + $meta;
        }
    }

    foreach ($order as $key) {
        if (! isset($catalog[$key]) || isset($catalog[$key]['parent']) || ! empty($catalog[$key]['hidden'])) {
            continue;
        }
        $item = ['key' => $key] + $catalog[$key];
        if (! empty($childrenOf[$key])) {
            $groups[] = $item;
        } else {
            $apps[] = $item;
        }
    }

    $featureChecked = function (string $key, array $meta) use ($featureMode, $featureStates) {
        if (old('features') !== null) {
            return (bool) old('features.'.$key);
        }

        if ($featureMode === 'edit') {
            return (bool) ($featureStates[$key]['enabled'] ?? false);
        }

        return (bool) ($meta['default'] ?? false);
    };
@endphp

<div class="feature-catalog">
    @foreach ($groups as $feature)
        @php $parentOn = $featureChecked($feature['key'], $feature); @endphp
        <div class="feature-group">
            <label class="feature-app">
                <span class="feature-app-icon-wrap">
                    <img src="{{ asset($feature['icon']) }}" alt="" class="feature-app-icon">
                </span>
                <span class="feature-app-text">
                    <span class="feature-app-name">{{ $feature['name'] }}</span>
                    <span class="feature-app-desc">{{ $feature['description'] }}</span>
                </span>
                <input
                    type="checkbox"
                    name="features[{{ $feature['key'] }}]"
                    value="1"
                    @checked($featureChecked($feature['key'], $feature))
                >
            </label>
            <div class="feature-custom" @if (! $parentOn) hidden @endif>
                <p class="feature-custom-label">TAMERUのカスタム機能</p>
                <div class="feature-custom-list">
                    @foreach ($childrenOf[$feature['key']] as $child)
                        <label class="feature-child">
                            <span class="feature-mark" aria-hidden="true">{{ $child['mark'] }}</span>
                            <span class="feature-app-text">
                                <span class="feature-app-name">{{ $child['name'] }}</span>
                                <span class="feature-app-desc">{{ $child['description'] }}</span>
                            </span>
                            <input
                                type="checkbox"
                                name="features[{{ $child['key'] }}]"
                                value="1"
                                @checked($parentOn && $featureChecked($child['key'], $child))
                                @disabled(! $parentOn)
                            >
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <div class="feature-app-grid">
        @foreach ($apps as $feature)
            <label class="feature-app">
                <span class="feature-app-icon-wrap">
                    <img src="{{ asset($feature['icon']) }}" alt="" class="feature-app-icon">
                </span>
                <span class="feature-app-text">
                    <span class="feature-app-name">{{ $feature['name'] }}</span>
                    <span class="feature-app-desc">{{ $feature['description'] }}</span>
                </span>
                <input
                    type="checkbox"
                    name="features[{{ $feature['key'] }}]"
                    value="1"
                    @checked($featureChecked($feature['key'], $feature))
                >
            </label>
        @endforeach
    </div>
</div>
