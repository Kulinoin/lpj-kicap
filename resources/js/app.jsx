import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import {
    ArrowLeft,
    Banknote,
    CalendarDays,
    Camera,
    CheckCircle2,
    ClipboardList,
    Clock3,
    FileText,
    Home,
    LockKeyhole,
    LogOut,
    MapPin,
    Paperclip,
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
    { key: 'dokumentasi', label: 'Dokumentasi', icon: Camera },
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

function buildRevisionForms(transactions = []) {
    return Object.fromEntries(
        transactions.map((transaction) => [
            transaction.id,
            {
                category: transaction.category ?? '',
                description: transaction.description ?? '',
                spent_at: transaction.spent_at ?? new Date().toISOString().slice(0, 10),
                no_proof_reason: transaction.no_proof_reason ?? '',
                proof: null,
            },
        ])
    );
}

const emptyParticipant = {
    name: '',
    origin: '',
    participant_number: '',
    attendance_status: 'hadir',
    result_status: '',
    note: '',
};

const emptyCommittee = {
    name: '',
    role: '',
    task: '',
    contact: '',
};

const emptySchedule = {
    start_time: '',
    end_time: '',
    activity_name: '',
    responsible_person: '',
    note: '',
};

const emptyExecutionForms = {
    participants: [{ ...emptyParticipant }],
    committees: [{ ...emptyCommittee }],
    schedules: [{ ...emptySchedule }],
    documentation: {
        category: 'pelaksanaan',
        caption: '',
        include_in_report: true,
        file: null,
    },
    attachment: {
        title: '',
        description: '',
        include_in_report: true,
        file: null,
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

function rowsOrEmpty(rows, emptyRow) {
    return rows?.length ? rows.map((row) => ({ ...emptyRow, ...row })) : [{ ...emptyRow }];
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
    const [revisionForms, setRevisionForms] = useState({});
    const [executionForms, setExecutionForms] = useState(emptyExecutionForms);
    const [profilePhoto, setProfilePhoto] = useState(null);
    const [profilePhotoPreview, setProfilePhotoPreview] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [isDetailLoading, setIsDetailLoading] = useState(false);
    const [isSavingProfile, setIsSavingProfile] = useState(false);
    const [isOperationalDirty, setIsOperationalDirty] = useState(false);
    const [profileMessage, setProfileMessage] = useState('');
    const [financeMessage, setFinanceMessage] = useState('');
    const [executionMessage, setExecutionMessage] = useState('');
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
        setExecutionMessage('');

        fetch(`/api/app/lpjs/${selectedLpjId}`, {
            headers: {
                Accept: 'application/json',
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Detail event tidak tersedia');
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
                setRevisionForms(buildRevisionForms(detail.finance?.transactions));
                setExecutionForms({
                    participants: rowsOrEmpty(detail.execution?.participants, emptyParticipant),
                    committees: rowsOrEmpty(detail.execution?.committees, emptyCommittee),
                    schedules: rowsOrEmpty(detail.execution?.schedules, emptySchedule),
                    documentation: { ...emptyExecutionForms.documentation },
                    attachment: { ...emptyExecutionForms.attachment },
                });
                setIsOperationalDirty(false);
            })
            .catch(() => {
                if (isMounted) {
                    setSelectedLpj(null);
                    setOperationalDrafts({});
                    setExecutionForms(emptyExecutionForms);
                    setRevisionForms({});
                    setOperationalSaveMessage('Detail event belum bisa dibuka');
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
                setRevisionForms(buildRevisionForms(payload.data.finance.transactions));
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

    const handleSubmitReview = () => {
        if (!selectedLpj?.can_submit_review) {
            return;
        }

        setOperationalSaveMessage('Mengajukan review...');

        fetch(`/api/app/lpjs/${selectedLpj.id}/submit-review`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
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
                        completeness_status: payload.data.completeness_status,
                        completeness_label: payload.data.completeness_label,
                        submitted_at: payload.data.submitted_at,
                        review: payload.data.review,
                    };
                });
                setOperationalSaveMessage('Event diajukan untuk review');
            })
            .catch(() => {
                setOperationalSaveMessage('Event belum bisa diajukan');
            });
    };

    const updateRevisionForm = (transactionId, field, value) => {
        setRevisionForms((current) => ({
            ...current,
            [transactionId]: {
                ...(current[transactionId] ?? {}),
                [field]: value,
            },
        }));
    };

    const handleRevisionSubmit = (event, transaction) => {
        event.preventDefault();

        if (!transaction.can_submit_revision) {
            return;
        }

        setFinanceMessage('Mengirim revisi transaksi...');

        const form = revisionForms[transaction.id] ?? {};
        const formData = new FormData();

        Object.entries(form).forEach(([key, value]) => {
            if (value !== null && value !== '') {
                formData.append(key, value);
            }
        });

        fetch(`/api/app/lpjs/${selectedLpj.id}/financial-transactions/${transaction.id}/revision`, {
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
                setRevisionForms(buildRevisionForms(payload.data.finance.transactions));
                setFinanceMessage('Revisi dikirim untuk review Admin');
            })
            .catch((error) => {
                const firstMessage = Object.values(error?.errors ?? {})?.[0]?.[0];
                setFinanceMessage(firstMessage ?? 'Revisi belum terkirim');
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
    const isDocumentationNav = activeNav === 'dokumentasi';
    const showDetail = (isOperationalNav || isFinanceNav || isDocumentationNav) && selectedLpjId;
    const pageTitle = activeNav === 'profil' ? 'Profil pengguna' : showDetail ? 'Detail Event' : 'Ruang kerja petugas';
    const visibleLpjs = useMemo(() => {
        const sortByWorkPriority = (items) => [...items].sort((left, right) => {
            const leftActive = left.status === 'aktif' ? 0 : 1;
            const rightActive = right.status === 'aktif' ? 0 : 1;

            if (leftActive !== rightActive) {
                return leftActive - rightActive;
            }

            return right.id - left.id;
        });

        if (activeNav === 'operasional' || activeNav === 'keuangan') {
            return sortByWorkPriority(activeLpjs);
        }

        if (activeNav === 'dokumentasi') {
            return sortByWorkPriority(activeLpjs);
        }

        return sortByWorkPriority([...activeLpjs, ...finishedLpjs]);
    }, [activeLpjs, activeNav, finishedLpjs]);

    const lpjHeading = {
        beranda: 'Event terbaru',
        operasional: 'Operasional event',
        keuangan: 'Dana kegiatan',
        dokumentasi: 'Dokumentasi event',
    }[activeNav] ?? 'Aktif dan selesai';

    const emptyLpjMessage = {
        operasional: 'Belum ada event aktif untuk input operasional.',
        keuangan: 'Belum ada event aktif untuk dana kegiatan.',
        dokumentasi: 'Belum ada event aktif untuk dokumentasi.',
    }[activeNav] ?? 'Belum ada event aktif atau selesai yang ditugaskan.';

    const openLpjDetail = (lpjId) => {
        setSelectedLpjId(lpjId);

        if (!isOperationalNav && !isFinanceNav && !isDocumentationNav) {
            setActiveNav('operasional');
        }
    };

    const closeLpjDetail = () => {
        setSelectedLpjId(null);
        setSelectedLpj(null);
        setOperationalDrafts({});
        setFinanceForms(emptyFinanceForms);
        setRevisionForms({});
        setExecutionForms(emptyExecutionForms);
        setIsOperationalDirty(false);
        setOperationalSaveMessage('');
        setFinanceMessage('');
        setExecutionMessage('');
    };

    const handleOperationalChange = (type, content) => {
        setOperationalDrafts((value) => ({ ...value, [type]: content }));
        setIsOperationalDirty(true);
    };

    const updateExecutionRow = (section, index, field, value) => {
        setExecutionForms((current) => ({
            ...current,
            [section]: current[section].map((row, rowIndex) =>
                rowIndex === index ? { ...row, [field]: value } : row
            ),
        }));
    };

    const addExecutionRow = (section, emptyRow) => {
        setExecutionForms((current) => ({
            ...current,
            [section]: [...current[section], { ...emptyRow }],
        }));
    };

    const updateExecutionUploadForm = (section, field, value) => {
        setExecutionForms((current) => ({
            ...current,
            [section]: {
                ...current[section],
                [field]: value,
            },
        }));
    };

    const handleExecutionSubmit = (event) => {
        event.preventDefault();

        if (!selectedLpj?.execution?.can_edit_activity_data) {
            return;
        }

        setExecutionMessage('Menyimpan data kegiatan...');

        fetch(`/api/app/lpjs/${selectedLpj.id}/execution-data`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({
                participants: executionForms.participants,
                committees: executionForms.committees,
                schedules: executionForms.schedules,
            }),
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
                        execution: payload.data.execution,
                    };
                });
                setExecutionForms((current) => ({
                    ...current,
                    participants: rowsOrEmpty(payload.data.execution.participants, emptyParticipant),
                    committees: rowsOrEmpty(payload.data.execution.committees, emptyCommittee),
                    schedules: rowsOrEmpty(payload.data.execution.schedules, emptySchedule),
                }));
                setExecutionMessage('Data kegiatan tersimpan');
            })
            .catch((error) => {
                const firstMessage = Object.values(error?.errors ?? {})?.[0]?.[0];
                setExecutionMessage(firstMessage ?? 'Data kegiatan belum tersimpan');
            });
    };

    const handleExecutionUpload = (event, formKey, endpoint) => {
        event.preventDefault();

        if (!selectedLpj?.execution?.can_upload_documentation) {
            return;
        }

        const form = executionForms[formKey];

        if (!form.file) {
            setExecutionMessage('Pilih file terlebih dahulu');
            return;
        }

        setExecutionMessage('Mengupload file...');

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
                        execution: payload.data.execution,
                    };
                });
                setExecutionForms((current) => ({
                    ...current,
                    [formKey]: { ...emptyExecutionForms[formKey] },
                }));
                setExecutionMessage('File tersimpan');
            })
            .catch((error) => {
                const firstMessage = Object.values(error?.errors ?? {})?.[0]?.[0];
                setExecutionMessage(firstMessage ?? 'File belum tersimpan');
            });
    };

    return (
        <main className="mobile-shell">
            <header className="app-header">
                <div className="brand-mark">
                    <img src="/icons/kicap-lpj.svg" alt="" />
                </div>
                <div>
                    <p className="section-kicker">Kicap Event</p>
                    <h1>{pageTitle}</h1>
                </div>
                <button className="logout-button" type="button" onClick={handleLogout} aria-label="Keluar">
                    <LogOut size={18} strokeWidth={2.4} />
                </button>
            </header>

            {activeNav !== 'profil' && !showDetail && (
                <>
                    <section className="summary-grid" aria-label="Ringkasan event">
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
                                <p className="section-kicker">Event Saya</p>
                                <h2>{lpjHeading}</h2>
                            </div>
                        </div>

                        <div className="lpj-list">
                            {isLoading && <div className="empty-state">Memuat event...</div>}

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
                                            ) : isDocumentationNav ? (
                                                <Camera size={14} strokeWidth={2.4} />
                                            ) : (
                                                <ClipboardList size={14} strokeWidth={2.4} />
                                            )}
                                            {isFinanceNav
                                                ? 'Buka keuangan'
                                                : isDocumentationNav
                                                    ? 'Buka dokumentasi'
                                                    : 'Buka detail'}
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

                    {isDetailLoading && <div className="empty-state">Memuat detail event...</div>}

                    {!isDetailLoading && !selectedLpj && (
                        <div className="empty-state">{operationalSaveMessage || 'Detail event belum tersedia.'}</div>
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
                                <div className="review-strip">
                                    <div>
                                        <span>Kelengkapan</span>
                                        <strong>{selectedLpj.completeness_label ?? 'Belum Lengkap'}</strong>
                                    </div>
                                    <button
                                        type="button"
                                        disabled={!selectedLpj.can_submit_review}
                                        onClick={handleSubmitReview}
                                    >
                                        <ClipboardList size={15} strokeWidth={2.4} />
                                        Ajukan Review
                                    </button>
                                </div>
                            </article>

                            {(isOperationalNav || isDocumentationNav) && (
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

                                    <form className="execution-form" onSubmit={handleExecutionSubmit}>
                                        <div className="narrative-header">
                                            <div>
                                                <p className="section-kicker">Pelaksanaan</p>
                                                <h2>Peserta, tim, dan rundown</h2>
                                            </div>
                                            <div className="save-state">
                                                <UsersRound size={14} strokeWidth={2.4} />
                                                {selectedLpj.execution?.can_edit_activity_data ? 'Bisa diedit' : 'Hanya baca'}
                                            </div>
                                        </div>

                                        {executionMessage && <div className="profile-message">{executionMessage}</div>}

                                        <section className="execution-card">
                                            <div className="finance-form-title">
                                                <UsersRound size={16} strokeWidth={2.4} />
                                                <span>Data peserta</span>
                                            </div>
                                            {executionForms.participants.map((participant, index) => (
                                                <div className="execution-row" key={`participant-${index}`}>
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        placeholder="Nama peserta"
                                                        value={participant.name}
                                                        onChange={(event) =>
                                                            updateExecutionRow('participants', index, 'name', event.target.value)
                                                        }
                                                    />
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        placeholder="Asal/kelas/divisi"
                                                        value={participant.origin ?? ''}
                                                        onChange={(event) =>
                                                            updateExecutionRow('participants', index, 'origin', event.target.value)
                                                        }
                                                    />
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        placeholder="Nomor peserta"
                                                        value={participant.participant_number ?? ''}
                                                        onChange={(event) =>
                                                            updateExecutionRow(
                                                                'participants',
                                                                index,
                                                                'participant_number',
                                                                event.target.value
                                                            )
                                                        }
                                                    />
                                                    <select
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        value={participant.attendance_status ?? 'hadir'}
                                                        onChange={(event) =>
                                                            updateExecutionRow(
                                                                'participants',
                                                                index,
                                                                'attendance_status',
                                                                event.target.value
                                                            )
                                                        }
                                                    >
                                                        {Object.entries(selectedLpj.execution?.attendance_options ?? {}).map(
                                                            ([value, label]) => (
                                                                <option value={value} key={value}>
                                                                    {label}
                                                                </option>
                                                            )
                                                        )}
                                                    </select>
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        placeholder="Hasil/status"
                                                        value={participant.result_status ?? ''}
                                                        onChange={(event) =>
                                                            updateExecutionRow(
                                                                'participants',
                                                                index,
                                                                'result_status',
                                                                event.target.value
                                                            )
                                                        }
                                                    />
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        placeholder="Keterangan"
                                                        value={participant.note ?? ''}
                                                        onChange={(event) =>
                                                            updateExecutionRow('participants', index, 'note', event.target.value)
                                                        }
                                                    />
                                                </div>
                                            ))}
                                            <button
                                                className="add-row-button"
                                                type="button"
                                                disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                onClick={() => addExecutionRow('participants', emptyParticipant)}
                                            >
                                                <PlusCircle size={16} strokeWidth={2.4} />
                                                Tambah Peserta
                                            </button>
                                        </section>

                                        <section className="execution-card">
                                            <div className="finance-form-title">
                                                <ClipboardList size={16} strokeWidth={2.4} />
                                                <span>Panitia / pendamping</span>
                                            </div>
                                            {executionForms.committees.map((committee, index) => (
                                                <div className="execution-row two" key={`committee-${index}`}>
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        placeholder="Nama"
                                                        value={committee.name}
                                                        onChange={(event) =>
                                                            updateExecutionRow('committees', index, 'name', event.target.value)
                                                        }
                                                    />
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        placeholder="Jabatan/peran"
                                                        value={committee.role ?? ''}
                                                        onChange={(event) =>
                                                            updateExecutionRow('committees', index, 'role', event.target.value)
                                                        }
                                                    />
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        placeholder="Tugas"
                                                        value={committee.task ?? ''}
                                                        onChange={(event) =>
                                                            updateExecutionRow('committees', index, 'task', event.target.value)
                                                        }
                                                    />
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        placeholder="Kontak opsional"
                                                        value={committee.contact ?? ''}
                                                        onChange={(event) =>
                                                            updateExecutionRow('committees', index, 'contact', event.target.value)
                                                        }
                                                    />
                                                </div>
                                            ))}
                                            <button
                                                className="add-row-button"
                                                type="button"
                                                disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                onClick={() => addExecutionRow('committees', emptyCommittee)}
                                            >
                                                <PlusCircle size={16} strokeWidth={2.4} />
                                                Tambah Tim
                                            </button>
                                        </section>

                                        <section className="execution-card">
                                            <div className="finance-form-title">
                                                <Clock3 size={16} strokeWidth={2.4} />
                                                <span>Rundown</span>
                                            </div>
                                            {executionForms.schedules.map((schedule, index) => (
                                                <div className="execution-row two" key={`schedule-${index}`}>
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        type="datetime-local"
                                                        value={schedule.start_time ?? ''}
                                                        onChange={(event) =>
                                                            updateExecutionRow('schedules', index, 'start_time', event.target.value)
                                                        }
                                                    />
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        type="datetime-local"
                                                        value={schedule.end_time ?? ''}
                                                        onChange={(event) =>
                                                            updateExecutionRow('schedules', index, 'end_time', event.target.value)
                                                        }
                                                    />
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        placeholder="Nama aktivitas"
                                                        value={schedule.activity_name}
                                                        onChange={(event) =>
                                                            updateExecutionRow(
                                                                'schedules',
                                                                index,
                                                                'activity_name',
                                                                event.target.value
                                                            )
                                                        }
                                                    />
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        placeholder="Penanggung jawab"
                                                        value={schedule.responsible_person ?? ''}
                                                        onChange={(event) =>
                                                            updateExecutionRow(
                                                                'schedules',
                                                                index,
                                                                'responsible_person',
                                                                event.target.value
                                                            )
                                                        }
                                                    />
                                                    <input
                                                        disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                        placeholder="Catatan"
                                                        value={schedule.note ?? ''}
                                                        onChange={(event) =>
                                                            updateExecutionRow('schedules', index, 'note', event.target.value)
                                                        }
                                                    />
                                                </div>
                                            ))}
                                            <button
                                                className="add-row-button"
                                                type="button"
                                                disabled={!selectedLpj.execution?.can_edit_activity_data}
                                                onClick={() => addExecutionRow('schedules', emptySchedule)}
                                            >
                                                <PlusCircle size={16} strokeWidth={2.4} />
                                                Tambah Rundown
                                            </button>
                                        </section>

                                        <button
                                            className="save-profile-button"
                                            type="submit"
                                            disabled={!selectedLpj.execution?.can_edit_activity_data}
                                        >
                                            <Save size={17} strokeWidth={2.5} />
                                            Simpan Data Kegiatan
                                        </button>
                                    </form>

                                    <div className="upload-grid">
                                        <form
                                            className="finance-form"
                                            onSubmit={(event) =>
                                                handleExecutionUpload(event, 'documentation', 'documentations')
                                            }
                                        >
                                            <div className="finance-form-title">
                                                <Camera size={16} strokeWidth={2.4} />
                                                <span>Dokumentasi kegiatan</span>
                                            </div>
                                            <select
                                                disabled={!selectedLpj.execution?.can_upload_documentation}
                                                value={executionForms.documentation.category}
                                                onChange={(event) =>
                                                    updateExecutionUploadForm(
                                                        'documentation',
                                                        'category',
                                                        event.target.value
                                                    )
                                                }
                                            >
                                                {Object.entries(selectedLpj.execution?.documentation_categories ?? {}).map(
                                                    ([value, label]) => (
                                                        <option value={value} key={value}>
                                                            {label}
                                                        </option>
                                                    )
                                                )}
                                            </select>
                                            <textarea
                                                disabled={!selectedLpj.execution?.can_upload_documentation}
                                                placeholder="Caption dokumentasi"
                                                value={executionForms.documentation.caption}
                                                onChange={(event) =>
                                                    updateExecutionUploadForm(
                                                        'documentation',
                                                        'caption',
                                                        event.target.value
                                                    )
                                                }
                                            />
                                            <label className="check-row">
                                                <input
                                                    disabled={!selectedLpj.execution?.can_upload_documentation}
                                                    type="checkbox"
                                                    checked={executionForms.documentation.include_in_report}
                                                    onChange={(event) =>
                                                        updateExecutionUploadForm(
                                                            'documentation',
                                                            'include_in_report',
                                                            event.target.checked
                                                        )
                                                    }
                                                />
                                                <span>Masuk LPJ</span>
                                            </label>
                                            <label className="file-button">
                                                <UploadCloud size={15} strokeWidth={2.4} />
                                                <span>{executionForms.documentation.file?.name ?? 'Upload foto/PDF'}</span>
                                                <input
                                                    disabled={!selectedLpj.execution?.can_upload_documentation}
                                                    accept="image/*,.pdf"
                                                    type="file"
                                                    onChange={(event) =>
                                                        updateExecutionUploadForm(
                                                            'documentation',
                                                            'file',
                                                            event.target.files?.[0] ?? null
                                                        )
                                                    }
                                                />
                                            </label>
                                            <button
                                                className="save-profile-button"
                                                type="submit"
                                                disabled={!selectedLpj.execution?.can_upload_documentation}
                                            >
                                                <UploadCloud size={17} strokeWidth={2.5} />
                                                Simpan Dokumentasi
                                            </button>
                                        </form>

                                        <form
                                            className="finance-form"
                                            onSubmit={(event) => handleExecutionUpload(event, 'attachment', 'attachments')}
                                        >
                                            <div className="finance-form-title">
                                                <Paperclip size={16} strokeWidth={2.4} />
                                                <span>Lampiran pendukung</span>
                                            </div>
                                            <input
                                                disabled={!selectedLpj.execution?.can_upload_documentation}
                                                placeholder="Judul lampiran"
                                                value={executionForms.attachment.title}
                                                onChange={(event) =>
                                                    updateExecutionUploadForm('attachment', 'title', event.target.value)
                                                }
                                                required
                                            />
                                            <textarea
                                                disabled={!selectedLpj.execution?.can_upload_documentation}
                                                placeholder="Keterangan lampiran"
                                                value={executionForms.attachment.description}
                                                onChange={(event) =>
                                                    updateExecutionUploadForm('attachment', 'description', event.target.value)
                                                }
                                            />
                                            <label className="check-row">
                                                <input
                                                    disabled={!selectedLpj.execution?.can_upload_documentation}
                                                    type="checkbox"
                                                    checked={executionForms.attachment.include_in_report}
                                                    onChange={(event) =>
                                                        updateExecutionUploadForm(
                                                            'attachment',
                                                            'include_in_report',
                                                            event.target.checked
                                                        )
                                                    }
                                                />
                                                <span>Masuk LPJ</span>
                                            </label>
                                            <label className="file-button">
                                                <FileText size={15} strokeWidth={2.4} />
                                                <span>{executionForms.attachment.file?.name ?? 'Upload lampiran'}</span>
                                                <input
                                                    disabled={!selectedLpj.execution?.can_upload_documentation}
                                                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx"
                                                    type="file"
                                                    onChange={(event) =>
                                                        updateExecutionUploadForm(
                                                            'attachment',
                                                            'file',
                                                            event.target.files?.[0] ?? null
                                                        )
                                                    }
                                                />
                                            </label>
                                            <button
                                                className="save-profile-button"
                                                type="submit"
                                                disabled={!selectedLpj.execution?.can_upload_documentation}
                                            >
                                                <UploadCloud size={17} strokeWidth={2.5} />
                                                Simpan Lampiran
                                            </button>
                                        </form>
                                    </div>

                                    <section className="execution-card">
                                        <div className="finance-form-title">
                                            <FileText size={16} strokeWidth={2.4} />
                                            <span>File tersimpan</span>
                                        </div>
                                        <div className="file-list">
                                            {(selectedLpj.execution?.documentations ?? []).map((item) => (
                                                <a href={item.url} key={`documentation-${item.id}`} target="_blank">
                                                    <Camera size={15} strokeWidth={2.4} />
                                                    <span>{item.caption || item.original_name || item.category_label}</span>
                                                    {item.include_in_report && <b>LPJ</b>}
                                                </a>
                                            ))}
                                            {(selectedLpj.execution?.attachments ?? []).map((item) => (
                                                <a href={item.url} key={`attachment-${item.id}`} target="_blank">
                                                    <Paperclip size={15} strokeWidth={2.4} />
                                                    <span>{item.title}</span>
                                                    {item.include_in_report && <b>LPJ</b>}
                                                </a>
                                            ))}
                                            {(selectedLpj.execution?.documentations ?? []).length === 0 &&
                                                (selectedLpj.execution?.attachments ?? []).length === 0 && (
                                                    <div className="empty-state">Belum ada dokumentasi atau lampiran.</div>
                                                )}
                                        </div>
                                    </section>
                                </>
                            )}

                            {isFinanceNav && <section className="finance-panel">
                                <div className="narrative-header">
                                    <div>
                                        <p className="section-kicker">Dana Kegiatan</p>
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
                                                {transaction.admin_note && (
                                                    <small>Catatan Admin: {transaction.admin_note}</small>
                                                )}
                                            </div>
                                            <b>{formatCurrency(transaction.amount)}</b>
                                            {transaction.can_submit_revision && (
                                                <form
                                                    className="revision-form"
                                                    onSubmit={(event) => handleRevisionSubmit(event, transaction)}
                                                >
                                                    <select
                                                        value={revisionForms[transaction.id]?.category ?? transaction.category}
                                                        onChange={(event) =>
                                                            updateRevisionForm(
                                                                transaction.id,
                                                                'category',
                                                                event.target.value
                                                            )
                                                        }
                                                        required
                                                    >
                                                        {(selectedLpj.finance_category_options ?? []).map((category) => (
                                                            <option value={category} key={category}>
                                                                {category}
                                                            </option>
                                                        ))}
                                                    </select>
                                                    <input
                                                        type="date"
                                                        value={revisionForms[transaction.id]?.spent_at ?? transaction.spent_at}
                                                        onChange={(event) =>
                                                            updateRevisionForm(
                                                                transaction.id,
                                                                'spent_at',
                                                                event.target.value
                                                            )
                                                        }
                                                        required
                                                    />
                                                    <textarea
                                                        value={
                                                            revisionForms[transaction.id]?.description ??
                                                            transaction.description
                                                        }
                                                        onChange={(event) =>
                                                            updateRevisionForm(
                                                                transaction.id,
                                                                'description',
                                                                event.target.value
                                                            )
                                                        }
                                                        required
                                                    />
                                                    <input
                                                        placeholder="Alasan jika tidak ada bukti"
                                                        value={revisionForms[transaction.id]?.no_proof_reason ?? ''}
                                                        onChange={(event) =>
                                                            updateRevisionForm(
                                                                transaction.id,
                                                                'no_proof_reason',
                                                                event.target.value
                                                            )
                                                        }
                                                    />
                                                    <label className="file-button">
                                                        <UploadCloud size={15} strokeWidth={2.4} />
                                                        <span>
                                                            {revisionForms[transaction.id]?.proof?.name ?? 'Upload bukti revisi'}
                                                        </span>
                                                        <input
                                                            accept="image/*,.pdf"
                                                            type="file"
                                                            onChange={(event) =>
                                                                updateRevisionForm(
                                                                    transaction.id,
                                                                    'proof',
                                                                    event.target.files?.[0] ?? null
                                                                )
                                                            }
                                                        />
                                                    </label>
                                                    <button className="save-profile-button" type="submit">
                                                        <Save size={17} strokeWidth={2.5} />
                                                        Kirim Revisi
                                                    </button>
                                                </form>
                                            )}
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
