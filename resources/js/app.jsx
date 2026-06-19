import React from 'react';
import { createRoot } from 'react-dom/client';
import { registerSW } from 'virtual:pwa-register';
import '../css/app.css';

registerSW({ immediate: true });

function KicapApp() {
    return (
        <main className="app-shell">
            <section className="hero-card">
                <div className="eyebrow">Kicap LPJ · Slice 00</div>
                <h1>Pondasi aplikasi LPJ siap dibangun.</h1>
                <p>
                    Baseline React PWA untuk user lapangan sudah aktif. Slice berikutnya akan mulai
                    mengisi role, master LPJ, profil lembaga, dan data awal.
                </p>

                <div className="status-grid">
                    <div>
                        <span>Backend</span>
                        <strong>Laravel</strong>
                    </div>
                    <div>
                        <span>Admin</span>
                        <strong>Filament</strong>
                    </div>
                    <div>
                        <span>User App</span>
                        <strong>React PWA</strong>
                    </div>
                    <div>
                        <span>Database</span>
                        <strong>MySQL</strong>
                    </div>
                </div>

                <div className="action-row">
                    <a href="/admin">Buka Admin Panel</a>
                    <a href="/health">Cek Health</a>
                </div>
            </section>
        </main>
    );
}

const root = document.getElementById('kicap-lpj-root');

if (root) {
    createRoot(root).render(<KicapApp />);
}
