<style>
    .fi-simple-layout {
        min-height: 100vh;
        color-scheme: light;
        background:
            radial-gradient(circle at 74% 9%, rgba(20, 184, 166, 0.16), transparent 30rem),
            radial-gradient(circle at 14% 78%, rgba(16, 185, 129, 0.12), transparent 27rem),
            linear-gradient(180deg, #eefdfb 0%, #f7fbff 62%, #eef3fb 100%);
    }

    .fi-simple-main-ctn {
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 1.5rem;
    }

    .fi-simple-main {
        width: min(100%, 27rem) !important;
        max-width: 27rem !important;
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .fi-simple-page {
        width: min(calc(100vw - 2rem), 27rem) !important;
        max-width: 27rem !important;
        padding: 1.75rem 1.65rem 1.2rem;
        border-radius: 1.5rem;
        background: rgba(255, 255, 255, 0.96) !important;
        box-shadow:
            0 2rem 5rem rgba(15, 23, 42, 0.15),
            0 0.75rem 1.75rem rgba(15, 118, 110, 0.10) !important;
        backdrop-filter: blur(18px);
    }

    .fi-simple-header {
        gap: 0.55rem;
        margin-bottom: 1rem;
        text-align: center;
    }

    .fi-simple-header .fi-logo {
        display: none;
    }

    .fi-simple-header::before {
        display: block;
        width: 5rem;
        height: 5rem;
        margin: 0 auto 0.35rem;
        background: url('/icons/kicap-event-logo.svg') center / contain no-repeat;
        content: "";
        filter: drop-shadow(0 0.75rem 1.25rem rgba(15, 118, 110, 0.22));
    }

    .fi-simple-header-heading {
        color: #0f172a;
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: 0;
    }

    .fi-simple-page-content {
        width: 100%;
        gap: 0.8rem;
    }

    .fi-simple-page form,
    .fi-simple-page .fi-form,
    .fi-simple-page .fi-sc,
    .fi-simple-page .fi-sc-component,
    .fi-simple-page .fi-fo-component-ctn {
        width: 100% !important;
        max-width: none !important;
    }

    .fi-fo-field {
        width: 100%;
        gap: 0.35rem;
    }

    .fi-fo-field-label,
    .fi-fo-field-label-content {
        color: #0f172a;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0;
    }

    .fi-input-wrp {
        width: 100%;
        min-height: 2.75rem;
        border-radius: 0.95rem !important;
        background: #ffffff !important;
        box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.12) !important;
    }

    .fi-input-wrp:focus-within {
        box-shadow:
            inset 0 0 0 2px #14b8a6,
            0 0 0 0.25rem rgba(20, 184, 166, 0.12) !important;
    }

    .fi-input {
        min-height: 2.75rem !important;
        color: #0f172a !important;
        font-size: 0.88rem !important;
        font-weight: 650 !important;
        line-height: 1.15 !important;
    }

    .fi-input::placeholder {
        color: #9ca3af !important;
        opacity: 1;
    }

    .fi-checkbox-input {
        border-radius: 0.25rem !important;
        color: #0f766e !important;
    }

    .fi-checkbox-input:checked {
        background-color: #0f766e !important;
        border-color: #0f766e !important;
    }

    .fi-form-actions {
        margin-top: 0.35rem;
    }

    .fi-simple-page .fi-form-actions .fi-btn,
    .fi-simple-page button[type="submit"] {
        min-height: 2.75rem;
        width: 100%;
        border: 0 !important;
        border-radius: 0.95rem !important;
        background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%) !important;
        color: #ffffff !important;
        font-size: 0.95rem;
        font-weight: 800;
        text-align: center;
        box-shadow: 0 1.15rem 1.8rem rgba(20, 184, 166, 0.28) !important;
    }

    .fi-simple-page .fi-form-actions .fi-btn:hover,
    .fi-simple-page button[type="submit"]:hover {
        filter: brightness(1.02);
    }

    .fi-simple-page::after {
        display: block;
        margin-top: 1rem;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 400;
        text-align: center;
        content: "V2.4.1 © 2026 Kulino";
    }

    @media (max-width: 480px) {
        .fi-simple-main-ctn {
            padding: 1rem;
        }

        .fi-simple-page {
            border-radius: 1.2rem;
            padding: 1.4rem 1rem 1rem;
        }
    }
</style>
