import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import {
    ArrowLeft,
    Banknote,
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
    ReceiptText,
    Save,
    Send,
    UploadCloud,
    UserRound,
    UsersRound,
    Wallet,
} from 'lucide-react';
import { registerSW } from 'virtual:pwa-register';
import '../css/app.css';

registerSW({ immediate: true });

const navItems = [
    { key: 'beranda', label: 'Beranda', icon: Home },
    { key: 'operasional', label: 'Operasional', icon: PlusCircle },
    { key: 'keuangan', label: 'Keuangan', icon: Wallet, isPrimary: true },
    { key: 'selesai', label: 'Selesai', icon: CheckCircle2 },
    { key: 'profil', label: 'Profil', icon: UserRound },
];

const emptyProfile = {
    name: '',
    username: '',
    email: '',
    whatsapp: '',
    avatar_url: null,
};

const emptyFinanceForms = {
    expense: {
        category: '',
        description: '',
        amount: '',
        spent_at: new Date().toISOString().slice(0, 10),
        no_proof_reason: '',
        proof: null,
    },
    advance: {
        category: '',
        description: '',
        amount: '',
        spent_at: new Date().toISOString().slice(0, 10),
        no_proof_reason: '',
        proof: null,
    },
    transfer: {
        recipient_user_id: '',
        amount: '',
        note: '',
    },
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

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value ?? 0);
}

