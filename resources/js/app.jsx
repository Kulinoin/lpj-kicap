import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import {
    ArrowLeft,
    Banknote,
    Building2,
    CalendarDays,
    Camera,
    CheckCircle2,
    ChevronRight,
    ClipboardList,
    Clock3,
    FileText,
    History,
    Home,
    LockKeyhole,
    LogOut,
    MapPin,
    Paperclip,
    Phone,
    PlusCircle,
    Printer,
    ReceiptText,
    Save,
    Search,
    Send,
    Settings,
    UploadCloud,
    UserRound,
    UsersRound,
    Wallet,
    Wifi,
    WifiOff,
} from 'lucide-react';
import { registerSW } from 'virtual:pwa-register';
import '../css/app.css';

registerSW({ immediate: true });

const navItems = [
    { key: 'beranda', label: 'Beranda', icon: Home },
    { key: 'operasional', label: 'Operasional', icon: PlusCircle },
    { key: 'keuangan', label: 'Keuangan', icon: Wallet, isPrimary: true },
    { key: 'dokumentasi', label: 'Dokumentasi', icon: Camera },
    { key: 'catatan', label: 'Catatan', icon: FileText },
];

const emptyProfile = {
    name: '',
    username: '',
    email: '',
    whatsapp: '',
    role_label: 'Petugas Lapangan',
    organization_name: 'PT. Kazoku Indonesia Center',
    member_since: '',
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

const IMAGE_UPLOAD_MAX_EDGE = 1800;
const IMAGE_UPLOAD_QUALITY = 0.82;
const IMAGE_UPLOAD_SKIP_BELOW_BYTES = 700 * 1024;

function readFileAsDataUrl(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(reader.error ?? new Error('Gagal membaca file gambar.'));
        reader.readAsDataURL(file);
    });
}

function loadImageFromDataUrl(dataUrl) {
    return new Promise((resolve, reject) => {
        const image = new Image();

        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error('Gagal memproses gambar.'));
        image.src = dataUrl;
    });
}

async function compressImageFileForUpload(file) {
    if (!file || typeof File === 'undefined' || !(file instanceof File)) {
        return file;
    }

    const mime = file.type || '';

    if (!mime.startsWith('image/') || mime === 'image/gif' || mime === 'image/svg+xml') {
        return file;
    }

    if (file.size <= IMAGE_UPLOAD_SKIP_BELOW_BYTES) {
        return file;
    }

    const dataUrl = await readFileAsDataUrl(file);
    const image = await loadImageFromDataUrl(dataUrl);

    const originalWidth = image.naturalWidth || image.width;
    const originalHeight = image.naturalHeight || image.height;
    const maxSide = Math.max(originalWidth, originalHeight);

    if (!originalWidth || !originalHeight || !maxSide) {
        return file;
    }

    const scale = Math.min(1, IMAGE_UPLOAD_MAX_EDGE / maxSide);
    const targetWidth = Math.max(1, Math.round(originalWidth * scale));
    const targetHeight = Math.max(1, Math.round(originalHeight * scale));

    const canvas = document.createElement('canvas');
    canvas.width = targetWidth;
    canvas.height = targetHeight;

    const context = canvas.getContext('2d', { alpha: false });
    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, targetWidth, targetHeight);
    context.drawImage(image, 0, 0, targetWidth, targetHeight);

    const blob = await new Promise((resolve) => {
        canvas.toBlob(resolve, 'image/jpeg', IMAGE_UPLOAD_QUALITY);
    });

    if (!blob || blob.size >= file.size) {
        return file;
    }

    const baseName = (file.name || 'upload')
        .replace(/\.[^.]+$/, '')
        .replace(/[^\w.-]+/g, '-')
        .replace(/-+$/, '') || 'upload';

    return new File([blob], `${baseName}-compressed.jpg`, {
        type: 'image/jpeg',
        lastModified: Date.now(),
    });
}

async function normalizeImageUploadFile(file) {
    try {
        return await compressImageFileForUpload(file);
    } catch (error) {
        console.warn('Image compression failed, using original file.', error);
        return file;
    }
}

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

function formatShortDateRange(lpj) {
    const options = { day: '2-digit', month: 'short' };

    if (!lpj.start_date && !lpj.end_date) {
        return 'Tanggal belum diisi';
    }

    const start = lpj.start_date ? new Date(lpj.start_date) : null;
    const end = lpj.end_date ? new Date(lpj.end_date) : null;

    if (!start || !end || lpj.start_date === lpj.end_date) {
        return (start ?? end)?.toLocaleDateString('id-ID', options) ?? 'Tanggal belum diisi';
    }

    return `${start.toLocaleDateString('id-ID', options)} - ${end.toLocaleDateString('id-ID', {
        ...options,
        year: 'numeric',
    })}`;
}

function initials(name) {
    return (name || 'User')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
}

function firstName(name) {
    return (name || 'Petugas').trim().split(/\s+/)[0] || 'Petugas';
}

function statusClass(status) {
    return status === 'finish' ? 'finish' : status === 'aktif' ? 'aktif' : 'draft';
}

