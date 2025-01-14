<style>
    /* Container Styles */
    .settings-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }

    .settings-header {
        background: var(--bg-secondary);
        padding: 20px 30px;
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .settings-header h1 {
        margin: 0;
        font-size: 1.8em;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .settings-section {
        background: var(--bg-secondary);
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .settings-section h2 {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 1.3em;
        color: var(--text-primary);
    }

    /* Theme Options */
    .theme-options {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .theme-option {
        cursor: pointer;
        flex: 1;
        min-width: 200px;
    }

    .theme-option input[type="radio"] {
        display: none;
    }

    .theme-preview {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 15px 25px;
        border-radius: 10px;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .theme-preview.light {
        background: #f8f9fa;
        color: #2c3e50;
    }

    .theme-preview.dark {
        background: #2d3748;
        color: #fff;
    }

    .theme-option input[type="radio"]:checked + .theme-preview {
        border-color: #3498db;
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.3);
    }

    /* Toggle Switch */
    .toggle-switch {
        position: relative;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        gap: 10px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
        background-color: #ccc;
        border-radius: 24px;
        transition: .4s;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        border-radius: 50%;
        transition: .4s;
    }

    input:checked + .toggle-slider {
        background-color: #3498db;
    }

    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }

    /* Form Styles */
    .settings-form {
        max-width: 400px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: var(--text-primary);
        font-weight: 500;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 1em;
        background: var(--bg-primary);
        color: var(--text-primary);
        transition: all 0.3s ease;
    }

    .form-group input:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }

    /* Button Styles */
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1em;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: #3498db;
        color: white;
    }

    .btn-primary:hover {
        background: #2980b9;
    }

    .btn-danger {
        background: #e74c3c;
        color: white;
    }

    .btn-danger:hover {
        background: #c0392b;
    }

    /* Danger Zone */
    .danger-zone {
        border: 1px solid #e74c3c;
    }

    .warning-text {
        color: #e74c3c;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .warning-text i {
        font-size: 1.2em;
    }

    /* Theme-specific styles */
    body.theme-light {
        --bg-primary: #f8f9fa;
        --bg-secondary: #ffffff;
        --text-primary: #2c3e50;
        --text-secondary: #34495e;
        --border-color: #ddd;
    }

    body.theme-dark {
        --bg-primary: #1a202c;
        --bg-secondary: #2d3748;
        --text-primary: #ffffff;
        --text-secondary: #e2e8f0;
        --border-color: #4a5568;
    }

    /* Message Styles */
    .message {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* Dark mode input autofill handling */
    body.theme-dark input:-webkit-autofill,
    body.theme-dark input:-webkit-autofill:hover,
    body.theme-dark input:-webkit-autofill:focus {
        -webkit-text-fill-color: var(--text-primary);
        -webkit-box-shadow: 0 0 0px 1000px var(--bg-secondary) inset;
        transition: background-color 5000s ease-in-out 0s;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .settings-container {
            padding: 10px;
        }

        .settings-section {
            padding: 15px;
        }

        .settings-form {
            max-width: 100%;
        }

        .theme-options {
            flex-direction: column;
        }

        .theme-preview {
            width: 100%;
            justify-content: center;
        }

        .settings-header h1 {
            font-size: 1.5em;
        }

        .btn {
            width: 100%;
        }
    }

    /* Accessibility */
    @media (prefers-reduced-motion: reduce) {
        * {
            transition: none !important;
        }
    }

    /* Focus styles for accessibility */
    .btn:focus,
    input:focus,
    .theme-option input[type="radio"]:focus + .theme-preview {
        outline: 2px solid #3498db;
        outline-offset: 2px;
    }

    /* High contrast mode support */
    @media (forced-colors: active) {
        .btn,
        .toggle-slider,
        .theme-preview {
            border: 1px solid currentColor;
        }
    }
</style>