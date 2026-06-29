<x-filament-panels::page>
    @php
        $record = $this->record;

        $role = (string) data_get($record, 'role', 'user');
        $username = trim((string) data_get($record, 'username', ''));
        $isActive = (bool) data_get($record, 'is_active', true);
        $createdAt = data_get($record, 'created_at');
        $updatedAt = data_get($record, 'updated_at');

        $roleKey = strtolower(trim($role));

        $roleLabel = match ($roleKey) {
            'admin' => 'Admin',
            'director', 'direktur' => 'Direktur',
            'user' => 'Petugas',
            default => $role !== '' ? \Illuminate\Support\Str::headline(str_replace(['_', '-'], ' ', $role)) : '-',
        };

        $statusLabel = $isActive ? 'Aktif' : 'Nonaktif';

        $statusClass = $isActive
            ? 'background: rgba(22, 163, 74, .12); color: rgb(34, 197, 94); border-color: rgba(34, 197, 94, .28);'
            : 'background: rgba(239, 68, 68, .12); color: rgb(248, 113, 113); border-color: rgba(248, 113, 113, .28);';

        $roleClass = match ($roleKey) {
            'admin' => 'background: rgba(245, 158, 11, .12); color: rgb(251, 191, 36); border-color: rgba(251, 191, 36, .28);',
            'director', 'direktur' => 'background: rgba(22, 163, 74, .12); color: rgb(34, 197, 94); border-color: rgba(34, 197, 94, .28);',
            'user' => 'background: rgba(59, 130, 246, .12); color: rgb(96, 165, 250); border-color: rgba(96, 165, 250, .28);',
            default => 'background: rgba(148, 163, 184, .12); color: rgb(148, 163, 184); border-color: rgba(148, 163, 184, .28);',
        };

        $avatarUrl = $record->admin_avatar_preview_url;
    @endphp

    <div class="kicap-user-preview">
        <style>
            .kicap-user-preview {
                display: grid;
                gap: 1rem;
            }

            .kicap-user-preview .hero {
                display: grid;
                grid-template-columns: 112px minmax(0, 1fr);
                gap: 1.2rem;
                align-items: center;
                padding: 1.25rem;
                border: 1px solid rgba(148, 163, 184, .22);
                border-radius: 20px;
                background: linear-gradient(135deg, rgba(15, 23, 42, .04), rgba(148, 163, 184, .05));
            }

            .dark .kicap-user-preview .hero {
                background: linear-gradient(135deg, rgba(255, 255, 255, .05), rgba(148, 163, 184, .06));
                border-color: rgba(255, 255, 255, .10);
            }

            .kicap-user-preview .avatar {
                width: 96px;
                height: 96px;
                border-radius: 999px;
                object-fit: cover;
                background: rgba(148, 163, 184, .18);
                border: 4px solid rgba(255, 255, 255, .75);
                box-shadow: 0 16px 34px rgba(15, 23, 42, .18);
            }

            .dark .kicap-user-preview .avatar {
                border-color: rgba(255, 255, 255, .12);
            }

            .kicap-user-preview .name {
                margin: 0;
                font-size: clamp(1.55rem, 2.2vw, 2.2rem);
                font-weight: 800;
                letter-spacing: -.04em;
                line-height: 1.05;
            }

            .kicap-user-preview .email {
                margin-top: .45rem;
                color: rgb(100, 116, 139);
                font-size: .96rem;
            }

            .dark .kicap-user-preview .email {
                color: rgb(148, 163, 184);
            }

            .kicap-user-preview .badges {
                display: flex;
                flex-wrap: wrap;
                gap: .45rem;
                margin-top: .85rem;
            }

            .kicap-user-preview .badge {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                padding: .32rem .65rem;
                border-radius: 999px;
                border: 1px solid;
                font-size: .78rem;
                font-weight: 750;
            }

            .kicap-user-preview .grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem;
            }

            .kicap-user-preview .card {
                padding: 1rem;
                border: 1px solid rgba(148, 163, 184, .22);
                border-radius: 18px;
                background: rgba(255, 255, 255, .72);
            }

            .dark .kicap-user-preview .card {
                background: rgba(255, 255, 255, .035);
                border-color: rgba(255, 255, 255, .10);
            }

            .kicap-user-preview .card-title {
                margin: 0 0 .75rem;
                font-size: .9rem;
                color: rgb(100, 116, 139);
                font-weight: 800;
                letter-spacing: .02em;
                text-transform: uppercase;
            }

            .dark .kicap-user-preview .card-title {
                color: rgb(148, 163, 184);
            }

            .kicap-user-preview .rows {
                display: grid;
                gap: .7rem;
            }

            .kicap-user-preview .row {
                display: grid;
                grid-template-columns: 150px minmax(0, 1fr);
                gap: .9rem;
                align-items: start;
                padding-bottom: .7rem;
                border-bottom: 1px solid rgba(148, 163, 184, .16);
            }

            .kicap-user-preview .row:last-child {
                padding-bottom: 0;
                border-bottom: 0;
            }

            .kicap-user-preview .label {
                color: rgb(100, 116, 139);
                font-size: .86rem;
                font-weight: 650;
            }

            .dark .kicap-user-preview .label {
                color: rgb(148, 163, 184);
            }

            .kicap-user-preview .value {
                font-weight: 650;
                word-break: break-word;
            }

            @media (max-width: 760px) {
                .kicap-user-preview .hero {
                    grid-template-columns: 1fr;
                    text-align: center;
                    justify-items: center;
                }

                .kicap-user-preview .badges {
                    justify-content: center;
                }

                .kicap-user-preview .grid {
                    grid-template-columns: 1fr;
                }

                .kicap-user-preview .row {
                    grid-template-columns: 1fr;
                    gap: .2rem;
                }
            }
        </style>

        <section class="hero">
            <img class="avatar" src="{{ $avatarUrl }}" alt="Avatar {{ $record->name }}" loading="lazy">

            <div>
                <h2 class="name">{{ $record->name }}</h2>
                <div class="email">{{ $record->email }}</div>

                <div class="badges">
                    <span class="badge" style="{{ $roleClass }}">{{ $roleLabel }}</span>
                    <span class="badge" style="{{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
            </div>
        </section>

        <div class="grid">
            <section class="card">
                <h3 class="card-title">Informasi User</h3>

                <div class="rows">
                    <div class="row">
                        <div class="label">Nama</div>
                        <div class="value">{{ $record->name ?: '-' }}</div>
                    </div>

                    <div class="row">
                        <div class="label">Username</div>
                        <div class="value">{{ $username !== '' ? '@' . $username : '-' }}</div>
                    </div>

                    <div class="row">
                        <div class="label">Email</div>
                        <div class="value">{{ $record->email ?: '-' }}</div>
                    </div>

                    <div class="row">
                        <div class="label">Role</div>
                        <div class="value">{{ $roleLabel }}</div>
                    </div>
                </div>
            </section>

            <section class="card">
                <h3 class="card-title">Aktivitas Akun</h3>

                <div class="rows">
                    <div class="row">
                        <div class="label">Status Akun</div>
                        <div class="value">{{ $statusLabel }}</div>
                    </div>

                    <div class="row">
                        <div class="label">Dibuat</div>
                        <div class="value">
                            {{ $createdAt ? $createdAt->timezone(config('app.timezone'))->format('d M Y H:i') : '-' }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="label">Update Terakhir</div>
                        <div class="value">
                            {{ $updatedAt ? $updatedAt->timezone(config('app.timezone'))->format('d M Y H:i') : '-' }}
                        </div>
                    </div>

                    <div class="row">
                        <div class="label">Sumber Avatar</div>
                        <div class="value">{{ $record->profile_photo_path ? strtoupper($record->profile_photo_disk ?: 'public') : 'Fallback sistem' }}</div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
