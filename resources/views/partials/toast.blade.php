@if(Auth::check() && (session('success') || session('error')))
    <style>
        .app-toast {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 380px;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            animation: app-toast-in 0.25s ease;
        }
        .app-toast-success {
            background: #10b981;
            color: #fff;
        }
        .app-toast-error {
            background: #ef4444;
            color: #fff;
        }
        .app-toast-close {
            background: none;
            border: none;
            color: inherit;
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
            opacity: 0.8;
            padding: 0 0 0 6px;
        }
        .app-toast-close:hover { opacity: 1; }
        .app-toast-hide { animation: app-toast-out 0.25s ease forwards; }
        @keyframes app-toast-in {
            from { opacity: 0; transform: translateX(24px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes app-toast-out {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(24px); }
        }
        @media (max-width: 600px) {
            .app-toast { left: 16px; right: 16px; max-width: none; top: 16px; }
        }
    </style>
    <div id="app-toast" class="app-toast {{ session('error') ? 'app-toast-error' : 'app-toast-success' }}">
        <span>{{ session('error') ? '⚠️' : '✅' }}</span>
        <span style="flex: 1;">{{ session('error') ?? session('success') }}</span>
        <button type="button" class="app-toast-close" onclick="document.getElementById('app-toast').remove()">&times;</button>
    </div>
    <script>
        setTimeout(function () {
            var toast = document.getElementById('app-toast');
            if (!toast) return;
            toast.classList.add('app-toast-hide');
            setTimeout(function () { toast.remove(); }, 250);
        }, 4000);
    </script>
@endif