function KicapApp() {
    const [lpjs, setLpjs] = useState([]);
    const [selectedLpjId, setSelectedLpjId] = useState(null);
    const [selectedLpj, setSelectedLpj] = useState(null);
    const [operationalDrafts, setOperationalDrafts] = useState({});
    const [profile, setProfile] = useState(emptyProfile);
    const [profileForm, setProfileForm] = useState({
        name: '',
        whatsapp: '',
        password: '',
        password_confirmation: '',
    });
    const [financeForms, setFinanceForms] = useState(emptyFinanceForms);
    const [profilePhoto, setProfilePhoto] = useState(null);
    const [profilePhotoPreview, setProfilePhotoPreview] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isDetailLoading, setIsDetailLoading] = useState(false);
    const [isSavingProfile, setIsSavingProfile] = useState(false);
    const [isOperationalDirty, setIsOperationalDirty] = useState(false);
    const [profileMessage, setProfileMessage] = useState('');
    const [financeMessage, setFinanceMessage] = useState('');
    const [operationalSaveMessage, setOperationalSaveMessage] = useState('');
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

    useEffect(() => {
        if (!selectedLpjId) {
            return;
        }

        let isMounted = true;

        setIsDetailLoading(true);
        setOperationalSaveMessage('');
        setFinanceMessage('');

        fetch(`/api/app/lpjs/${selectedLpjId}`, {
            headers: {
                Accept: 'application/json',
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Detail LPJ tidak tersedia');
                }

                return response.json();
            })
            .then((payload) => {
                if (!isMounted) {
                    return;
                }

                const detail = payload.data;
                const drafts = {};

                detail.activity_notes.forEach((note) => {
                    drafts[note.type] = note.content ?? '';
                });

                setSelectedLpj(detail);
                setOperationalDrafts(drafts);
                setFinanceForms(emptyFinanceForms);
                setIsOperationalDirty(false);
            })
            .catch(() => {
                if (isMounted) {
                    setSelectedLpj(null);
                    setOperationalDrafts({});
                    setOperationalSaveMessage('Detail LPJ belum bisa dibuka');
                }
            })
            .finally(() => {
                if (isMounted) {
                    setIsDetailLoading(false);
                }
            });

        return () => {
            isMounted = false;
        };
    }, [selectedLpjId]);

    useEffect(() => {
        if (!selectedLpj?.can_input_operational_data || !isOperationalDirty) {
            return undefined;
        }

        setOperationalSaveMessage('Menyimpan catatan...');

        const timeoutId = window.setTimeout(() => {
            const notes = selectedLpj.activity_notes.map((note) => ({
                type: note.type,
                content: operationalDrafts[note.type] ?? '',
            }));

            fetch(`/api/app/lpjs/${selectedLpj.id}/activity-notes`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ notes }),
            })
                .then(async (response) => {
                    if (!response.ok) {
                        throw await response.json();
                    }

                    return response.json();
                })
                .then((payload) => {
                    setSelectedLpj((current) => {
                        if (!current) {
                            return current;
                        }

                        return {
                            ...current,
                            activity_notes: payload.data.activity_notes,
                        };
                    });
                    setIsOperationalDirty(false);
                    setOperationalSaveMessage('Catatan tersimpan');
                })
                .catch(() => {
                    setOperationalSaveMessage('Catatan belum tersimpan');
                });
        }, 900);

        return () => window.clearTimeout(timeoutId);
    }, [isOperationalDirty, operationalDrafts, selectedLpj]);

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

    const updateFinanceForm = (formKey, field, value) => {
        setFinanceForms((current) => ({
            ...current,
            [formKey]: {
                ...current[formKey],
                [field]: value,
            },
        }));
    };

    const handleFinanceSubmit = (event, formKey, endpoint) => {
        event.preventDefault();

        if (!selectedLpj?.finance?.can_input_finance) {
            return;
        }

        setFinanceMessage('Menyimpan transaksi...');

        const form = financeForms[formKey];
        const formData = new FormData();

        Object.entries(form).forEach(([key, value]) => {
            if (value !== null && value !== '') {
                formData.append(key, value);
            }
        });

        fetch(`/api/app/lpjs/${selectedLpj.id}/${endpoint}`, {
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
                setSelectedLpj((current) => {
                    if (!current) {
                        return current;
                    }

                    return {
                        ...current,
                        finance: payload.data.finance,
                    };
                });
                setFinanceForms((current) => ({
                    ...current,
                    [formKey]: emptyFinanceForms[formKey],
                }));
                setFinanceMessage('Transaksi tersimpan');
            })
            .catch((error) => {
                const firstMessage = Object.values(error?.errors ?? {})?.[0]?.[0];
                setFinanceMessage(firstMessage ?? 'Transaksi belum tersimpan');
            });
    };

    const totals = useMemo(() => {
        return {
            active: lpjs.filter((lpj) => lpj.status === 'aktif').length,
            finished: lpjs.filter((lpj) => lpj.status === 'finish').length,
        };
    }, [lpjs]);

    const activeLpjs = useMemo(() => lpjs.filter((lpj) => lpj.status === 'aktif'), [lpjs]);
    const finishedLpjs = useMemo(() => lpjs.filter((lpj) => lpj.status === 'finish'), [lpjs]);
    const avatarSource = profilePhotoPreview ?? profile.avatar_url;
    const isOperationalNav = activeNav === 'operasional';
    const isFinanceNav = activeNav === 'keuangan';
    const showDetail = (isOperationalNav || isFinanceNav) && selectedLpjId;
    const pageTitle = activeNav === 'profil' ? 'Profil pengguna' : showDetail ? 'Detail LPJ' : 'Ruang kerja petugas';
    const visibleLpjs = useMemo(() => {
        if (activeNav === 'operasional' || activeNav === 'keuangan') {
            return activeLpjs;
        }

        if (activeNav === 'selesai') {
            return finishedLpjs;
        }

        return lpjs;
    }, [activeLpjs, activeNav, finishedLpjs, lpjs]);

    const lpjHeading = {
        beranda: 'Aktif dan selesai',
        operasional: 'Input operasional',
        keuangan: 'Operasional keuangan',
        selesai: 'Sudah selesai',
    }[activeNav] ?? 'Aktif dan selesai';

    const emptyLpjMessage = {
        operasional: 'Belum ada LPJ aktif untuk input operasional.',
        keuangan: 'Belum ada LPJ aktif untuk operasional keuangan.',
        selesai: 'Belum ada LPJ selesai yang ditugaskan.',
    }[activeNav] ?? 'Belum ada LPJ aktif atau selesai yang ditugaskan.';

    const openLpjDetail = (lpjId) => {
        setSelectedLpjId(lpjId);

        if (!isOperationalNav && !isFinanceNav) {
            setActiveNav('operasional');
        }
    };

    const closeLpjDetail = () => {
        setSelectedLpjId(null);
        setSelectedLpj(null);
        setOperationalDrafts({});
        setFinanceForms(emptyFinanceForms);
        setIsOperationalDirty(false);
        setOperationalSaveMessage('');
        setFinanceMessage('');
    };

    const handleOperationalChange = (type, content) => {
        setOperationalDrafts((value) => ({ ...value, [type]: content }));
        setIsOperationalDirty(true);
    };

    return (
        <main className="mobile-shell">
            <header className="app-header">
                <div className="brand-mark">
                    <img src="/icons/kicap-lpj.svg" alt="" />
                </div>
                <div>
                    <p className="section-kicker">Kicap LPJ</p>
                    <h1>{pageTitle}</h1>
                </div>
                <button className="logout-button" type="button" onClick={handleLogout} aria-label="Keluar">
                    <LogOut size={18} strokeWidth={2.4} />
                </button>
            </header>

            {activeNav !== 'profil' && !showDetail && (
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
                            <span>Selesai</span>
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
                                <h2>{lpjHeading}</h2>
                            </div>
                        </div>

                        <div className="lpj-list">
                            {isLoading && <div className="empty-state">Memuat LPJ...</div>}

                            {!isLoading && visibleLpjs.length === 0 && (
                                <div className="empty-state">{emptyLpjMessage}</div>
                            )}

                            {!isLoading &&
                                visibleLpjs.map((lpj) => (
                                    <button
                                        className="lpj-card"
                                        key={lpj.id}
                                        type="button"
                                        onClick={() => openLpjDetail(lpj.id)}
                                    >
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
                                        <span className="open-detail-label">
                                            {isFinanceNav ? (
                                                <Wallet size={14} strokeWidth={2.4} />
                                            ) : (
                                                <ClipboardList size={14} strokeWidth={2.4} />
                                            )}
                                            {isFinanceNav ? 'Buka keuangan' : 'Buka detail'}
                                        </span>
                                    </button>
                                ))}
                        </div>
                    </section>
                </>
            )}

            {showDetail && (
                <section className="detail-panel">
                    <button className="back-button" type="button" onClick={closeLpjDetail}>
                        <ArrowLeft size={17} strokeWidth={2.5} />
                        Kembali
                    </button>

                    {isDetailLoading && <div className="empty-state">Memuat detail LPJ...</div>}

                    {!isDetailLoading && !selectedLpj && (
                        <div className="empty-state">{operationalSaveMessage || 'Detail LPJ belum tersedia.'}</div>
                    )}

                    {!isDetailLoading && selectedLpj && (
                        <>
                            <article className="detail-hero">
                                <div className="lpj-card-top">
                                    <span className={`status-pill ${selectedLpj.status}`}>
                                        {selectedLpj.status_label}
                                    </span>
                                    <span className="lpj-code">{selectedLpj.code}</span>
                                </div>
                                <h2>{selectedLpj.title}</h2>
                                <p className="lpj-type">{selectedLpj.type ?? '-'}</p>
                                <div className="meta-list">
                                    <span>
                                        <CalendarDays size={15} strokeWidth={2.4} />
                                        {formatDateRange(selectedLpj)}
                                    </span>
                                    <span>
                                        <MapPin size={15} strokeWidth={2.4} />
                                        {selectedLpj.location ?? '-'}
                                    </span>
                                    <span>
                                        <UsersRound size={15} strokeWidth={2.4} />
                                        {selectedLpj.person_in_charge ?? '-'}
                                    </span>
                                </div>
                            </article>

                            {isOperationalNav && (
                                <>
                                    <div className="narrative-header">
                                        <div>
                                            <p className="section-kicker">Input Operasional</p>
                                            <h2>
                                                {selectedLpj.can_input_operational_data
                                                    ? 'Catatan petugas'
                                                    : 'Catatan terkunci'}
                                            </h2>
                                        </div>
                                        <div className="save-state">
                                            <Save size={14} strokeWidth={2.4} />
                                            {selectedLpj.can_input_operational_data
                                                ? operationalSaveMessage || 'Siap diisi'
                                                : 'Hanya baca'}
                                        </div>
                                    </div>

                                    <div className="narrative-list">
                                        {selectedLpj.activity_notes.map((note) => (
                                            <label className="narrative-card" key={note.type}>
                                                <span>{note.label}</span>
                                                <textarea
                                                    disabled={!selectedLpj.can_input_operational_data}
                                                    rows={5}
                                                    value={operationalDrafts[note.type] ?? ''}
                                                    onChange={(event) =>
                                                        handleOperationalChange(note.type, event.target.value)
                                                    }
                                                />
                                            </label>
                                        ))}
                                    </div>
                                </>
                            )}

                            {isFinanceNav && <section className="finance-panel">
                                <div className="narrative-header">
                                    <div>
                                        <p className="section-kicker">Operasional Keuangan</p>
                                        <h2>Saldo dan transaksi</h2>
                                    </div>
                                    <div className="save-state">
                                        <Wallet size={14} strokeWidth={2.4} />
                                        {formatCurrency(selectedLpj.finance?.balance ?? 0)}
                                    </div>
                                </div>

                                <div className="finance-summary">
                                    <article>
                                        <span>Saldo pegangan</span>
                                        <strong>{formatCurrency(selectedLpj.finance?.balance ?? 0)}</strong>
                                    </article>
                                    <article>
                                        <span>Transaksi saya</span>
                                        <strong>{selectedLpj.finance?.transactions?.length ?? 0}</strong>
                                    </article>
                                    <article>
                                        <span>Klaim talangan</span>
                                        <strong>{selectedLpj.finance?.advance_claims?.length ?? 0}</strong>
                                    </article>
                                </div>

                                {financeMessage && <div className="profile-message">{financeMessage}</div>}

                                <form
                                    className="finance-form"
                                    onSubmit={(event) => handleFinanceSubmit(event, 'expense', 'expenses')}
                                >
                                    <div className="finance-form-title">
                                        <ReceiptText size={16} strokeWidth={2.4} />
                                        <span>Catat pengeluaran</span>
                                    </div>
                                    <select
                                        disabled={!selectedLpj.finance?.can_input_finance}
                                        value={financeForms.expense.category}
                                        onChange={(event) =>
                                            updateFinanceForm('expense', 'category', event.target.value)
                                        }
                                        required
                                    >
                                        <option value="">Pilih kategori</option>
                                        {(selectedLpj.finance_category_options ?? []).map((category) => (
                                            <option value={category} key={category}>
                                                {category}
                                            </option>
                                        ))}
                                    </select>
                                    <input
                                        disabled={!selectedLpj.finance?.can_input_finance}
                                        inputMode="decimal"
                                        placeholder="Nominal"
                                        type="number"
                                        min="1"
                                        value={financeForms.expense.amount}
                                        onChange={(event) => updateFinanceForm('expense', 'amount', event.target.value)}
                                        required
                                    />
                                    <input
                                        disabled={!selectedLpj.finance?.can_input_finance}
                                        type="date"
                                        value={financeForms.expense.spent_at}
                                        onChange={(event) =>
                                            updateFinanceForm('expense', 'spent_at', event.target.value)
                                        }
                                        required
                                    />
                                    <textarea
                                        disabled={!selectedLpj.finance?.can_input_finance}
                                        placeholder="Keterangan pengeluaran"
                                        value={financeForms.expense.description}
                                        onChange={(event) =>
                                            updateFinanceForm('expense', 'description', event.target.value)
                                        }
                                        required
                                    />
                                    <input
                                        disabled={!selectedLpj.finance?.can_input_finance}
                                        placeholder="Alasan jika tidak ada bukti"
                                        value={financeForms.expense.no_proof_reason}
                                        onChange={(event) =>
                                            updateFinanceForm('expense', 'no_proof_reason', event.target.value)
                                        }
                                    />
                                    <label className="file-button">
                                        <UploadCloud size={15} strokeWidth={2.4} />
                                        <span>{financeForms.expense.proof?.name ?? 'Upload bukti'}</span>
                                        <input
                                            disabled={!selectedLpj.finance?.can_input_finance}
                                            accept="image/*,.pdf"
                                            type="file"
                                            onChange={(event) =>
                                                updateFinanceForm('expense', 'proof', event.target.files?.[0] ?? null)
                                            }
                                        />
                                    </label>
                                    <button
                                        className="save-profile-button"
                                        type="submit"
                                        disabled={!selectedLpj.finance?.can_input_finance}
                                    >
                                        <Save size={17} strokeWidth={2.5} />
                                        Simpan Pengeluaran
                                    </button>
                                </form>

                                <form
                                    className="finance-form"
                                    onSubmit={(event) =>
                                        handleFinanceSubmit(event, 'transfer', 'balance-transfers')
                                    }
                                >
                                    <div className="finance-form-title">
                                        <Send size={16} strokeWidth={2.4} />
                                        <span>Transfer saldo</span>
                                    </div>
                                    <select
                                        disabled={
                                            !selectedLpj.finance?.can_input_finance ||
                                            !selectedLpj.finance?.can_transfer_balance
                                        }
                                        value={financeForms.transfer.recipient_user_id}
                                        onChange={(event) =>
                                            updateFinanceForm('transfer', 'recipient_user_id', event.target.value)
                                        }
                                        required
                                    >
                                        <option value="">Pilih penerima</option>
                                        {(selectedLpj.finance?.transfer_targets ?? []).map((target) => (
                                            <option value={target.id} key={target.id}>
                                                {target.name}
                                            </option>
                                        ))}
                                    </select>
                                    <input
                                        disabled={
                                            !selectedLpj.finance?.can_input_finance ||
                                            !selectedLpj.finance?.can_transfer_balance
                                        }
                                        inputMode="decimal"
                                        placeholder="Nominal"
                                        type="number"
                                        min="1"
                                        value={financeForms.transfer.amount}
                                        onChange={(event) => updateFinanceForm('transfer', 'amount', event.target.value)}
                                        required
                                    />
                                    <input
                                        disabled={
                                            !selectedLpj.finance?.can_input_finance ||
                                            !selectedLpj.finance?.can_transfer_balance
                                        }
                                        placeholder="Catatan transfer"
                                        value={financeForms.transfer.note}
                                        onChange={(event) => updateFinanceForm('transfer', 'note', event.target.value)}
                                    />
                                    <button
                                        className="save-profile-button"
                                        type="submit"
                                        disabled={
                                            !selectedLpj.finance?.can_input_finance ||
                                            !selectedLpj.finance?.can_transfer_balance
                                        }
                                    >
                                        <Send size={17} strokeWidth={2.5} />
                                        Transfer Sekarang
                                    </button>
                                </form>

                                <form
                                    className="finance-form"
                                    onSubmit={(event) =>
                                        handleFinanceSubmit(event, 'advance', 'advance-expenses')
                                    }
                                >
                                    <div className="finance-form-title">
                                        <Banknote size={16} strokeWidth={2.4} />
                                        <span>Dana talangan</span>
                                    </div>
                                    <select
                                        disabled={!selectedLpj.finance?.can_input_finance}
                                        value={financeForms.advance.category}
                                        onChange={(event) =>
                                            updateFinanceForm('advance', 'category', event.target.value)
                                        }
                                        required
                                    >
                                        <option value="">Pilih kategori</option>
                                        {(selectedLpj.finance_category_options ?? []).map((category) => (
                                            <option value={category} key={category}>
                                                {category}
                                            </option>
                                        ))}
                                    </select>
                                    <input
                                        disabled={!selectedLpj.finance?.can_input_finance}
                                        inputMode="decimal"
                                        placeholder="Nominal"
                                        type="number"
                                        min="1"
                                        value={financeForms.advance.amount}
                                        onChange={(event) => updateFinanceForm('advance', 'amount', event.target.value)}
                                        required
                                    />
                                    <input
                                        disabled={!selectedLpj.finance?.can_input_finance}
                                        type="date"
                                        value={financeForms.advance.spent_at}
                                        onChange={(event) =>
                                            updateFinanceForm('advance', 'spent_at', event.target.value)
                                        }
                                        required
                                    />
                                    <textarea
                                        disabled={!selectedLpj.finance?.can_input_finance}
                                        placeholder="Keterangan dana talangan"
                                        value={financeForms.advance.description}
                                        onChange={(event) =>
                                            updateFinanceForm('advance', 'description', event.target.value)
                                        }
                                        required
                                    />
                                    <input
                                        disabled={!selectedLpj.finance?.can_input_finance}
                                        placeholder="Alasan jika tidak ada bukti"
                                        value={financeForms.advance.no_proof_reason}
                                        onChange={(event) =>
                                            updateFinanceForm('advance', 'no_proof_reason', event.target.value)
                                        }
                                    />
                                    <label className="file-button">
                                        <UploadCloud size={15} strokeWidth={2.4} />
                                        <span>{financeForms.advance.proof?.name ?? 'Upload bukti'}</span>
                                        <input
                                            disabled={!selectedLpj.finance?.can_input_finance}
                                            accept="image/*,.pdf"
                                            type="file"
                                            onChange={(event) =>
                                                updateFinanceForm('advance', 'proof', event.target.files?.[0] ?? null)
                                            }
                                        />
                                    </label>
                                    <button
                                        className="save-profile-button"
                                        type="submit"
                                        disabled={!selectedLpj.finance?.can_input_finance}
                                    >
                                        <Save size={17} strokeWidth={2.5} />
                                        Simpan Talangan
                                    </button>
                                </form>

                                <div className="finance-history">
                                    <h3>Riwayat terbaru</h3>
                                    {(selectedLpj.finance?.transactions ?? []).length === 0 && (
                                        <div className="empty-state">Belum ada transaksi keuangan.</div>
                                    )}
                                    {(selectedLpj.finance?.transactions ?? []).map((transaction) => (
                                        <article key={transaction.id}>
                                            <div>
                                                <strong>{transaction.category}</strong>
                                                <span>{transaction.source_label} · {transaction.status_label}</span>
                                            </div>
                                            <b>{formatCurrency(transaction.amount)}</b>
                                        </article>
                                    ))}
                                </div>
                            </section>}
                        </>
                    )}
                </section>
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
