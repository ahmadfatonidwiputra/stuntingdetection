@if(session('login_welcome'))
    @php($welcome = session('login_welcome'))
    <style>
        .welcome-popup-overlay {
            position: fixed;
            inset: 0;
            z-index: 10000;
            background: rgba(0, 0, 0, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: welcome-overlay-in 0.2s ease;
        }
        .welcome-popup-overlay.welcome-popup-hide { animation: welcome-overlay-out 0.25s ease forwards; }
        @keyframes welcome-overlay-in { from { opacity: 0; } to { opacity: 1; } }
        @keyframes welcome-overlay-out { from { opacity: 1; } to { opacity: 0; } }
        .welcome-popup-box {
            background: var(--bg-card, #16213e);
            border: 1px solid var(--glass-border, rgba(255,255,255,0.1));
            border-radius: 18px;
            padding: 36px 32px;
            max-width: 380px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            animation: welcome-box-in 0.3s ease;
        }
        @keyframes welcome-box-in {
            from { opacity: 0; transform: scale(0.9) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .welcome-popup-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 16px;
        }
        .welcome-popup-title {
            font-size: 19px;
            font-weight: 800;
            color: var(--text, #f1f5f9);
            margin-bottom: 6px;
        }
        .welcome-popup-message {
            font-size: 14px;
            color: var(--text-muted, #94a3b8);
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .welcome-popup-close {
            padding: 10px 28px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-family: inherit;
            background: linear-gradient(135deg, #0ea5e9, #8b5cf6);
            color: #fff;
        }
    </style>
    <div id="welcome-popup-overlay" class="welcome-popup-overlay">
        <div class="welcome-popup-box">
            <div class="welcome-popup-icon">👋</div>
            <div class="welcome-popup-title">Selamat Datang, {{ $welcome['name'] }}!</div>
            <div class="welcome-popup-message">
                Anda berhasil login{{ $welcome['role'] ? ' sebagai ' . $welcome['role'] : '' }}.
            </div>
            <button type="button" class="welcome-popup-close" id="welcome-popup-close-btn">Lanjutkan</button>
        </div>
    </div>
    <script>
        (function () {
            var overlay = document.getElementById('welcome-popup-overlay');

            function closeWelcome() {
                overlay.classList.add('welcome-popup-hide');
                setTimeout(function () { overlay.remove(); }, 250);
            }

            document.getElementById('welcome-popup-close-btn').addEventListener('click', closeWelcome);
            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeWelcome();
            });

            setTimeout(closeWelcome, 3500);
        })();
    </script>
@endif
