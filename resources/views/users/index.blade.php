@extends('layouts.master')

@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <div x-data="userList">
            <script src="{{ asset('assets/js/simple-datatables.js') }}"></script>

            <div class="panel border-[#e0e6ed] px-0 dark:border-[#1b2e4b]">
                <div class="flex items-center justify-between px-5 mb-5">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                        👥 Manajemen User
                    </h2>
                    <a href="{{ route('users.create') }}"
                        class="btn btn-primary flex items-center gap-2 px-4 py-2 rounded-lg shadow-sm hover:shadow-md transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Tambah User
                    </a>
                </div>

                <div class="invoice-table px-5 pb-5">
                    <table id="userTable" class="whitespace-nowrap w-full"></table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userList', () => ({
                users: @json($users),
                datatable: null,
                dataArr: [],

                init() {
                    this.prepareData();
                    this.initTable();
                },

                prepareData() {
                    this.dataArr = this.users.map(u => [
                        u.name ?? '-',
                        u.email ?? '-',
                        u.role_name ?? '-',
                        u.status ?? '-',
                        (u.positions || []).map(p => p.name).join(', ') || '-',
                        u.id
                    ]);
                },

                initTable() {
                    this.datatable = new simpleDatatables.DataTable('#userTable', {
                        data: {
                            headings: [
                                'Nama',
                                'Email',
                                'Role',
                                'Status',
                                'Jabatan',
                                'Aksi'
                            ],
                            data: this.dataArr
                        },
                        columns: [{
                                select: 3,
                                render: (data) => {
                                    const color = data === 'Aktif' ? 'success' :
                                        'danger';
                                    return `<span class="badge badge-outline-${color}">${data}</span>`;
                                }
                            },
                            {
                                select: 5,
                                sortable: false,
                                render: (data) => `
                    <div class="flex items-center justify-center gap-1">
                        <a href="/users/${data}/edit" class="btn btn-sm btn-outline-warning" title="Edit User">✏️ Edit</a>
                    </div>
                `
                            }
                        ],
                    });
                }

            }));
        });
    </script>
@endsection
