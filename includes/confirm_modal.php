<?php
// filepath: c:\xampp\htdocs\MyLibrary\includes\confirm_modal.php
function getConfirmModal() {
    return <<<HTML
    <!-- Custom Confirm Modal -->
    <div class="custom-confirm-overlay" id="customConfirmOverlay" style="display: none;">
        <div class="custom-confirm-modal">
            <div class="custom-confirm-header">
                <h5 class="custom-confirm-title" id="confirmTitle">Confirm Action</h5>
            </div>
            <div class="custom-confirm-body">
                <p class="custom-confirm-message" id="confirmMessage"></p>
            </div>
            <div class="custom-confirm-footer">
                <button class="custom-btn custom-btn-cancel" onclick="cancelConfirm()">Cancel</button>
                <button class="custom-btn custom-btn-confirm" id="confirmButton">Confirm</button>
            </div>
        </div>
    </div>

    <style>
        .custom-confirm-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease-out;
        }

        .custom-confirm-modal {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            min-width: 400px;
            max-width: 500px;
            animation: slideUp 0.3s ease-out;
        }

        .custom-confirm-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 25px;
            border-radius: 15px 15px 0 0;
        }

        .custom-confirm-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .custom-confirm-body {
            padding: 25px;
        }

        .custom-confirm-message {
            margin: 0;
            font-size: 15px;
            color: #2c3e50;
            line-height: 1.6;
        }

        .custom-confirm-footer {
            padding: 0 25px 25px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .custom-btn {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .custom-btn-cancel {
            background: #e0e0e0;
            color: #555;
        }

        .custom-btn-cancel:hover {
            background: #d0d0d0;
        }

        .custom-btn-confirm {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .custom-btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .custom-confirm-modal {
                min-width: 90%;
                max-width: 90%;
                margin: 0 20px;
            }
        }
    </style>

    <script>
        let confirmCallback = null;

        function customConfirm(message, callback) {
            const overlay = document.getElementById('customConfirmOverlay');
            const messageEl = document.getElementById('confirmMessage');
            const confirmBtn = document.getElementById('confirmButton');
            
            messageEl.textContent = message;
            overlay.style.display = 'flex';
            confirmCallback = callback;
            
            // Remove old event listener and add new one
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            document.getElementById('confirmButton').onclick = function() {
                overlay.style.display = 'none';
                if (confirmCallback) {
                    confirmCallback();
                }
            };
        }

        function cancelConfirm() {
            document.getElementById('customConfirmOverlay').style.display = 'none';
            confirmCallback = null;
        }

        // Close on overlay click
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('customConfirmOverlay')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    cancelConfirm();
                }
            });
        });
    </script>
HTML;
}
?>