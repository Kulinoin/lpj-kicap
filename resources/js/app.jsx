import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import {
    CalendarDays,
    Camera,
    CheckCircle2,
    ClipboardList,
    Home,
    LockKeyhole,
    LogOut,
    MapPin,
    Phone,
    PlusCircle,
    Save,
    UserRound,
    UsersRound,
} from 'lucide-react';
import { registerSW } from 'virtual:pwa-register';
import '../css/app.css';

registerSW({ immediate: true });

const navItems = [
    { key: 'beranda', label: 'Beranda', icon: Home },
    { key: 'lpj', label: 'LPJ', icon: ClipboardList },
    { key: 'input', label: 'Input', icon: PlusCircle, isPrimary: true },
    { key: 'profil', label: 'Profil', icon: UserRound },
];

const emptyProfile = {
    name: '',
    username: '',
    email: '',
    whatsapp: '',
    avatar_url: null,
};

function formatDateRange(lpj) {
    if (!lpj.start_date && !lpj.end_date) {
        return 'Tanggal belum diisi';
    }

    if (lpj.start_date === lpj.end_date || !lpj.end_date) {
        return lpj.start_date;
    }

    return `${lpj.start_date} - ${lpj.end_date}`;
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function KicapApp() {
    const [lpjs, setLpjs] = useState([]);
    const [profile, setProfile] = useState(emptyProfile);
    const [profileForm, setProfileForm] = useState({
        name: '',
        whatsapp: '',
        password: '',
        password_confirmation: '',
    });
    const [profilePhoto, setProfilePhoto] = useState(null);
    const [profilePhotoPreview, setProfilePhotoPreview] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isSavingProfile, setIsSavingProfile] = useState(false);
    const [profileMessage, setProfileMessage] = useState('');
    const [activeNav, setActiveNav] = useState('beranda');

    useEffect(() => {
        let isMounted = true;

        Promise.all([
            fetch('/api/app/lpjs', {
                headers: {
                    Accept: 'application/json',
                },
            }).then((response) => response.json()),
            fetch('/api/app/profile', {
                headers: {
                    Accept: 'application/json',
                },
            }).then((response) => response.json()),
        ])
            .then(([lpjPayload, profilePayload]) => {
                if (!isMounted) {
                    return;
                }

                const nextProfile = profilePayload.data ?? emptyProfile;

                setLpjs(lpjPayload.data ?? []);
                setProfile(nextProfile);
                setProfileForm({
                    name: nextProfile.name ?? '',
                    whatsapp: nextProfile.whatsapp ?? '',
                    password: '',
                    password_confirmation: '',
                });
            })
            .catch(() => {
                if (isMounted) {
                    setLpjs([]);
                    setProfile(emptyProfile);
                }
            })
            .finally(() => {
                if (isMounted) {
                    setIsLoading(false);
                }
            });

        return () => {
            isMounted = false;
        };
    }, []);

    const handleLogout = () => {
        fetch('/app/logout', {
            method: 'POST',
            headers: {
                Accept: 'text/html',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
        }).then(() => {
            window.location.assign('/login');
        });
    };

    const handleProfilePhotoChange = (event) => {
        const file = event.target.files?.[0] ?? null;

        setProfilePhoto(file);
        setProfilePhotoPreview(file ? URL.createObjectURL(file) : null);
    };

    const handleProfileSubmit = (event) => {
        event.preventDefault();
        setIsSavingProfile(true);
        setProfileMessage('');

        const formData = new FormData();
        formData.append('name', profileForm.name);
        formData.append('whatsapp', profileForm.whatsapp);

        if (profilePhoto) {
            formData.append('profile_photo', profilePhoto);
        }

        if (profileForm.password) {
            formData.append('password', profileForm.password);
            formData.append('password_confirmation', profileForm.password_confirmation);
        }

        fetch('/api/app/profile', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: formData,
        })
            .then(async (response) => {
                if (!response.ok) {
                    throw await response.json();
                }

                return response.json();
            })
            .then((payload) => {
                const nextProfile = payload.data ?? emptyProfile;

                setProfile(nextProfile);
                setProfilePhoto(null);
                setProfilePhotoPreview(null);
                setProfileForm({
                    name: nextProfile.name ?? '',
                    whatsapp: nextProfile.whatsapp ?? '',
                    password: '',
                    password_confirmation: '',
                });
                setProfileMessage('Profil tersimpan');
            })
            .catch(() => {
                setProfileMessage('Profil belum tersimpan');
            })
            .finally(() => {
                setIsSavingProfile(false);
            });
    };

    const totals = useMemo(() => {
        return {
            active: lpjs.filter((lpj) => lpj.status === 'aktif').length,
            finished: lpjs.filter((lpj) => lpj.status === 'finish').length,
        };
    }, [lpjs]);

    const avatarSource = profilePhotoPreview ?? profile.avatar_url;

    return (
        <main className="mobile-shell">
            <header className="app-header">
                <div className="brand-mark">
                    <img src="/icons/kicap-lpj.svg" alt="" />
                </div>
                <div>
                    <p className="section-kicker">Kicap LPJ</p>
                    <h1>{activeNav === 'profil' ? 'Profil pengguna' : 'Ruang kerja petugas'}</h1>
                </div>
                <button className="logout-button" type="button" onClick={handleLogout} aria-label="Keluar">
                    <LogOut size={18} strokeWidth={2.4} />
                </button>
            </header>

            {activeNav !== 'profil' && (
                <>
                    <section className="summary-grid" aria-label="Ringkasan LPJ">
                        <article className="summary-tile coral">
                            <div className="summary-icon">
                                <ClipboardList size={18} strokeWidth={2.5} />
                            </div>
                            <span>Aktif</span>
                            <strong>{totals.active}</strong>
                        </article>
                        <article className="summary-tile teal">
                            <div className="summary-icon">
                                <CheckCircle2 size={18} strokeWidth={2.5} />
                            </div>
                            <span>Finish</span>
                            <strong>{totals.finished}</strong>
                        </article>
                        <article className="summary-tile amber">
                            <div className="summary-icon">
                                <UsersRound size={18} strokeWidth={2.5} />
                            </div>
                            <span>Ditugaskan</span>
                            <strong>{lpjs.length}</strong>
                        </article>
                    </section>

                    <section className="lpj-section">
                        <div className="section-heading">
                            <div>
                                <p className="section-kicker">LPJ Saya</p>
                                <h2>Aktif dan finish</h2>
                            </div>
                        </div>

                        <div className="lpj-list">
                            {isLoading && <div className="empty-state">Memuat LPJ...</div>}

                            {!isLoading && lpjs.length === 0 && (
                                <div className="empty-state">Belum ada LPJ aktif atau finish yang ditugaskan.</div>
                            )}

                            {!isLoading &&
                                lpjs.map((lpj) => (
                                    <article className="lpj-card" key={lpj.id}>
                                        <div className="lpj-card-top">
                                            <span className={`status-pill ${lpj.status}`}>{lpj.status_label}</span>
                                            <span className="lpj-code">{lpj.code}</span>
                                        </div>
                                        <h3>{lpj.title}</h3>
                                        <p className="lpj-type">{lpj.type ?? '-'}</p>
                                        <div className="meta-list">
                                            <span>
                                                <CalendarDays size={15} strokeWidth={2.4} />
                                                {formatDateRange(lpj)}
                                            </span>
                                            <span>
                                                <MapPin size={15} strokeWidth={2.4} />
                                                {lpj.location ?? '-'}
                                            </span>
                                        </div>
                                    </article>
                                ))}
                        </div>
                    </section>
                </>
            )}

            {activeNav === 'profil' && (
                <section className="profile-panel">
                    <form className="profile-form" onSubmit={handleProfileSubmit}>
                        <div className="profile-hero">
                            <label className="avatar-picker">
                                {avatarSource ? (
                                    <img src={avatarSource} alt="" />
                                ) : (
                                    <UserRound size={42} strokeWidth={2.2} />
                                )}
                                <span>
                                    <Camera size={15} strokeWidth={2.4} />
                                </span>
                                <input accept="image/*" type="file" onChange={handleProfilePhotoChange} />
                            </label>
                            <div>
                                <h2>{profile.name || 'Pengguna'}</h2>
                                <p>{profile.email}</p>
                                <small>@{profile.username}</small>
                            </div>
                        </div>

                        <label className="app-field">
                            <span>
                                <UserRound size={15} strokeWidth={2.4} />
                                Nama
                            </span>
                            <input
                                value={profileForm.name}
                                onChange={(event) => setProfileForm((value) => ({ ...value, name: event.target.value }))}
                                required
                            />
                        </label>

                        <label className="app-field">
                            <span>
                                <Phone size={15} strokeWidth={2.4} />
                                WhatsApp
                            </span>
                            <input
                                inputMode="tel"
                                value={profileForm.whatsapp}
                                onChange={(event) =>
                                    setProfileForm((value) => ({ ...value, whatsapp: event.target.value }))
                                }
                            />
                        </label>

                        <div className="password-box">
                            <div className="password-box-title">
                                <LockKeyhole size={16} strokeWidth={2.4} />
                                <span>Password</span>
                            </div>
                            <input
                                autoComplete="new-password"
                                placeholder="Password baru"
                                type="password"
                                value={profileForm.password}
                                onChange={(event) =>
                                    setProfileForm((value) => ({ ...value, password: event.target.value }))
                                }
                            />
                            <input
                                autoComplete="new-password"
                                placeholder="Konfirmasi password"
                                type="password"
                                value={profileForm.password_confirmation}
                                onChange={(event) =>
                                    setProfileForm((value) => ({
                                        ...value,
                                        password_confirmation: event.target.value,
                                    }))
                                }
                            />
                        </div>

                        {profileMessage && <div className="profile-message">{profileMessage}</div>}

                        <button className="save-profile-button" type="submit" disabled={isSavingProfile}>
                            <Save size={17} strokeWidth={2.5} />
                            {isSavingProfile ? 'Menyimpan' : 'Simpan Profil'}
                        </button>
                    </form>
                </section>
            )}

            <nav className="bottom-nav" aria-label="Navigasi aplikasi">
                {navItems.map((item) => {
                    const Icon = item.icon;

                    return (
                        <button
                            className={[
                                activeNav === item.key ? 'is-active' : '',
                                item.isPrimary ? 'is-primary-action' : '',
                            ].join(' ')}
                            key={item.key}
                            type="button"
                            onClick={() => setActiveNav(item.key)}
                        >
                            <span className="nav-icon" aria-hidden="true">
                                <Icon size={22} strokeWidth={2.3} />
                            </span>
                            <span className="nav-label">{item.label}</span>
                        </button>
                    );
                })}
            </nav>
        </main>
    );
}

const root = document.getElementById('kicap-lpj-root');

if (root) {
    createRoot(root).render(<KicapApp />);
}
