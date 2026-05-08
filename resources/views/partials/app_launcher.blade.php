{{--
    ヘッダー右側に置くアプリ切替ランチャー（黒丸9つアイコン）
    タップでパネルを開き、利用可能なアプリのアイコンと名称を表示する。
--}}
<div class="app_launcher">
    <button type="button" class="app_launcher_button" id="app_launcher_button"
            aria-haspopup="dialog" aria-expanded="false"
            aria-controls="app_launcher_panel" aria-label="アプリ一覧">
        <span class="app_launcher_dots" aria-hidden="true">
            <span></span><span></span><span></span>
            <span></span><span></span><span></span>
            <span></span><span></span><span></span>
        </span>
    </button>
    <div class="app_launcher_panel" id="app_launcher_panel" role="dialog" aria-label="アプリ一覧" hidden>
        <div class="app_launcher_panel_header">アプリを切り替える</div>
        <div class="app_launcher_grid">
            @if (App\Models\Version::where('tameru', true)->first())
            <a href="{{ route('topGet') }}" class="app_launcher_item">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/header/tameru_logo_only.svg') }}" alt="" class="app_launcher_icon">
                <div class="app_launcher_text">
                    <div class="app_launcher_name">TAMERU</div>
                    <div class="app_launcher_desc">電子帳簿保存システム</div>
                </div>
            </a>
            @endif
            @if (App\Models\Version::where('フロー', true)->first())
            <a href="{{ route('workflow') }}" class="app_launcher_item">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/header/rapid_logo_only.svg') }}" alt="" class="app_launcher_icon">
                <div class="app_launcher_text">
                    <div class="app_launcher_name">Rapid</div>
                    <div class="app_launcher_desc">電子申請システム</div>
                </div>
            </a>
            @endif
            @if (App\Models\Version::where('スケジュール', true)->first())
            <a href="{{ route('scheduleget') }}" class="app_launcher_item">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/header/skett_logo_only.svg') }}" alt="" class="app_launcher_icon">
                <div class="app_launcher_text">
                    <div class="app_launcher_name">Skett</div>
                    <div class="app_launcher_desc">スケジュール管理システム</div>
                </div>
            </a>
            @endif
            @if (App\Models\Version::where('名刺', true)->first())
            <a href="{{ route('cardviewget') }}" class="app_launcher_item">
                <img src="{{ asset(config('prefix.prefix').'/'.'img/header/readbridge_icon.svg') }}" alt="" class="app_launcher_icon">
                <div class="app_launcher_text">
                    <div class="app_launcher_name">ReadBridge</div>
                    <div class="app_launcher_desc">名刺管理システム</div>
                </div>
            </a>
            @endif
        </div>
    </div>
</div>

@once
<script>
(function () {
    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }
    ready(function () {
        var btn = document.getElementById('app_launcher_button');
        var panel = document.getElementById('app_launcher_panel');
        if (!btn || !panel) {
            return;
        }

        // 親ヘッダーの z-index / stacking context に阻まれて他要素に隠れる問題を避けるため、
        // パネルを <body> 直下に移動する（位置はボタン基準で fixed 配置）
        if (panel.parentNode !== document.body) {
            document.body.appendChild(panel);
        }

        function positionPanel() {
            var rect = btn.getBoundingClientRect();
            panel.style.top = Math.max(8, rect.bottom + 8) + 'px';
            var rightOffset = Math.max(8, window.innerWidth - rect.right);
            panel.style.right = rightOffset + 'px';
            panel.style.left = 'auto';
        }
        function closePanel() {
            panel.hidden = true;
            btn.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('app_launcher_open');
        }
        function openPanel() {
            positionPanel();
            panel.hidden = false;
            btn.setAttribute('aria-expanded', 'true');
            document.body.classList.add('app_launcher_open');
        }

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (panel.hidden) {
                openPanel();
            } else {
                closePanel();
            }
        });
        document.addEventListener('click', function (e) {
            if (panel.hidden) {
                return;
            }
            if (panel.contains(e.target) || btn.contains(e.target)) {
                return;
            }
            closePanel();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !panel.hidden) {
                closePanel();
            }
        });
        window.addEventListener('resize', function () {
            if (!panel.hidden) {
                positionPanel();
            }
        });
        window.addEventListener('scroll', function () {
            if (!panel.hidden) {
                positionPanel();
            }
        }, true);
    });
})();
</script>
@endonce
