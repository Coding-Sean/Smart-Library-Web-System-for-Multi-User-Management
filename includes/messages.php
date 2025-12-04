<?php
// filepath: c:\xampp\htdocs\MyLibrary\includes\messages.php

function displayMessage() {
    $hasSuccess = isset($_GET['success']);
    $hasError = isset($_GET['error']);
    
    if (!$hasSuccess && !$hasError) {
        return '';
    }
    
    $message = $hasSuccess ? htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8') : htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8');
    $type = $hasSuccess ? 'success' : 'error';
    $bgColor = $hasSuccess ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)';
    $icon = $hasSuccess ? '✓' : '✕';
    
    return <<<HTML
    <div class="custom-message-alert" id="customMessageAlert">
        <div class="custom-message-container">
            <div class="custom-message-icon">{$icon}</div>
            <div class="custom-message-text">{$message}</div>
            <button class="custom-message-close" onclick="closeCustomMessage()">×</button>
        </div>
    </div>
    
    <style>
        .custom-message-alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            animation: slideDownFade 0.4s ease-out;
        }
        
        .custom-message-container {
            background: {$bgColor};
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 15px;
            min-width: 350px;
            max-width: 600px;
        }
        
        .custom-message-icon {
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .custom-message-text {
            flex: 1;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.4;
        }
        
        .custom-message-close {
            background: transparent;
            border: none;
            color: white;
            font-size: 30px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            line-height: 1;
            opacity: 0.8;
            transition: opacity 0.3s;
            flex-shrink: 0;
        }
        
        .custom-message-close:hover {
            opacity: 1;
        }
        
        @keyframes slideDownFade {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
        
        @keyframes slideUpFade {
            from {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
            to {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
        }
        
        .custom-message-alert.hiding {
            animation: slideUpFade 0.4s ease-out forwards;
        }
        
        @media (max-width: 768px) {
            .custom-message-container {
                min-width: auto;
                max-width: 90vw;
                margin: 0 20px;
            }
        }
    </style>
    
    <script>
        function closeCustomMessage() {
            const alert = document.getElementById('customMessageAlert');
            if (alert) {
                alert.classList.add('hiding');
                setTimeout(function() {
                    alert.remove();
                    // Clean URL
                    const url = window.location.href.split('?')[0];
                    window.history.replaceState({}, document.title, url);
                }, 400);
            }
        }
        
        // Auto close after 5 seconds
        setTimeout(closeCustomMessage, 5000);
    </script>
HTML;
}
?>