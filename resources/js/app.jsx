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
    const emptySelectionParticipantForm = {
        name: '',
        origin: '',
        participant_number: '',
        whatsapp: '',
        note: '',
    };

    const buildSelectionRegistrationForms = (participants = []) => {
        return (participants ?? []).reduce((forms, participant) => {
            forms[participant.id] = {
                participant_number: participant.participant_number ?? '',
                whatsapp: participant.whatsapp ?? '',
                note: participant.note ?? '',
                photo: null,
            };

            return forms;
        }, {});
    };

    const selectionStatusClass = (status) => {
        if (status === 'lulus') return 'is-pass';
        if (status === 'gugur' || status === 'tidak_hadir') return 'is-fail';
        if (status === 'aktif') return 'is-active';
        return '';
    };

    // KICAP_SELECTION_REGISTRATION_01

    const [profileView, setProfileView] = useState('home');
    const [selectionData, setSelectionData] = useState(null);
    const [selectionLoading, setSelectionLoading] = useState(false);
    const [selectionMessage, setSelectionMessage] = useState('');
    const [selectionSearch, setSelectionSearch] = useState('');
    const [selectionParticipantForm, setSelectionParticipantForm] = useState(emptySelectionParticipantForm);
    const [selectionRegistrationForms, setSelectionRegistrationForms] = useState({});
    const [expandedSelectionParticipantId, setExpandedSelectionParticipantId] = useState(null);
    const [editingSelectionParticipantId, setEditingSelectionParticipantId] = useState(null);
    const [previewSelectionPhotoParticipantId, setPreviewSelectionPhotoParticipantId] = useState(null);
    const [documentationMessage, setDocumentationMessage] = useState('');
    const [editingDocumentationId, setEditingDocumentationId] = useState(null);
    const [documentationEditForms, setDocumentationEditForms] = useState({});
    const documentationCategoryOptions = useMemo(() => {
        const raw = selectedLpj?.execution?.documentation_categories;

        if (Array.isArray(raw)) {
            return raw.map((item) => {
                if (typeof item === 'string') {
                    return { value: item, label: item };
                }

                return {
                    value: item.value ?? item.key ?? item.id ?? '',
                    label: item.label ?? item.name ?? item.value ?? item.key ?? '',
                };
            }).filter((item) => item.value);
        }

        if (raw && typeof raw === 'object') {
            return Object.entries(raw).map(([value, label]) => ({
                value,
                label: String(label),
            }));
        }

        return [
            { value: 'pelaksanaan', label: 'Pelaksanaan' },
            { value: 'briefing', label: 'Briefing' },
            { value: 'lainnya', label: 'Lainnya' },
        ];
    }, [selectedLpj?.execution?.documentation_categories]);

    // KICAP_FIX_DOCUMENTATION_BLANK_CATEGORIES_01
    const [rundownData, setRundownData] = useState({ can_manage_rundown: false, schedules: [] });
    const [rundownLoading, setRundownLoading] = useState(false);
    const [rundownMessage, setRundownMessage] = useState('');
    const [rundownForm, setRundownForm] = useState({
        activity_name: '',
        start_time: '',
        end_time: '',
        responsible_person: '',
        note: '',
    });
    const [rundownStatusForms, setRundownStatusForms] = useState({});
    const [editingSelectionTestResult, setEditingSelectionTestResult] = useState(null);
    const [selectionTestResultForms, setSelectionTestResultForms] = useState({});

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

    const loadSelectionData = () => {
        if (!selectedLpj?.id) {
            return;
        }

        setSelectionLoading(true);
        setSelectionMessage('');

        fetch('/api/app/lpjs/' + selectedLpj.id + '/selection', {
            headers: { Accept: 'application/json' },
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstMessage = Object.values(payload.errors ?? {})?.[0]?.[0];
                    throw new Error(firstMessage ?? payload.message ?? 'Data seleksi tidak bisa dimuat.');
                }

                return payload;
            })
            .then((payload) => {
                const selection = payload.data?.selection ?? null;
                setSelectionData(selection);
                setSelectionRegistrationForms(buildSelectionRegistrationForms(selection?.participants ?? []));
            })
            .catch((error) => {
                setSelectionMessage(error.message ?? 'Data seleksi tidak bisa dimuat.');
            })
            .finally(() => {
                setSelectionLoading(false);
            });
    };

    useEffect(() => {
        if (activeNav === 'operasional' && selectedLpj?.id) {
            loadSelectionData();
        }
    }, [activeNav, selectedLpj?.id]);

    const updateSelectionParticipantForm = (field, value) => {
        setSelectionParticipantForm((current) => ({
            ...current,
            [field]: value,
        }));
    };

    const updateSelectionRegistrationForm = async (participantId, field, value) => {
        if (field === 'photo' && typeof File !== 'undefined' && value instanceof File && typeof normalizeImageUploadFile === 'function') {
            value = await normalizeImageUploadFile(value);
        }

        setSelectionRegistrationForms((current) => ({
            ...current,
            [participantId]: {
                ...(current[participantId] ?? {}),
                [field]: value,
            },
        }));
    };


    const isProgressTestLocked = (participant, result) => {
        const results = participant.test_results ?? [];
        const failedIndex = results.findIndex((item) => item.status === 'gagal');
        const currentIndex = results.findIndex((item) => item.id === result.id);

        return failedIndex >= 0 && currentIndex > failedIndex;
    };

    const progressTestStatusLabel = (status) => {
        if (status === 'gagal') return 'Gugur';
        if (status === 'lulus') return 'Lulus';
        return 'Belum Tes';
    };

    const progressTestStatusClass = (status) => {
        if (status === 'gagal') return 'is-eliminated';
        if (status === 'lulus') return 'is-passed';
        return 'is-pending';
    };

    const openSelectionTestResultEditor = (participant, result) => {
        if (isProgressTestLocked(participant, result)) {
            setSelectionMessage('Peserta sudah gugur, tes berikutnya terkunci.');
            return;
        }

        setEditingSelectionTestResult({
            participantId: participant.id,
            participantName: participant.name,
            resultId: result.id,
            stageName: result.stage_name,
            testName: result.test_name,
        });

        setSelectionTestResultForms((current) => ({
            ...current,
            [result.id]: {
                status: result.status === 'gagal' || result.status === 'lulus' ? result.status : 'belum_tes',
                note: result.note ?? '',
            },
        }));
    };

    const updateSelectionTestResultForm = (resultId, field, value) => {
        setSelectionTestResultForms((current) => ({
            ...current,
            [resultId]: {
                ...(current[resultId] ?? {}),
                [field]: value,
            },
        }));
    };

    const handleSelectionTestResultSubmit = (event) => {
        event.preventDefault();

        if (!selectedLpj?.id || !selectionData?.can_manage_selection || !editingSelectionTestResult) {
            return;
        }

        const form = selectionTestResultForms[editingSelectionTestResult.resultId] ?? {};
        const formData = new FormData();

        formData.append('status', form.status ?? 'belum_tes');

        if (form.note) {
            formData.append('note', form.note);
        }

        setSelectionMessage('Menyimpan progress tes...');

        fetch('/api/app/lpjs/' + selectedLpj.id + '/selection/participants/' + editingSelectionTestResult.participantId + '/test-results/' + editingSelectionTestResult.resultId, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: formData,
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstMessage = Object.values(payload.errors ?? {})?.[0]?.[0];
                    throw new Error(firstMessage ?? payload.message ?? 'Progress tes belum tersimpan.');
                }

                return payload;
            })
            .then((payload) => {
                const selection = payload.data?.selection ?? null;

                setSelectionData(selection);
                setSelectionRegistrationForms(buildSelectionRegistrationForms(selection?.participants ?? []));
                setSelectionMessage('Progress tes tersimpan.');
                setEditingSelectionTestResult(null);
            })
            .catch((error) => {
                setSelectionMessage(error.message ?? 'Progress tes belum tersimpan.');
            });
    };

    const handleCreateSelectionParticipant = (event) => {
        event.preventDefault();

        if (!selectedLpj?.id || !selectionData?.can_manage_selection) {
            return;
        }

        setSelectionMessage('Menyimpan peserta...');

        const formData = new FormData();

        Object.entries(selectionParticipantForm).forEach(([key, value]) => {
            if (value !== null && value !== '') {
                formData.append(key, value);
            }
        });

        fetch('/api/app/lpjs/' + selectedLpj.id + '/selection/participants', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: formData,
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstMessage = Object.values(payload.errors ?? {})?.[0]?.[0];
                    throw new Error(firstMessage ?? payload.message ?? 'Peserta belum tersimpan.');
                }

                return payload;
            })
            .then((payload) => {
                const selection = payload.data?.selection ?? null;
                setSelectionData(selection);
                setSelectionRegistrationForms(buildSelectionRegistrationForms(selection?.participants ?? []));
                setSelectionParticipantForm(emptySelectionParticipantForm);
                setSelectionMessage('Peserta tersimpan.');
            })
            .catch((error) => {
                setSelectionMessage(error.message ?? 'Peserta belum tersimpan.');
            });
    };

    const handleSelectionRegistrationSubmit = (event, participant) => {
        event.preventDefault();

        if (!selectedLpj?.id || !selectionData?.can_manage_selection) {
            return;
        }

        setSelectionMessage('Menyimpan registrasi peserta...');

        const form = selectionRegistrationForms[participant.id] ?? {};
        const formData = new FormData();

        Object.entries(form).forEach(([key, value]) => {
            if (value !== null && value !== '') {
                formData.append(key, value);
            }
        });

        fetch('/api/app/lpjs/' + selectedLpj.id + '/selection/participants/' + participant.id + '/registration', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: formData,
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstMessage = Object.values(payload.errors ?? {})?.[0]?.[0];
                    throw new Error(firstMessage ?? payload.message ?? 'Registrasi belum tersimpan.');
                }

                return payload;
            })
            .then((payload) => {
                const selection = payload.data?.selection ?? null;
                setSelectionData(selection);
                setSelectionRegistrationForms(buildSelectionRegistrationForms(selection?.participants ?? []));
                setSelectionMessage('Registrasi peserta tersimpan.');
                setEditingSelectionParticipantId(null);
            })
            .catch((error) => {
                setSelectionMessage(error.message ?? 'Registrasi belum tersimpan.');
            });
    };

    const loadOperationalSchedules = () => {
        if (!selectedLpj?.id) {
            return;
        }

        setRundownLoading(true);
        setRundownMessage('');

        fetch('/api/app/lpjs/' + selectedLpj.id + '/operational-schedules', {
            headers: { Accept: 'application/json' },
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstMessage = Object.values(payload.errors ?? {})?.[0]?.[0];
                    throw new Error(firstMessage ?? payload.message ?? 'Rundown tidak bisa dimuat.');
                }

                return payload;
            })
            .then((payload) => {
                setRundownData(payload.data ?? { can_manage_rundown: false, schedules: [] });
                setRundownStatusForms((payload.data?.schedules ?? []).reduce((forms, item) => ({ ...forms, [item.id]: { status: item.status ?? 'belum_mulai', status_note: item.status_note ?? '' } }), {}));
            })
            .catch((error) => {
                setRundownMessage(error.message ?? 'Rundown tidak bisa dimuat.');
            })
            .finally(() => {
                setRundownLoading(false);
            });
    };

    useEffect(() => {
        if (activeNav === 'operasional' && selectedLpj?.id) {
            loadOperationalSchedules();
        }
    }, [activeNav, selectedLpj?.id]);

    const updateRundownForm = (field, value) => {
        setRundownForm((current) => ({
            ...current,
            [field]: value,
        }));
    };

    const updateRundownStatusForm = (scheduleId, field, value) => {
        setRundownStatusForms((current) => ({
            ...current,
            [scheduleId]: {
                ...(current[scheduleId] ?? {}),
                [field]: value,
            },
        }));
    };

    const handleUpdateRundownStatus = (event, item) => {
        event.preventDefault();

        if (!selectedLpj?.id || !rundownData?.can_manage_rundown) {
            return;
        }

        setRundownMessage('Menyimpan status rundown...');

        const form = rundownStatusForms[item.id] ?? {};
        const formData = new FormData();

        formData.append('status', form.status ?? item.status ?? 'belum_mulai');

        if (form.status_note) {
            formData.append('status_note', form.status_note);
        }

        fetch('/api/app/lpjs/' + selectedLpj.id + '/operational-schedules/' + item.id + '/status', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: formData,
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstMessage = Object.values(payload.errors ?? {})?.[0]?.[0];
                    throw new Error(firstMessage ?? payload.message ?? 'Status rundown belum tersimpan.');
                }

                return payload;
            })
            .then((payload) => {
                setRundownData(payload.data ?? { can_manage_rundown: true, schedules: [] });
                setRundownStatusForms((payload.data?.schedules ?? []).reduce((forms, item) => ({ ...forms, [item.id]: { status: item.status ?? 'belum_mulai', status_note: item.status_note ?? '' } }), {}));
                setRundownMessage('Status rundown tersimpan.');
            })
            .catch((error) => {
                setRundownMessage(error.message ?? 'Status rundown belum tersimpan.');
            });
    };

    // KICAP_OPERATIONAL_CORRECT_FLOW_01

    const handleCreateRundown = (event) => {
        event.preventDefault();

        if (!selectedLpj?.id || !rundownData?.can_manage_rundown) {
            return;
        }

        setRundownMessage('Menyimpan rundown...');

        const formData = new FormData();

        Object.entries(rundownForm).forEach(([key, value]) => {
            if (value !== null && value !== '') {
                formData.append(key, value);
            }
        });

        fetch('/api/app/lpjs/' + selectedLpj.id + '/operational-schedules', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: formData,
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstMessage = Object.values(payload.errors ?? {})?.[0]?.[0];
                    throw new Error(firstMessage ?? payload.message ?? 'Rundown belum tersimpan.');
                }

                return payload;
            })
            .then((payload) => {
                setRundownData(payload.data ?? { can_manage_rundown: true, schedules: [] });
                setRundownForm({
                    activity_name: '',
                    start_time: '',
                    end_time: '',
                    responsible_person: '',
                    note: '',
                });
                setRundownMessage('Rundown tersimpan.');
            })
            .catch((error) => {
                setRundownMessage(error.message ?? 'Rundown belum tersimpan.');
            });
    };

    // KICAP_SELECTION_RUNDOWN_01

    const reloadSelectedLpjDetail = () => {
        if (!selectedLpj?.id) {
            return Promise.resolve();
        }

        return fetch('/api/app/lpjs/' + selectedLpj.id, {
            headers: { Accept: 'application/json' },
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstMessage = Object.values(payload.errors ?? {})?.[0]?.[0];
                    throw new Error(firstMessage ?? payload.message ?? 'Detail event belum bisa dimuat ulang.');
                }

                return payload;
            })
            .then((payload) => {
                setSelectedLpj(payload.data ?? null);
            });
    };

    const openDocumentationEdit = (item) => {
        setEditingDocumentationId((current) => current === item.id ? null : item.id);
        setDocumentationEditForms((current) => ({
            ...current,
            [item.id]: {
                category: item.category ?? 'pelaksanaan',
                caption: item.caption ?? '',
                include_in_report: item.include_in_report !== false,
                file: null,
            },
        }));
    };

    const updateDocumentationEditForm = async (id, field, value) => {
        if (field === 'file' && typeof File !== 'undefined' && value instanceof File && typeof normalizeImageUploadFile === 'function') {
            value = await normalizeImageUploadFile(value);
        }

        setDocumentationEditForms((current) => ({
            ...current,
            [id]: {
                ...(current[id] ?? {}),
                [field]: value,
            },
        }));
    };

    const handleDocumentationEditSubmit = (event, item) => {
        event.preventDefault();

        if (!selectedLpj?.id) {
            return;
        }

        const form = documentationEditForms[item.id] ?? {};
        const formData = new FormData();

        formData.append('category', form.category ?? item.category ?? 'pelaksanaan');
        formData.append('caption', form.caption ?? '');
        formData.append('include_in_report', form.include_in_report === false ? '0' : '1');

        if (form.file) {
            formData.append('file', form.file);
        }

        setDocumentationMessage('Menyimpan koreksi dokumentasi...');

        fetch('/api/app/lpjs/' + selectedLpj.id + '/documentations/' + item.id + '/update', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: formData,
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstMessage = Object.values(payload.errors ?? {})?.[0]?.[0];
                    throw new Error(firstMessage ?? payload.message ?? 'Koreksi dokumentasi belum tersimpan.');
                }

                return payload;
            })
            .then((payload) => {
                setSelectedLpj((current) => current ? {
                    ...current,
                    execution: payload.data?.execution ?? current.execution,
                } : current);
                setEditingDocumentationId(null);
                setDocumentationMessage('Koreksi dokumentasi tersimpan.');
            })
            .catch((error) => {
                setDocumentationMessage(error.message ?? 'Koreksi dokumentasi belum tersimpan.');
            });
    };

    // KICAP_CLEAN_OPERATIONAL_DOCS_EDIT_01

    const handleDocumentationUpload = async (event) => {
        event.preventDefault();

        if (!selectedLpj?.id || !selectedLpj?.execution?.can_upload_documentation) {
            return;
        }

        setDocumentationMessage('Mengunggah dokumentasi...');

        const form = event.currentTarget;
        const formData = new FormData(form);
        const file = formData.get('file');

        if (file && typeof File !== 'undefined' && file instanceof File && typeof normalizeImageUploadFile === 'function') {
            const compressed = await normalizeImageUploadFile(file);
            formData.set('file', compressed);
        }

        fetch('/api/app/lpjs/' + selectedLpj.id + '/documentations', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: formData,
        })
            .then(async (response) => {
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstMessage = Object.values(payload.errors ?? {})?.[0]?.[0];
                    throw new Error(firstMessage ?? payload.message ?? 'Dokumentasi belum tersimpan.');
                }

                return payload;
            })
            .then(() => reloadSelectedLpjDetail())
            .then(() => {
                form.reset();
                setDocumentationMessage('Dokumentasi tersimpan.');
            })
            .catch((error) => {
                setDocumentationMessage(error.message ?? 'Dokumentasi belum tersimpan.');
            });
    };

    // KICAP_DOCUMENTATION_HISTORY_RESTORE_01

    useEffect(() => {
        try {
            const storedNav = window.sessionStorage.getItem('kicap:pwa:activeNav');
            const storedLpjId = window.sessionStorage.getItem('kicap:pwa:selectedLpjId');

            if (storedLpjId && !selectedLpjId) {
                const parsedId = Number(storedLpjId);

                if (Number.isFinite(parsedId) && parsedId > 0) {
                    setSelectedLpjId(parsedId);
                }
            }

            if (storedNav && storedNav !== activeNav) {
                setActiveNav(storedNav);
            }
        } catch (error) {
            // Abaikan jika sessionStorage tidak tersedia.
        }
    }, []);

    useEffect(() => {
        try {
            window.sessionStorage.setItem('kicap:pwa:activeNav', activeNav);

            if (selectedLpjId) {
                window.sessionStorage.setItem('kicap:pwa:selectedLpjId', String(selectedLpjId));
            } else {
                window.sessionStorage.removeItem('kicap:pwa:selectedLpjId');
            }
        } catch (error) {
            // Abaikan storage error.
        }
    }, [activeNav, selectedLpjId]);

    const refreshCurrentPwaView = () => {
        if (!selectedLpj?.id) {
            return;
        }

        try {
            window.sessionStorage.setItem('kicap:pwa:activeNav', activeNav);
            window.sessionStorage.setItem('kicap:pwa:selectedLpjId', String(selectedLpj.id));
        } catch (error) {
            // Abaikan storage error.
        }

        if (activeNav === 'operasional') {
            if (typeof loadSelectionData === 'function') {
                loadSelectionData();
            }

            if (typeof loadOperationalSchedules === 'function') {
                loadOperationalSchedules();
            }

            return;
        }

        if (
            activeNav === 'dokumentasi'
            || activeNav === 'keuangan'
            || activeNav === 'catatan'
            || activeNav === 'beranda'
        ) {
            if (typeof reloadSelectedLpjDetail === 'function') {
                reloadSelectedLpjDetail();
            }
        }
    };

    // KICAP_NATIVE_REFRESH_KEEP_PAGE_01

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
    const selectionParticipants = useMemo(() => {
        const keyword = selectionSearch.trim().toLowerCase();
        const participants = selectionData?.participants ?? [];

        if (!keyword) {
            return participants;
        }

        return participants.filter((participant) => {
            return [
                participant.name,
                participant.origin,
                participant.participant_number,
                participant.whatsapp,
                participant.registration_status_label,
                participant.selection_status_label,
            ].filter(Boolean).join(' ').toLowerCase().includes(keyword);
        });
    }, [selectionData, selectionSearch]);

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

                            {isDocumentationNav && <section className="documentation-panel-v2">
                                <div className="documentation-hero-v2">
                                    <div>
                                        <p className="section-kicker">Dokumentasi Event</p>
                                        <h2>Riwayat Dokumentasi</h2>
                                        <p></p>
                                    </div>
                                    <button type="button" className="ghost-button" onClick={reloadSelectedLpjDetail}>
                                        Refresh
                                    </button>
                                </div>

                                {documentationMessage && <div className="form-message">{documentationMessage}</div>}

                                {selectedLpj?.execution?.can_upload_documentation && (
                                    <form className="documentation-upload-card-v2" onSubmit={handleDocumentationUpload}>
                                        <div>
                                            <p className="section-kicker">Upload Dokumentasi</p>
                                            <h3>Tambah foto/lampiran kegiatan</h3>
                                        </div>

                                        <label>
                                            Kategori
                                            <select name="category" required defaultValue="pelaksanaan">
                                                {documentationCategoryOptions.map((category) => (
                                                    <option
                                                        key={category.value}
                                                        value={category.value}
                                                    >
                                                        {category.label}
                                                    </option>
                                                ))}
                                            </select>
                                        </label>

                                        <label>
                                            Caption / Keterangan
                                            <textarea name="caption" placeholder="Contoh: Tes fisik lari 2400m" />
                                        </label>

                                        <label className="documentation-file-v2">
                                            <span>Pilih Foto/File</span>
                                            <input name="file" type="file" accept="image/*,.pdf" capture="environment" required />
                                        </label>

                                        <input type="hidden" name="include_in_report" value="1" />

                                        <button type="submit" className="primary-button">Simpan Dokumentasi</button>
                                    </form>
                                )}

                                <div className="documentation-history-grid-v2">
                                    {(selectedLpj?.execution?.documentations ?? []).length === 0 && (
                                        <div className="empty-card">Belum ada dokumentasi pada event ini.</div>
                                    )}

                                    {(selectedLpj?.execution?.documentations ?? []).map((item) => (
                                        <article className="documentation-history-card-v2" key={item.id}>
                                            {item.is_image && item.url ? (
                                                <button
                                                    type="button"
                                                    className="documentation-thumb-v2"
                                                    onClick={() => setGalleryPreview({
                                                        url: item.url,
                                                        title: item.caption || item.original_name || 'Dokumentasi',
                                                    })}
                                                >
                                                    <img src={item.url} alt={item.caption || item.original_name || 'Dokumentasi'} />
                                                </button>
                                            ) : (
                                                <a className="documentation-file-preview-v2" href={item.url} target="_blank" rel="noreferrer">
                                                    Buka File
                                                </a>
                                            )}

                                            <div className="documentation-history-body-v2">
                                                <span>{item.category_label || item.category || 'Dokumentasi'}</span>
                                                <strong>{item.caption || item.original_name || 'Tanpa caption'}</strong>
                                                <small>{item.created_at || item.uploaded_at || ''}</small>
                                                <div className="documentation-actions-v2">
                                                    {item.url && (
                                                        <a href={item.url} target="_blank" rel="noreferrer">
                                                            Lihat
                                                        </a>
                                                    )}
                                                    <button type="button" onClick={() => openDocumentationEdit(item)}>
                                                        {editingDocumentationId === item.id ? 'Tutup' : 'Edit'}
                                                    </button>
                                                </div>

                                                {editingDocumentationId === item.id && (
                                                    <form className="documentation-edit-form-v2" onSubmit={(event) => handleDocumentationEditSubmit(event, item)}>
                                                        <select
                                                            value={documentationEditForms[item.id]?.category ?? item.category ?? 'pelaksanaan'}
                                                            onChange={(event) => updateDocumentationEditForm(item.id, 'category', event.target.value)}
                                                        >
                                                            {documentationCategoryOptions.map((category) => (
                                                                <option key={category.value} value={category.value}>
                                                                    {category.label}
                                                                </option>
                                                            ))}
                                                        </select>

                                                        <textarea
                                                            value={documentationEditForms[item.id]?.caption ?? item.caption ?? ''}
                                                            onChange={(event) => updateDocumentationEditForm(item.id, 'caption', event.target.value)}
                                                            placeholder="Koreksi caption/keterangan"
                                                        />

                                                        <label className="documentation-edit-file-v2">
                                                            Ganti file jika perlu
                                                            <input
                                                                type="file"
                                                                accept="image/*,.pdf"
                                                                onChange={(event) => updateDocumentationEditForm(item.id, 'file', event.target.files?.[0] ?? null)}
                                                            />
                                                        </label>

                                                        <button type="submit">Simpan Koreksi</button>
                                                    </form>
                                                )}
                                            </div>
                                        </article>
                                    ))}
                                </div>
                            </section>}

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

                                                        {isOperationalNav && <section className="selection-panel">
                                <div className="selection-hero">
                                    <div>
                                        <p className="section-kicker">Operasional Seleksi</p>
                                        <h2>Registrasi Peserta</h2>
                                        <p></p>
                                    </div>
                                    <button type="button" className="ghost-button" onClick={loadSelectionData} disabled={selectionLoading}>
                                        {selectionLoading ? 'Memuat...' : 'Refresh'}
                                    </button>
                                </div>

                                {selectionMessage && <div className="form-message">{selectionMessage}</div>}

                                <div className="selection-summary-grid">
                                    <div className="selection-summary-card"><span>Total</span><strong>{selectionData?.summary?.total ?? 0}</strong></div>
                                    <div className="selection-summary-card"><span>Belum Registrasi</span><strong>{selectionData?.summary?.belum_registrasi ?? 0}</strong></div>
                                    <div className="selection-summary-card"><span>Sudah Registrasi</span><strong>{selectionData?.summary?.sudah_registrasi ?? 0}</strong></div>
                                    <div className="selection-summary-card"><span>Masih Lanjut</span><strong>{selectionData?.summary?.aktif ?? 0}</strong></div>
                                    <div className="selection-summary-card"><span>Gugur</span><strong>{selectionData?.summary?.gugur ?? 0}</strong></div>
                                    <div className="selection-summary-card"><span>Lulus</span><strong>{selectionData?.summary?.lulus ?? 0}</strong></div>
                                </div>

                                {false && selectionData?.can_manage_selection && (
                                    <form className="selection-form-card" onSubmit={handleCreateSelectionParticipant}>
                                        <div className="selection-form-title">
                                            <div>
                                                <p className="section-kicker">Peserta Seleksi</p>
                                                <h3>Daftar peserta dari Admin</h3>
                                            </div>
                                        </div>

                                        <label>
                                            Nama Peserta
                                            <input type="text" value={selectionParticipantForm.name} onChange={(event) => updateSelectionParticipantForm('name', event.target.value)} placeholder="Nama lengkap peserta" required />
                                        </label>

                                        <label>
                                            Asal / Sekolah / Kota
                                            <input type="text" value={selectionParticipantForm.origin} onChange={(event) => updateSelectionParticipantForm('origin', event.target.value)} placeholder="Contoh: SMKN 6 Surabaya" />
                                        </label>

                                        <label>
                                            Catatan Awal
                                            <textarea value={selectionParticipantForm.note} onChange={(event) => updateSelectionParticipantForm('note', event.target.value)} placeholder="Catatan perhatian/evaluasi awal jika ada" />
                                        </label>

                                        <button type="submit" className="primary-button">Simpan Peserta</button>
                                    </form>
                                )}

                                <section className="selection-rundown-card">
                                    <div className="selection-form-title">
                                        <div>
                                            <p className="section-kicker">Rundown Event</p>
                                            <h3>Rundown Acara</h3>
                                            <p></p>
                                        </div>
                                        <button type="button" className="ghost-button" onClick={loadOperationalSchedules} disabled={rundownLoading}>
                                            {rundownLoading ? 'Memuat...' : 'Refresh Rundown'}
                                        </button>
                                    </div>

                                    {rundownMessage && <div className="form-message">{rundownMessage}</div>}

                                    {false && rundownData?.can_manage_rundown && (
                                        <form className="rundown-form" onSubmit={handleCreateRundown}>
                                            <label>
                                                Nama Kegiatan
                                                <input
                                                    type="text"
                                                    value={rundownForm.activity_name}
                                                    onChange={(event) => updateRundownForm('activity_name', event.target.value)}
                                                    placeholder="Contoh: Registrasi peserta"
                                                    required
                                                />
                                            </label>

                                            <div className="rundown-two-col">
                                                <label>
                                                    Mulai
                                                    <input
                                                        type="datetime-local"
                                                        value={rundownForm.start_time}
                                                        onChange={(event) => updateRundownForm('start_time', event.target.value)}
                                                    />
                                                </label>

                                                <label>
                                                    Selesai
                                                    <input
                                                        type="datetime-local"
                                                        value={rundownForm.end_time}
                                                        onChange={(event) => updateRundownForm('end_time', event.target.value)}
                                                    />
                                                </label>
                                            </div>

                                            <label>
                                                PIC / Penanggung Jawab
                                                <input
                                                    type="text"
                                                    value={rundownForm.responsible_person}
                                                    onChange={(event) => updateRundownForm('responsible_person', event.target.value)}
                                                    placeholder="Nama petugas/PIC"
                                                />
                                            </label>

                                            <label>
                                                Catatan
                                                <textarea
                                                    value={rundownForm.note}
                                                    onChange={(event) => updateRundownForm('note', event.target.value)}
                                                    placeholder="Catatan rundown jika ada"
                                                />
                                            </label>

                                            <button type="submit" className="primary-button">Simpan Rundown</button>
                                        </form>
                                    )}

                                    <div className="rundown-list">
                                        {(rundownData?.schedules ?? []).length === 0 ? (
                                            <div className="empty-card">Belum ada rundown.</div>
                                        ) : (
                                            (rundownData?.schedules ?? []).map((item) => (
                                                <article className="rundown-item" key={item.id}>
                                                    <div>
                                                        <strong>{item.activity_name}</strong>
                                                        <span>{item.start_time || '-'}{item.end_time ? ' — ' + item.end_time : ''}</span>
                                                        <small>PIC: {item.responsible_person || '-'} · Input: {item.created_by || '-'}</small>
                                                    </div>
                                                    {item.note && <p>{item.note}</p>}
                                                    <div className="rundown-status-pill">{item.status_label ?? 'Belum mulai'}</div>
                                                    {rundownData?.can_manage_rundown && (
                                                        <form className="rundown-status-form" onSubmit={(event) => handleUpdateRundownStatus(event, item)}>
                                                            <select
                                                                value={rundownStatusForms[item.id]?.status ?? item.status ?? 'belum_mulai'}
                                                                onChange={(event) => updateRundownStatusForm(item.id, 'status', event.target.value)}
                                                            >
                                                                <option value="belum_mulai">Belum mulai</option>
                                                                <option value="sedang_berlangsung">Sedang berlangsung</option>
                                                                <option value="selesai">Selesai</option>
                                                                <option value="terkendala">Terkendala</option>
                                                            </select>
                                                            <textarea
                                                                value={rundownStatusForms[item.id]?.status_note ?? ''}
                                                                onChange={(event) => updateRundownStatusForm(item.id, 'status_note', event.target.value)}
                                                                placeholder="Catatan status jika ada"
                                                            />
                                                            <button type="submit" className="ghost-button">Update Status</button>
                                                        </form>
                                                    )}
                                                </article>
                                            ))
                                        )}
                                    </div>
                                </section>

                                <div className="selection-toolbar">
                                    <input type="search" value={selectionSearch} onChange={(event) => setSelectionSearch(event.target.value)} placeholder="Cari nama, no peserta, WhatsApp, asal..." />
                                </div>

                                <div className="selection-list">
                                    {selectionLoading && <div className="empty-card">Memuat data peserta...</div>}

                                    {!selectionLoading && selectionParticipants.length === 0 && (
                                        <div className="empty-card">Belum ada peserta. Admin perlu menambahkan peserta terlebih dulu.</div>
                                    )}

                                    {!selectionLoading && selectionParticipants.map((participant) => {
                                        const form = selectionRegistrationForms[participant.id] ?? {};
                                        const expanded = expandedSelectionParticipantId === participant.id;

                                        return (
                                            <article className="selection-participant-card" key={participant.id}>
                                                <button type="button" className="selection-participant-head" onClick={() => { setExpandedSelectionParticipantId(expanded ? null : participant.id); setEditingSelectionParticipantId(null); setPreviewSelectionPhotoParticipantId(null); }}>
                                                    <div className="participant-photo">
                                                        {participant.photo_url ? <img src={participant.photo_url} alt={participant.name} /> : <span>{participant.name?.slice(0, 1) ?? '?'}</span>}
                                                    </div>
                                                    <div className="participant-main">
                                                        <strong>{participant.name}</strong>
                                                        <span>{participant.participant_number ? ('No. ' + participant.participant_number) : 'Belum ada nomor peserta'}</span>
                                                        <small>{participant.origin || '-'}</small>
                                                    </div>
                                                    <div className="participant-badges participant-status-line">
                                                        <span className={'mini-badge participant-status-pill ' + (participant.selection_status === 'gugur' ? 'is-eliminated' : 'is-active')}>
                                                            {participant.selection_status === 'gugur' ? 'Gugur' : 'Aktif'}
                                                        </span>
                                                    </div>
                                                </button>

                                                {expanded && (
                                                    <div className="selection-participant-body selection-participant-sheet">
                                                        <div className="sheet-grabber" aria-hidden="true" />
                                                        <div className="participant-sheet-header">
                                                            <button
                                                                type="button"
                                                                className={'participant-sheet-photo ' + (participant.photo_url ? 'is-clickable' : 'is-empty')}
                                                                onClick={() => participant.photo_url && setPreviewSelectionPhotoParticipantId(participant.id)}
                                                                disabled={!participant.photo_url}
                                                                aria-label="Lihat foto peserta"
                                                            >
                                                                {participant.photo_url ? <img src={participant.photo_url} alt={participant.name} /> : <span>{participant.name?.slice(0, 1) ?? '?'}</span>}
                                                            </button>
                                                            <div className="participant-sheet-title">
                                                                <p className="section-kicker">Detail Peserta</p>
                                                                <h3>{participant.name}</h3>
                                                                <span>{participant.participant_number ? ('No. ' + participant.participant_number) : 'Belum ada nomor peserta'}</span>
                                                                <small>{participant.origin || '-'}</small>
                                                                <div className="participant-sheet-badges participant-status-line">
                                                                    <span className={'mini-badge participant-status-pill ' + (participant.selection_status === 'gugur' ? 'is-eliminated' : 'is-active')}>
                                                                        {participant.selection_status === 'gugur' ? 'Gugur' : 'Aktif'}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <button type="button" className="sheet-close-button" onClick={() => { setExpandedSelectionParticipantId(null); setEditingSelectionParticipantId(null); setPreviewSelectionPhotoParticipantId(null); setPreviewSelectionPhotoParticipantId(null); }}>
                                                                Tutup
                                                            </button>
                                                        </div>
                                                        {previewSelectionPhotoParticipantId === participant.id && participant.photo_url && (
                                                            <div className="participant-photo-lightbox" role="dialog" aria-modal="true">
                                                                <button type="button" className="photo-lightbox-backdrop" onClick={() => setPreviewSelectionPhotoParticipantId(null)} aria-label="Tutup foto" />
                                                                <div className="photo-lightbox-panel">
                                                                    <button type="button" className="photo-lightbox-close" onClick={() => setPreviewSelectionPhotoParticipantId(null)}>
                                                                        Tutup
                                                                    </button>
                                                                    <img src={participant.photo_url} alt={participant.name} />
                                                                    <strong>{participant.name}</strong>
                                                                </div>
                                                            </div>
                                                        )}
                                                        {editingSelectionParticipantId === participant.id ? (
                                                            <form onSubmit={(event) => handleSelectionRegistrationSubmit(event, participant)}>
                                                            <label>
                                                                Nomor Peserta / Nomor Ujian
                                                                <input type="text" value={form.participant_number ?? ''} onChange={(event) => updateSelectionRegistrationForm(participant.id, 'participant_number', event.target.value)} placeholder="Diisi setelah registrasi onsite" disabled={!selectionData?.can_manage_selection} />
                                                            </label>

                                                            <label>
                                                                Nomor WhatsApp
                                                                <input type="text" value={form.whatsapp ?? ''} onChange={(event) => updateSelectionRegistrationForm(participant.id, 'whatsapp', event.target.value)} placeholder="Contoh: 081234567890" disabled={!selectionData?.can_manage_selection} />
                                                            </label>

                                                            <label>
                                                                Foto Peserta
                                                                <input type="file" accept="image/*" onChange={(event) => updateSelectionRegistrationForm(participant.id, 'photo', event.target.files?.[0] ?? null)} disabled={!selectionData?.can_manage_selection} />
                                                            </label>

                                                            <label>
                                                                Catatan Peserta
                                                                <textarea value={form.note ?? ''} onChange={(event) => updateSelectionRegistrationForm(participant.id, 'note', event.target.value)} placeholder="Catatan perhatian/evaluasi/perbaikan" disabled={!selectionData?.can_manage_selection} />
                                                            </label>

                                                            <div className="selection-meta-grid">
                                                                <div><span>Registrasi Oleh</span><strong>{participant.registered_by || '-'}</strong></div>
                                                                <div><span>WhatsApp</span><strong>{participant.whatsapp || '-'}</strong></div>
                                                            </div>

                                                            {selectionData?.can_manage_selection && <button type="submit" className="primary-button">Simpan Registrasi</button>}
                                                            <button type="button" className="ghost-button" onClick={() => setEditingSelectionParticipantId(null)}>Batal Edit</button>
                                                        </form>
                                                    ) : (
                                                        <div className="participant-detail-preview">
                                                            <div className="participant-detail-grid">
                                                                <div><span>No. Peserta</span><strong>{participant.participant_number || '-'}</strong></div>
                                                                <div><span>Asal</span><strong>{participant.origin || '-'}</strong></div>
                                                                <div><span>WhatsApp</span><strong>{participant.whatsapp || '-'}</strong></div>
                                                                <div><span>Registrasi Oleh</span><strong>{participant.registered_by || '-'}</strong></div>
                                                            </div>
                                                            <div className="participant-detail-note">
                                                                <span>Catatan Peserta</span>
                                                                <p>{participant.note || '-'}</p>
                                                            </div>
                                                            {selectionData?.can_manage_selection && (
                                                                <button type="button" className="primary-button" onClick={() => setEditingSelectionParticipantId(participant.id)}>
                                                                    Edit Data / Upload Foto
                                                                </button>
                                                            )}
                                                        </div>
                                                    )}

                                                        <div className="selection-tests-preview">
                                                            <h4>Progress Tes</h4>
                                                            {(participant.test_results ?? []).length === 0 ? (
                                                                <p>Belum ada item tes.</p>
                                                            ) : (
                                                                <div className="selection-test-list">
                                                                    {(participant.test_results ?? []).map((result) => (
                                                                        <React.Fragment key={result.id}>
                                                                            <button type="button" className={'selection-test-row selection-test-action ' + (isProgressTestLocked(participant, result) ? 'is-locked' : '')} onClick={() => openSelectionTestResultEditor(participant, result)}>
                                                                                <div>
                                                                                    <strong>{result.stage_name}</strong>
                                                                                    <span>{result.test_name}</span>
                                                                                    {result.note && <small>{result.note}</small>}
                                                                                </div>
                                                                                <em className={'test-status-pill ' + progressTestStatusClass(result.status)}>
                                                                                    {progressTestStatusLabel(result.status)}
                                                                                </em>
                                                                            </button>
                                                                            {editingSelectionTestResult?.resultId === result.id && (
                                                                                <div className="selection-test-sheet" role="dialog" aria-modal="true">
                                                                                    <button type="button" className="selection-test-sheet-backdrop" onClick={() => setEditingSelectionTestResult(null)} aria-label="Tutup update tes" />
                                                                                    <form className="selection-test-sheet-panel" onSubmit={handleSelectionTestResultSubmit}>
                                                                                        <div className="sheet-grabber" aria-hidden="true" />
                                                                                        <p className="section-kicker">Update Progress Tes</p>
                                                                                        <h3>{result.test_name}</h3>
                                                                                        <small>{result.stage_name} · {participant.name}</small>

                                                                                        <label>
                                                                                            Status Tes
                                                                                            <select
                                                                                                value={selectionTestResultForms[result.id]?.status ?? result.status ?? 'belum_tes'}
                                                                                                onChange={(event) => updateSelectionTestResultForm(result.id, 'status', event.target.value)}
                                                                                            >
                                                                                                <option value="belum_tes">Belum Tes</option>
                                                                                                <option value="lulus">Lulus</option>
                                                                                                <option value="gagal">Gugur</option>
                                                                                            </select>
                                                                                        </label>

                                                                                        <label>
                                                                                            Catatan
                                                                                            <textarea
                                                                                                value={selectionTestResultForms[result.id]?.note ?? ''}
                                                                                                onChange={(event) => updateSelectionTestResultForm(result.id, 'note', event.target.value)}
                                                                                                placeholder="Catatan hasil tes jika ada"
                                                                                            />
                                                                                        </label>

                                                                                        <button type="submit" className="primary-button">Simpan Progress Tes</button>
                                                                                        <button type="button" className="ghost-button" onClick={() => setEditingSelectionTestResult(null)}>Batal</button>
                                                                                    </form>
                                                                                </div>
                                                                            )}
                                                                        </React.Fragment>
                                                                    ))}
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                )}
                                            </article>
                                        );
                                    })}
                                </div>
                            </section>}

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