function statusAccent(status) {
    return status === 'finish' ? 'finish' : status === 'aktif' ? 'aktif' : 'draft';
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
        current_password: '',
        password: '',
        password_confirmation: '',
    });
    const [financeForms, setFinanceForms] = useState(emptyFinanceForms);
    const [financeEntryMode, setFinanceEntryMode] = useState('expense');
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
    const [financeDetailDialog, setFinanceDetailDialog] = useState(null);
    const [financeDetailLoadingId, setFinanceDetailLoadingId] = useState(null);
    const [showAllFinanceHistory, setShowAllFinanceHistory] = useState(false);
    const [executionMessage, setExecutionMessage] = useState('');
    const [operationalSaveMessage, setOperationalSaveMessage] = useState('');
    const [activeNav, setActiveNav] = useState('beranda');
    const [galleryPreview, setGalleryPreview] = useState(null);
    const [isOnline, setIsOnline] = useState(navigator.onLine);
    const [eventSearch, setEventSearch] = useState('');
    const [eventFilter, setEventFilter] = useState('tugas');
    const [profileView, setProfileView] = useState('home');

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
                    current_password: '',
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
        const updateStatus = () => setIsOnline(navigator.onLine);

        window.addEventListener('online', updateStatus);
        window.addEventListener('offline', updateStatus);

        fetch('/health', { headers: { Accept: 'application/json' } })
            .then((response) => setIsOnline(response.ok && navigator.onLine))
            .catch(() => setIsOnline(false));

        return () => {
            window.removeEventListener('online', updateStatus);
            window.removeEventListener('offline', updateStatus);
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

        if (file) {
            submitProfile({ photo: file, message: 'Foto profil tersimpan' });
        }
    };

    const submitProfile = ({ photo = profilePhoto, includePassword = false, message = 'Profil tersimpan' } = {}) => {
        setIsSavingProfile(true);
        setProfileMessage('');

        const formData = new FormData();
        formData.append('name', profileForm.name);
        formData.append('whatsapp', profileForm.whatsapp);

        if (photo) {
            formData.append('profile_photo', photo);
        }

        if (includePassword && profileForm.password) {
            formData.append('current_password', profileForm.current_password);
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
                    current_password: '',
                    password: '',
                    password_confirmation: '',
                });
                setProfileMessage(message);
            })
            .catch((error) => {
                const firstMessage = Object.values(error?.errors ?? {})?.[0]?.[0];
                setProfileMessage(firstMessage ?? 'Profil belum tersimpan');
            })
            .finally(() => {
                setIsSavingProfile(false);
            });
    };

    const handleProfileSubmit = (event) => {
        event.preventDefault();
        submitProfile();
    };

    const saveProfileFromSubpage = () => {
        if (profileView === 'settings') {
            if (!profileForm.current_password && !profileForm.password && !profileForm.password_confirmation) {
                setProfileView('home');
                return;
            }

            if (!profileForm.current_password || !profileForm.password || profileForm.password !== profileForm.password_confirmation) {
                setProfileMessage('Lengkapi password lama dan pastikan konfirmasi cocok');
                return;
            }

            submitProfile({ includePassword: true, message: 'Password tersimpan' });
            setProfileView('home');
            return;
        }

        if (profileView === 'personal') {
            submitProfile();
        }

        setProfileView('home');
    };

    const updateFinanceForm = async (formKey, field, value) => {
        if ((field === 'proof' || field === 'file' || field === 'photo' || field === 'profile_photo') && typeof File !== 'undefined' && value instanceof File) {
            value = await normalizeImageUploadFile(value);
        }

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
                formData.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : value);
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

    const updateRevisionForm = async (transactionId, field, value) => {
        if ((field === 'proof' || field === 'file' || field === 'photo' || field === 'profile_photo') && typeof File !== 'undefined' && value instanceof File) {
            value = await normalizeImageUploadFile(value);
        }

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
                formData.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : value);
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
            assigned: lpjs.length,
        };
    }, [lpjs]);

    const activeLpjs = useMemo(() => lpjs.filter((lpj) => lpj.status === 'aktif'), [lpjs]);
    const finishedLpjs = useMemo(() => lpjs.filter((lpj) => lpj.status === 'finish'), [lpjs]);
    const avatarSource = profilePhotoPreview ?? profile.avatar_url;
    const isHomeNav = activeNav === 'beranda';
    const isEventsNav = activeNav === 'events';
    const isProfileNav = activeNav === 'profil';
    const isNotesNav = activeNav === 'catatan';
    const isOperationalNav = activeNav === 'operasional';

    const handleFinanceDetailPreview = async (transaction) => {
        if (!selectedLpj?.id || !transaction?.id) {
            return;
        }

        setFinanceDetailLoadingId(transaction.id);
        setFinanceMessage('');

        // KICAP_TRANSFER_HISTORY_LOCAL_DETAIL
        if (transaction.history_type === 'transfer' || transaction.is_transfer) {
            setFinanceDetailDialog({
                ...transaction,
                proof: null,
                no_proof_reason: transaction.no_proof_reason || 'Transfer saldo tidak membutuhkan bukti.',
            });
            setFinanceDetailLoadingId(null);
            return;
        }

        try {
            const response = await fetch(
                `/api/app/lpjs/${selectedLpj.id}/financial-transactions/${transaction.id}`,
                { headers: { Accept: 'application/json' } }
            );

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                const firstMessage = Object.values(payload.errors ?? {})?.[0]?.[0];
                throw new Error(firstMessage ?? payload.message ?? 'Detail transaksi tidak dapat dibuka.');
            }

            setFinanceDetailDialog(payload.data?.transaction ?? transaction);
        } catch (error) {
            const message = error.message ?? 'Detail transaksi tidak dapat dibuka.';
            setFinanceMessage(message);
            window.alert(message);
        } finally {
            setFinanceDetailLoadingId(null);
        }
    };

    const closeFinanceDetailDialog = () => {
        setFinanceDetailDialog(null);
        setShowAllFinanceHistory(false);
        setFinanceDetailLoadingId(null);
    };
    const isFinanceNav = activeNav === 'keuangan';
    const isDocumentationNav = activeNav === 'dokumentasi';
    const showDetail = (isHomeNav || isEventsNav || isNotesNav || isOperationalNav || isFinanceNav || isDocumentationNav) && selectedLpjId;
    const pageTitle = showDetail
        ? {
            beranda: 'Detail Event',
            events: 'Detail Event',
            catatan: 'Catatan petugas',
            operasional: 'Pelaksanaan event',
            keuangan: 'Dana kegiatan',
            dokumentasi: 'Dokumentasi event',
        }[activeNav] ?? 'Detail Event'
        : isEventsNav
            ? 'Daftar Event'
            : isProfileNav
                ? profileView === 'home'
                    ? 'Profil'
                    : profileView === 'personal'
                        ? 'Personal Information'
                        : profileView === 'settings'
                            ? 'Settings'
                            : 'Activity History'
                : 'Beranda';
    const visibleLpjs = useMemo(() => {
        const sortByWorkPriority = (items) => [...items].sort((left, right) => {
            const leftActive = left.status === 'aktif' ? 0 : 1;
            const rightActive = right.status === 'aktif' ? 0 : 1;

            if (leftActive !== rightActive) {
                return leftActive - rightActive;
            }

            return right.id - left.id;
        });

        if (activeNav === 'catatan' || activeNav === 'operasional' || activeNav === 'keuangan') {
            return sortByWorkPriority(activeLpjs);
        }

        if (activeNav === 'dokumentasi') {
            return sortByWorkPriority(activeLpjs);
        }

        return sortByWorkPriority([...activeLpjs, ...finishedLpjs]);
    }, [activeLpjs, activeNav, finishedLpjs]);

    const dashboardEvents = useMemo(() => activeLpjs.slice(0, 2), [activeLpjs]);
    const latestTransaction = useMemo(() => {
        return lpjs
            .map((lpj) => ({ ...lpj.latest_transaction, event_title: lpj.title }))
            .find((transaction) => transaction?.category);
    }, [lpjs]);

    const filteredEvents = useMemo(() => {
        const query = eventSearch.trim().toLowerCase();

        return lpjs
            .filter((lpj) => {
                if (eventFilter === 'aktif') {
                    return lpj.status === 'aktif';
                }

                if (eventFilter === 'selesai') {
                    return lpj.status === 'finish';
                }

                return true;
            })
            .filter((lpj) => {
                if (!query) {
                    return true;
                }

                return [lpj.title, lpj.location, lpj.type, lpj.code]
                    .filter(Boolean)
                    .some((value) => value.toLowerCase().includes(query));
            });
    }, [eventFilter, eventSearch, lpjs]);

    const lpjHeading = {
        beranda: 'Event terbaru',
        catatan: 'Catatan petugas',
        operasional: 'Pelaksanaan event',
        keuangan: 'Dana kegiatan',
        dokumentasi: 'Dokumentasi event',
    }[activeNav] ?? 'Aktif dan selesai';

    const emptyLpjMessage = {
        catatan: 'Belum ada event aktif untuk catatan petugas.',
        operasional: 'Belum ada event aktif untuk data pelaksanaan.',
        keuangan: 'Belum ada event aktif untuk dana kegiatan.',
        dokumentasi: 'Belum ada event aktif untuk dokumentasi.',
    }[activeNav] ?? 'Belum ada event aktif atau selesai yang ditugaskan.';

    const openLpjDetail = (lpjId) => {
        setSelectedLpjId(lpjId);
    };

    const clearSelectedLpj = () => {
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
        setGalleryPreview(null);
    };

    const closeLpjDetail = () => {
        clearSelectedLpj();
    };

    const handleNavChange = (navKey) => {
        setActiveNav(navKey);
        setProfileView('home');
        clearSelectedLpj();
    };

    const openProfile = () => {
        setActiveNav('profil');
        setProfileView('home');
        clearSelectedLpj();
    };

    const openEventList = () => {
        setActiveNav('events');
        setEventFilter('tugas');
        clearSelectedLpj();
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

    const updateExecutionUploadForm = async (section, field, value) => {
        if ((field === 'proof' || field === 'file' || field === 'photo' || field === 'profile_photo') && typeof File !== 'undefined' && value instanceof File) {
            value = await normalizeImageUploadFile(value);
        }

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
                formData.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : value);
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
            <header className={`app-header ${isHomeNav && !showDetail ? 'home-header' : 'compact-header'}`}>
                {isHomeNav && !showDetail ? (
                    <>
                        <button className="avatar-button" type="button" onClick={openProfile} aria-label="Buka profil">
                            {avatarSource ? <img src={avatarSource} alt="" /> : <span>{initials(profile.name)}</span>}
                        </button>
                        <div>
                            <p className="section-kicker">Halo, {firstName(profile.name)}!</p>
                            <h1>{profile.organization_name || 'PT. Kazoku Indonesia Center'}</h1>
                        </div>
                    </>
                ) : (
                    <>
                        <button
                            className="icon-back-button"
                            type="button"
                            onClick={() => {
                                if (showDetail) {
                                    closeLpjDetail();
                                    return;
                                }

                                if (isProfileNav && profileView !== 'home') {
                                    saveProfileFromSubpage();
                                    return;
                                }

                                handleNavChange('beranda');
                            }}
                            aria-label="Kembali"
                        >
                            <ArrowLeft size={20} strokeWidth={2.4} />
                        </button>
                        <h1>{pageTitle}</h1>
                    </>
                )}

                <div className="header-actions">
                    <span className={`online-dot ${isOnline ? 'online' : 'offline'}`} title={isOnline ? 'Online' : 'Offline'}>
                        {isOnline ? <Wifi size={13} strokeWidth={2.6} /> : <WifiOff size={13} strokeWidth={2.6} />}
                    </span>
                    {!isHomeNav && !isProfileNav && (
                        <button className="avatar-button small" type="button" onClick={openProfile} aria-label="Buka profil">
                            {avatarSource ? <img src={avatarSource} alt="" /> : <span>{initials(profile.name)}</span>}
                        </button>
                    )}
                </div>
            </header>

            {isHomeNav && !showDetail && (
                <>
                    <section className="summary-grid" aria-label="Ringkasan event">
                        <article className="summary-tile blue">
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
                            <span>Tugas</span>
                            <strong>{totals.assigned}</strong>
                        </article>
                    </section>

                    <section className="lpj-section home-events-section">
                        <div className="section-heading">
                            <div>
                                <h2>Event Aktif</h2>
                            </div>
                            <button className="see-all-button" type="button" onClick={openEventList}>
                                Lihat Semua
                            </button>
                        </div>

                        <div className="lpj-list">
                            {isLoading && <div className="empty-state">Memuat event...</div>}

                            {!isLoading && dashboardEvents.length === 0 && (
                                <div className="empty-state">Belum ada event aktif yang ditugaskan.</div>
                            )}

                            {!isLoading &&
                                dashboardEvents.map((lpj) => (
                                    <button
                                        className={`event-card ${statusAccent(lpj.status)}`}
                                        key={lpj.id}
                                        type="button"
                                        onClick={() => openLpjDetail(lpj.id)}
                                    >
                                        <div className="event-card-top">
                                            <span className={`status-pill ${statusClass(lpj.status)}`}>{lpj.status_label}</span>
                                            <span className="event-date">{formatShortDateRange(lpj)}</span>
                                        </div>
                                        <h3>{lpj.title}</h3>
                                        <p>{lpj.location ?? '-'} • {lpj.participant_count ?? 0} Peserta</p>
                                        <div className="progress-row">
                                            <span>{lpj.progress_label ?? 'Kelengkapan Lapangan'}</span>
                                            <b>{lpj.progress_percentage ?? 0}%</b>
                                        </div>
                                        <div className="progress-track">
                                            <span style={{ width: `${lpj.progress_percentage ?? 0}%` }} />
                                        </div>
                                    </button>
                                ))}
                        </div>
                    </section>

                    <button
                        className="last-activity-card"
                        type="button"
                        onClick={() => latestTransaction && handleNavChange('keuangan')}
                    >
                        <span className="activity-icon">
                            <History size={17} strokeWidth={2.4} />
                        </span>
                        <span>
                            <b>Transaksi Terakhir</b>
                            {latestTransaction
                                ? `${latestTransaction.category} - ${formatCurrency(latestTransaction.amount)} (${latestTransaction.status_label})`
                                : 'Belum ada transaksi terbaru'}
                        </span>
                        <ChevronRight size={16} strokeWidth={2.5} />
                    </button>
                </>
            )}

            {isEventsNav && !showDetail && (
                <>
                    <label className="search-box">
                        <Search size={17} strokeWidth={2.4} />
                        <input
                            placeholder="Cari nama event..."
                            value={eventSearch}
                            onChange={(event) => setEventSearch(event.target.value)}
                        />
                    </label>

                    <div className="event-filter-tabs" role="tablist" aria-label="Filter event">
                        {[
                            ['semua', 'Semua'],
                            ['aktif', 'Aktif'],
                            ['selesai', 'Selesai'],
                            ['tugas', 'Tugas'],
                        ].map(([key, label]) => (
                            <button
                                className={eventFilter === key ? 'is-active' : ''}
                                key={key}
                                type="button"
                                onClick={() => setEventFilter(key)}
                            >
                                {label}
                            </button>
                        ))}
                    </div>

                    <div className="event-list-screen">
                        {isLoading && <div className="empty-state">Memuat event...</div>}
                        {!isLoading && filteredEvents.length === 0 && (
                            <div className="empty-state">Event tidak ditemukan.</div>
                        )}
                        {!isLoading &&
                            filteredEvents.map((lpj) => (
                                <button
                                    className={`event-list-card ${statusAccent(lpj.status)}`}
                                    key={lpj.id}
                                    type="button"
                                    onClick={() => openLpjDetail(lpj.id)}
                                >
                                    <div>
                                        <div className="event-list-title">
                                            <h3>{lpj.title}</h3>
                                            <span className={`status-pill ${statusClass(lpj.status)}`}>{lpj.status_label}</span>
                                        </div>
                                        <span className="event-list-meta">
                                            <CalendarDays size={14} strokeWidth={2.3} />
                                            {formatShortDateRange(lpj)}
                                        </span>
                                        <span className="event-list-meta">
                                            <MapPin size={14} strokeWidth={2.3} />
                                            {lpj.location ?? 'Belum ditentukan'}
                                        </span>
                                        <div className="progress-row">
                                            <span>{lpj.progress_label ?? 'Kelengkapan Lapangan'}</span>
                                            <b>{lpj.progress_percentage ?? 0}%</b>
                                        </div>
                                        <div className="progress-line">
                                            <span style={{ width: `${lpj.progress_percentage ?? 0}%` }} />
                                        </div>
                                    </div>
                                    <ChevronRight size={18} strokeWidth={2.5} />
                                </button>
                            ))}
                    </div>
                </>
            )}

            {!isHomeNav && !isEventsNav && !isProfileNav && !showDetail && (
                <>
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
                                        className={`event-list-card compact ${statusAccent(lpj.status)}`}
                                        key={lpj.id}
                                        type="button"
                                        onClick={() => openLpjDetail(lpj.id)}
                                    >
                                        <div>
                                            <div className="event-list-title">
                                                <h3>{lpj.title}</h3>
                                                <span className={`status-pill ${statusClass(lpj.status)}`}>{lpj.status_label}</span>
                                            </div>
                                            <span className="event-list-meta">
                                                <CalendarDays size={14} strokeWidth={2.3} />
                                                {formatShortDateRange(lpj)}
                                            </span>
                                            <span className="event-list-meta">
                                                <MapPin size={14} strokeWidth={2.3} />
                                                {lpj.location ?? 'Belum ditentukan'}
                                            </span>
                                        </div>
                                        <ChevronRight size={18} strokeWidth={2.5} />
                                    </button>
                                ))}
                        </div>
                    </section>
                </>
            )}

            {showDetail && (
                <section className="detail-panel">
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
                                        <span>{isHomeNav ? 'LPJ' : 'Kelengkapan'}</span>
                                        <strong>
                                            {isHomeNav
                                                ? selectedLpj.can_print_report
                                                    ? 'LPJ tersedia'
                                                    : 'Belum tersedia'
                                                : selectedLpj.completeness_label ?? 'Belum Lengkap'}
                                        </strong>
                                    </div>
                                    {!isHomeNav && !selectedLpj.can_print_report && (
                                        <button
                                            type="button"
                                            disabled={!selectedLpj.can_submit_review}
                                            onClick={handleSubmitReview}
                                        >
                                            <ClipboardList size={15} strokeWidth={2.4} />
                                            Ajukan Review
                                        </button>
                                    )}
                                    {selectedLpj.can_print_report && selectedLpj.report_print_url && (
                                        <a href={selectedLpj.report_print_url} target="_blank" rel="noreferrer">
                                            <Printer size={15} strokeWidth={2.4} />
                                            Cetak LPJ
                                        </a>
                                    )}
                                </div>
                            </article>

                            {isHomeNav && (
                                <section className="event-detail-card">
                                    <div className="finance-form-title">
                                        <ClipboardList size={16} strokeWidth={2.4} />
                                        <span>Detail event</span>
                                    </div>
                                    <div className="event-detail-list">
                                        <div>
                                            <span>Kode Event</span>
                                            <strong>{selectedLpj.code}</strong>
                                        </div>
                                        <div>
                                            <span>Tipe Event</span>
                                            <strong>{selectedLpj.type ?? '-'}</strong>
                                        </div>
                                        <div>
                                            <span>Status</span>
                                            <strong>{selectedLpj.status_label ?? '-'}</strong>
                                        </div>
                                        <div>
                                            <span>Kelengkapan</span>
                                            <strong>{selectedLpj.completeness_label ?? '-'}</strong>
                                        </div>
                                        <div>
                                            <span>Tanggal</span>
                                            <strong>{formatDateRange(selectedLpj)}</strong>
                                        </div>
                                        <div>
                                            <span>Lokasi</span>
                                            <strong>{selectedLpj.location ?? '-'}</strong>
                                        </div>
                                        <div>
                                            <span>Penanggung Jawab</span>
                                            <strong>{selectedLpj.person_in_charge ?? '-'}</strong>
                                        </div>
                                        <div>
                                            <span>Sumber Dana</span>
                                            <strong>{selectedLpj.funding_source ?? '-'}</strong>
                                        </div>
                                        <div>
                                            <span>Nomor Surat/Tugas</span>
                                            <strong>{selectedLpj.assignment_letter_number ?? '-'}</strong>
                                        </div>
                                        <div>
                                            <span>Periode LPJ</span>
                                            <strong>{selectedLpj.period_label ?? '-'}</strong>
                                        </div>
                                        <div>
                                            <span>Penyelenggara Eksternal</span>
                                            <strong>{selectedLpj.external_organizer ?? '-'}</strong>
                                        </div>
                                        <div className="wide">
                                            <span>Peran Lembaga</span>
                                            <strong>{selectedLpj.organization_role ?? '-'}</strong>
                                        </div>
                                    </div>
                                </section>
                            )}

                            {isNotesNav && (
                                <>
                                    <div className="narrative-header">
                                        <div>
                                            <p className="section-kicker">Catatan Petugas</p>
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

                            {isOperationalNav && (
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
                            )}

                            {isDocumentationNav && (
                                <>
                                    <div className="narrative-header">
                                        <div>
                                            <p className="section-kicker">Dokumentasi</p>
                                            <h2>
                                                {selectedLpj.execution?.can_upload_documentation
                                                    ? 'Dokumentasi dan lampiran'
                                                    : 'Dokumentasi terkunci'}
                                            </h2>
                                        </div>
                                        <div className="save-state">
                                            <Camera size={14} strokeWidth={2.4} />
                                            {selectedLpj.execution?.can_upload_documentation ? 'Bisa upload' : 'Hanya baca'}
                                        </div>
                                    </div>

                                    {executionMessage && <div className="profile-message">{executionMessage}</div>}

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
                                                item.is_image ? (
                                                    <button
                                                        key={`documentation-${item.id}`}
                                                        type="button"
                                                        onClick={() => setGalleryPreview({
                                                            title: item.caption || item.original_name || item.category_label,
                                                            url: item.url,
                                                        })}
                                                    >
                                                        <Camera size={15} strokeWidth={2.4} />
                                                        <span>{item.caption || item.original_name || item.category_label}</span>
                                                        {item.include_in_report && <b>LPJ</b>}
                                                    </button>
                                                ) : (
                                                    <a href={item.url} key={`documentation-${item.id}`} target="_blank" rel="noreferrer">
                                                        <Camera size={15} strokeWidth={2.4} />
                                                        <span>{item.caption || item.original_name || item.category_label}</span>
                                                        {item.include_in_report && <b>LPJ</b>}
                                                    </a>
                                                )
                                            ))}
                                            {(selectedLpj.execution?.attachments ?? []).map((item) => (
                                                item.is_image ? (
                                                    <button
                                                        key={`attachment-${item.id}`}
                                                        type="button"
                                                        onClick={() => setGalleryPreview({
                                                            title: item.title,
                                                            url: item.url,
                                                        })}
                                                    >
                                                        <Paperclip size={15} strokeWidth={2.4} />
                                                        <span>{item.title}</span>
                                                        {item.include_in_report && <b>LPJ</b>}
                                                    </button>
                                                ) : (
                                                    <a href={item.url} key={`attachment-${item.id}`} target="_blank" rel="noreferrer">
                                                        <Paperclip size={15} strokeWidth={2.4} />
                                                        <span>{item.title}</span>
                                                        {item.include_in_report && <b>LPJ</b>}
                                                    </a>
                                                )
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

                                {(() => {
                                    const selectedFinanceMode = financeEntryMode === 'transfer'
                                        ? 'transfer'
                                        : financeEntryMode === 'advance'
                                            ? 'advance'
                                            : 'expense';

                                    const isTransferMode = selectedFinanceMode === 'transfer';
                                    const isAdvanceMode = selectedFinanceMode === 'advance';
                                    const activeFinanceForm = financeForms[selectedFinanceMode];
                                    const financeEndpoint = isTransferMode
                                        ? 'balance-transfers'
                                        : isAdvanceMode
                                            ? 'advance-expenses'
                                            : 'expenses';
                                    const canUseFinanceForm = Boolean(selectedLpj.finance?.can_input_finance);
                                    const canSubmitFinanceForm = canUseFinanceForm && (!isTransferMode || selectedLpj.finance?.can_transfer_balance);

                                    return (
                                        <form
                                            className="finance-form finance-form-unified"
                                            onSubmit={(event) => handleFinanceSubmit(event, selectedFinanceMode, financeEndpoint)}
                                        >
                                            <div className="finance-form-title">
                                                {isTransferMode ? (
                                                    <Send size={16} strokeWidth={2.4} />
                                                ) : isAdvanceMode ? (
                                                    <Banknote size={16} strokeWidth={2.4} />
                                                ) : (
                                                    <ReceiptText size={16} strokeWidth={2.4} />
                                                )}
                                                <span>Catat keuangan</span>
                                            </div>

                                            <label className="finance-field-label">
                                                <span>Jenis catatan</span>
                                                <select
                                                    disabled={!canUseFinanceForm}
                                                    value={selectedFinanceMode}
                                                    onChange={(event) => setFinanceEntryMode(event.target.value)}
                                                    required
                                                >
                                                    <option value="expense">Pengeluaran dari Saldo Pegangan</option>
                                                    <option value="advance">Pengeluaran Dana Talangan / Dana Pribadi</option>
                                                    <option value="transfer">Transfer Saldo ke User Lain</option>
                                                </select>
                                            </label>

                                            {isTransferMode && !selectedLpj.finance?.can_transfer_balance && (
                                                <div className="finance-mode-hint">
                                                    Transfer saldo belum aktif untuk akun ini.
                                                </div>
                                            )}

                                            {!isTransferMode && (
                                                <>
                                                    <select
                                                        disabled={!canUseFinanceForm}
                                                        value={activeFinanceForm.category}
                                                        onChange={(event) =>
                                                            updateFinanceForm(selectedFinanceMode, 'category', event.target.value)
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
                                                        disabled={!canUseFinanceForm}
                                                        inputMode="decimal"
                                                        placeholder="Nominal"
                                                        type="number"
                                                        min="1"
                                                        value={activeFinanceForm.amount}
                                                        onChange={(event) =>
                                                            updateFinanceForm(selectedFinanceMode, 'amount', event.target.value)
                                                        }
                                                        required
                                                    />

                                                    <input
                                                        disabled={!canUseFinanceForm}
                                                        type="date"
                                                        value={activeFinanceForm.spent_at}
                                                        onChange={(event) =>
                                                            updateFinanceForm(selectedFinanceMode, 'spent_at', event.target.value)
                                                        }
                                                        required
                                                    />

                                                    <textarea
                                                        disabled={!canUseFinanceForm}
                                                        placeholder={isAdvanceMode ? 'Keterangan dana talangan / dana pribadi' : 'Keterangan pengeluaran'}
                                                        value={activeFinanceForm.description}
                                                        onChange={(event) =>
                                                            updateFinanceForm(selectedFinanceMode, 'description', event.target.value)
                                                        }
                                                        required
                                                    />

                                                    <input
                                                        disabled={!canUseFinanceForm}
                                                        placeholder="Alasan jika tidak ada bukti"
                                                        value={activeFinanceForm.no_proof_reason}
                                                        onChange={(event) =>
                                                            updateFinanceForm(selectedFinanceMode, 'no_proof_reason', event.target.value)
                                                        }
                                                    />

                                                    <label className="file-button">
                                                        <UploadCloud size={15} strokeWidth={2.4} />
                                                        <span>{activeFinanceForm.proof?.name ?? 'Upload bukti'}</span>
                                                        <input
                                                            disabled={!canUseFinanceForm}
                                                            accept="image/*,.pdf"
                                                            type="file"
                                                            onChange={(event) =>
                                                                updateFinanceForm(selectedFinanceMode, 'proof', event.target.files?.[0] ?? null)
                                                            }
                                                        />
                                                    </label>
                                                </>
                                            )}

                                            {isTransferMode && (
                                                <>
                                                    <select
                                                        disabled={!canSubmitFinanceForm}
                                                        value={activeFinanceForm.recipient_user_id}
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
                                                        disabled={!canSubmitFinanceForm}
                                                        inputMode="decimal"
                                                        placeholder="Nominal"
                                                        type="number"
                                                        min="1"
                                                        value={activeFinanceForm.amount}
                                                        onChange={(event) => updateFinanceForm('transfer', 'amount', event.target.value)}
                                                        required
                                                    />

                                                    <input
                                                        disabled={!canSubmitFinanceForm}
                                                        placeholder="Catatan transfer"
                                                        value={activeFinanceForm.note}
                                                        onChange={(event) => updateFinanceForm('transfer', 'note', event.target.value)}
                                                    />
                                                </>
                                            )}

                                            <button
                                                className="save-profile-button"
                                                type="submit"
                                                disabled={!canSubmitFinanceForm}
                                            >
                                                {isTransferMode ? (
                                                    <Send size={17} strokeWidth={2.5} />
                                                ) : (
                                                    <Save size={17} strokeWidth={2.5} />
                                                )}
                                                {isTransferMode
                                                    ? 'Transfer Sekarang'
                                                    : isAdvanceMode
                                                        ? 'Simpan Talangan'
                                                        : 'Simpan Pengeluaran'}
                                            </button>
                                        </form>
                                    );
                                })()}
                                <div className="finance-history">
                                    <div className="finance-history-header">
                                        <h3>{showAllFinanceHistory ? 'Semua riwayat' : 'Riwayat terbaru'}</h3>
                                        {(selectedLpj.finance?.transactions ?? []).length > 10 && (
                                            <button
                                                type="button"
                                                className="finance-history-see-all"
                                                onClick={() => setShowAllFinanceHistory((current) => !current)}
                                            >
                                                {showAllFinanceHistory ? 'Tampilkan 10 terakhir' : 'Lihat semua'}
                                            </button>
                                        )}
                                    </div>

                                      {financeDetailDialog && (
                                          <div className="finance-minimal-dialog-backdrop" onClick={closeFinanceDetailDialog}>
                                              <section
                                                  className="finance-minimal-dialog"
                                                  role="dialog"
                                                  aria-modal="true"
                                                  onClick={(event) => event.stopPropagation()}
                                              >
                                                  <div className="finance-minimal-dialog-header">
                                                      <div>
                                                          <small>Detail Keuangan</small>
                                                          <h3>{financeDetailDialog.category ?? '-'}</h3>
                                                      </div>
                                                      <button type="button" onClick={closeFinanceDetailDialog}>
                                                          Tutup
                                                      </button>
                                                  </div>

                                                  <div className="finance-minimal-dialog-list">
                                                      <div>
                                                          <span>Kategori</span>
                                                          <b>{financeDetailDialog.category ?? '-'}</b>
                                                      </div>
                                                      <div>
                                                          <span>Nominal</span>
                                                          <b>{formatCurrency(financeDetailDialog.amount ?? 0)}</b>
                                                      </div>
                                                      <div>
                                                          <span>Tanggal</span>
                                                          <b>{financeDetailDialog.spent_at ?? '-'}</b>
                                                      </div>
                                                      <div>
                                                          <span>Keterangan</span>
                                                          <p>{financeDetailDialog.description || '-'}</p>
                                                      </div>
                                                  </div>

                                                  <div className="finance-minimal-proof">
                                          {financeDetailDialog.proof?.url ? (
                                              <>
                                                  <span>Bukti / Lampiran</span>
                                                  <p>Ada lampiran.</p>
                                              </>
                                          ) : (
                                              <>
                                                  <span>Alasan jika tidak ada bukti</span>
                                                  <p>{financeDetailDialog.no_proof_reason || 'Alasan belum diisi.'}</p>
                                              </>
                                          )}
                                      </div>
                                              </section>
                                          </div>
                                      )}
                                    {(selectedLpj.finance?.transactions ?? []).length === 0 && (
                                        <div className="empty-state">Belum ada transaksi keuangan.</div>
                                    )}
                                    {((selectedLpj.finance?.transactions ?? []).slice(0, showAllFinanceHistory ? undefined : 10)).map((transaction) => (
                                        <article
                                              key={transaction.id}
                                              className="finance-history-item-clickable"
                                              role="button"
                                              tabIndex={0}
                                              onClick={() => handleFinanceDetailPreview(transaction)}
                                              onKeyDown={(event) => {
                                                  if (event.key === 'Enter' || event.key === ' ') {
                                                      event.preventDefault();
                                                      handleFinanceDetailPreview(transaction);
                                                  }
                                              }}
                                          >
                                            <div>
                                                <strong>
                                                      {transaction.category}
                                                      {transaction.spent_at ? ` / ${transaction.spent_at}` : ''}
                                                  </strong>
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

            {isProfileNav && (
                <section className="profile-panel">
                    {profileView === 'home' && (
                        <>
                            <div className="profile-hero-card">
                                <label className="avatar-picker large">
                                    {avatarSource ? (
                                        <img src={avatarSource} alt="" />
                                    ) : (
                                        <span>{initials(profile.name)}</span>
                                    )}
                                    <b>
                                        <Camera size={15} strokeWidth={2.4} />
                                    </b>
                                    <input accept="image/*" type="file" onChange={handleProfilePhotoChange} />
                                </label>
                                <h2>{profile.name || 'Pengguna'}</h2>
                                <p>{profile.role_label || 'Petugas'} {profile.organization_name ? `• ${profile.organization_name}` : ''}</p>
                            </div>

                            <div className="profile-stats">
                                <article>
                                    <span>Events</span>
                                    <strong>{totals.finished}</strong>
                                    <small>Completed</small>
                                </article>
                                <article>
                                    <span>Member</span>
                                    <strong>{profile.member_since || '2024'}</strong>
                                    <small>Active Tier</small>
                                </article>
                            </div>

                            <div className="profile-menu-list">
                                <button type="button" onClick={() => setProfileView('personal')}>
                                    <span><UserRound size={18} strokeWidth={2.4} /></span>
                                    Personal Information
                                    <ChevronRight size={17} strokeWidth={2.4} />
                                </button>
                                <button type="button" onClick={() => setProfileView('history')}>
                                    <span><History size={18} strokeWidth={2.4} /></span>
                                    Activity History
                                    <ChevronRight size={17} strokeWidth={2.4} />
                                </button>
                                <button type="button" onClick={() => setProfileView('settings')}>
                                    <span><Settings size={18} strokeWidth={2.4} /></span>
                                    Settings
                                    <ChevronRight size={17} strokeWidth={2.4} />
                                </button>
                                <button className="logout-menu-button" type="button" onClick={handleLogout}>
                                    <span><LogOut size={18} strokeWidth={2.4} /></span>
                                    Logout
                                </button>
                            </div>

                            {profileMessage && <div className="profile-message">{profileMessage}</div>}

                            <p className="app-version">Kicap Event PWA v2.4.1</p>
                        </>
                    )}

                    {profileView === 'personal' && (
                        <form className="profile-form" onSubmit={handleProfileSubmit}>
                            <label className="app-field">
                                <span>
                                    <UserRound size={15} strokeWidth={2.4} />
                                    Nama
                                </span>
                                <input
                                    value={profileForm.name}
                                    onChange={(event) => setProfileForm((value) => ({ ...value, name: event.target.value }))}
                                    onBlur={() => submitProfile()}
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
                                    onBlur={() => submitProfile()}
                                />
                            </label>

                            <label className="app-field readonly">
                                <span>
                                    <Building2 size={15} strokeWidth={2.4} />
                                    Lembaga
                                </span>
                                <input value={profile.organization_name || 'PT. Kazoku Indonesia Center'} readOnly />
                            </label>

                            <label className="app-field readonly">
                                <span>
                                    <UserRound size={15} strokeWidth={2.4} />
                                    Email / Username
                                </span>
                                <input value={`${profile.email || '-'} • @${profile.username || '-'}`} readOnly />
                            </label>

                            {profileMessage && <div className="profile-message">{profileMessage}</div>}
                        </form>
                    )}

                    {profileView === 'settings' && (
                        <form className="profile-form" onSubmit={(event) => {
                            event.preventDefault();
                            submitProfile({ includePassword: true, message: 'Password tersimpan' });
                        }}>
                            <div className="password-box">
                                <div className="password-box-title">
                                    <LockKeyhole size={16} strokeWidth={2.4} />
                                    <span>Update password</span>
                                </div>
                                <input
                                    autoComplete="current-password"
                                    placeholder="Password lama"
                                    type="password"
                                    value={profileForm.current_password}
                                    onChange={(event) =>
                                        setProfileForm((value) => ({ ...value, current_password: event.target.value }))
                                    }
                                />
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
                                    placeholder="Konfirmasi password baru"
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
                        </form>
                    )}

                    {profileView === 'history' && (
                        <div className="activity-history">
                            {lpjs.map((lpj) => (
                                <article key={lpj.id}>
                                    <span className={`status-pill ${statusClass(lpj.status)}`}>{lpj.status_label}</span>
                                    <div>
                                        <strong>{lpj.title}</strong>
                                        <small>{formatShortDateRange(lpj)} • {lpj.assignment_role}</small>
                                    </div>
                                </article>
                            ))}
                            {lpjs.length === 0 && <div className="empty-state">Belum ada riwayat event.</div>}
                        </div>
                    )}
                </section>
            )}

            {galleryPreview && (
                <div className="gallery-preview" role="dialog" aria-modal="true" aria-label="Preview gambar">
                    <button
                        className="gallery-backdrop"
                        type="button"
                        aria-label="Tutup preview"
                        onClick={() => setGalleryPreview(null)}
                    />
                    <div className="gallery-frame">
                        <div className="gallery-topbar">
                            <strong>{galleryPreview.title}</strong>
                            <button type="button" onClick={() => setGalleryPreview(null)}>
                                Tutup
                            </button>
                        </div>
                        <img src={galleryPreview.url} alt={galleryPreview.title} />
                    </div>
                </div>
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
                            onClick={() => handleNavChange(item.key)}
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
