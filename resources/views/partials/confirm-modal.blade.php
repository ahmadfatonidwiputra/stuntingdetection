@if(Auth::check())
    <style>
        .confirm-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9998;
            background: rgba(0, 0, 0, 0.55);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .confirm-modal-overlay.active { display: flex; }
        .confirm-modal-box {
            background: var(--bg-card, #16213e);
            border: 1px solid var(--glass-border, rgba(255,255,255,0.1));
            border-radius: 16px;
            padding: 28px;
            max-width: 380px;
            width: 100%;
            text-align: center;
            animation: confirm-modal-in 0.2s ease;
        }
        @keyframes confirm-modal-in {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .confirm-modal-icon { font-size: 36px; margin-bottom: 12px; }
        .confirm-modal-title { font-size: 17px; font-weight: 700; margin-bottom: 8px; color: var(--text, #f1f5f9); }
        .confirm-modal-message { font-size: 14px; color: var(--text-muted, #94a3b8); margin-bottom: 22px; line-height: 1.5; }
        .confirm-modal-actions { display: flex; gap: 10px; justify-content: center; }
        .confirm-modal-actions button {
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-family: inherit;
        }
        #confirm-modal-cancel {
            background: transparent;
            border: 1px solid var(--glass-border, rgba(255,255,255,0.15));
            color: var(--text-secondary, #cbd5e1);
        }
        #confirm-modal-ok {
            background: #ef4444;
            color: #fff;
        }
    </style>
    <div id="confirm-modal-overlay" class="confirm-modal-overlay">
        <div class="confirm-modal-box">
            <div class="confirm-modal-icon">⚠️</div>
            <div class="confirm-modal-title">Konfirmasi</div>
            <div class="confirm-modal-message" id="confirm-modal-message"></div>
            <div class="confirm-modal-actions">
                <button type="button" id="confirm-modal-cancel">Batal</button>
                <button type="button" id="confirm-modal-ok">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>
    <script>
        (function () {
            var overlay = document.getElementById('confirm-modal-overlay');
            var messageEl = document.getElementById('confirm-modal-message');
            var pendingForm = null;

            function closeModal() {
                overlay.classList.remove('active');
                pendingForm = null;
            }

            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (form instanceof HTMLFormElement && form.hasAttribute('data-confirm')) {
                    e.preventDefault();
                    pendingForm = form;
                    messageEl.textContent = form.getAttribute('data-confirm');
                    overlay.classList.add('active');
                }
            });

            document.getElementById('confirm-modal-cancel').addEventListener('click', closeModal);

            document.getElementById('confirm-modal-ok').addEventListener('click', function () {
                var form = pendingForm;
                closeModal();
                if (form) {
                    HTMLFormElement.prototype.submit.call(form);
                }
            });

            overlay.addEventListener('click', function (e) {
                if (e.target === overlay) closeModal();
            });
        })();
    </script>
@endif
