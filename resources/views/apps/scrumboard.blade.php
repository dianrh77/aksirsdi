@extends('layouts.master')
@section('content')
    <div class="animate__animated p-6" :class="[$store.app.animation]">
        <!-- start main content section -->
        <div x-data="scrumboard">
            <div>
                <button type="button" class="btn btn-primary flex" @click="addEditProject()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 ltr:mr-3 rtl:ml-3">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add Project
                </button>
            </div>

            <!-- project list -->
            <div class="relative pt-5">
                <div class="perfect-scrollbar -mx-2 h-full">
                    <div class="flex flex-nowrap items-start gap-5 overflow-x-auto px-2 pb-2">
                        <template x-for="project in projectList" :key="project.id">
                            <div class="panel w-80 flex-none">
                                <div class="mb-5 flex justify-between">
                                    <h4 x-text="project.title" class="text-base font-semibold"></h4>

                                    <div class="flex items-center">
                                        <button type="button" class="hover:text-primary ltr:mr-2 rtl:ml-2" @click="addEditTask(project.id)">
                                            <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5">
                                                <circle opacity="0.5" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"></circle>
                                                <path d="M15 12L12 12M12 12L9 12M12 12L12 9M12 12L12 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                            </svg>
                                        </button>
                                        <div x-data="dropdown" @click.outside="open = false" class="dropdown">
                                            <button type="button" class="hover:text-primary" @click="toggle">
                                                <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70 hover:opacity-100">
                                                    <circle cx="5" cy="12" r="2" stroke="currentColor" stroke-width="1.5"></circle>
                                                    <circle opacity="0.5" cx="12" cy="12" r="2" stroke="currentColor" stroke-width="1.5"></circle>
                                                    <circle cx="19" cy="12" r="2" stroke="currentColor" stroke-width="1.5"></circle>
                                                </svg>
                                            </button>
                                            <ul x-cloak="" x-show="open" x-transition="" x-transition.duration.300ms="" class="ltr:right-0 rtl:left-0">
                                                <li><a href="javascript:;" @click="toggle, addEditProject(project)">Edit</a></li>
                                                <li><a href="javascript:;" @click="toggle, deleteProject(project)">Delete</a></li>
                                                <li><a href="javascript:;" @click="toggle, clearProjects(project)">Clear All</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- task list -->
                                <div class="sortable-list min-h-[150px]" :data-id="project.id">
                                    <template x-for="task in project.tasks">
                                        <div :key="project.id + '' + task.id" :data-id="project.id + '' + task.id" class="mb-5 cursor-move space-y-3 rounded-md bg-[#f4f4f4] p-3 pb-5 shadow dark:bg-[#262e40]">
                                            <template x-if="task.image">
                                                <img src="{{ asset('assets/images/carousel1.jpeg') }}" alt="images" class="h-32 w-full rounded-md object-cover">
                                            </template>
                                            <div class="text-base font-medium" x-text="task.title"></div>
                                            <p class="break-all" x-text="task.description"></p>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <template x-if="task.tags?.length">
                                                    <template x-for="(tag , i) in task.tags" :key="i">
                                                        <div class="btn btn-outline-primary flex px-2 py-1">
                                                            <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0">
                                                                <path d="M4.72848 16.1369C3.18295 14.5914 2.41018 13.8186 2.12264 12.816C1.83509 11.8134 2.08083 10.7485 2.57231 8.61875L2.85574 7.39057C3.26922 5.59881 3.47597 4.70292 4.08944 4.08944C4.70292 3.47597 5.59881 3.26922 7.39057 2.85574L8.61875 2.57231C10.7485 2.08083 11.8134 1.83509 12.816 2.12264C13.8186 2.41018 14.5914 3.18295 16.1369 4.72848L17.9665 6.55812C20.6555 9.24711 22 10.5916 22 12.2623C22 13.933 20.6555 15.2775 17.9665 17.9665C15.2775 20.6555 13.933 22 12.2623 22C10.5916 22 9.24711 20.6555 6.55812 17.9665L4.72848 16.1369Z" stroke="currentColor" stroke-width="1.5"></path>
                                                                <circle opacity="0.5" cx="8.60699" cy="8.87891" r="2" transform="rotate(-45 8.60699 8.87891)" stroke="currentColor" stroke-width="1.5"></circle>
                                                                <path opacity="0.5" d="M11.5417 18.5L18.5208 11.5208" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                                            </svg>
                                                            <span class="ltr:ml-2 rtl:mr-2" x-text="tag"></span>
                                                        </div>
                                                    </template>
                                                </template>
                                                <template x-if="!task.tags?.length">
                                                    <div class="btn flex px-2 py-1 text-white-dark shadow-none dark:border-white-dark/50">
                                                        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0">
                                                            <path d="M4.72848 16.1369C3.18295 14.5914 2.41018 13.8186 2.12264 12.816C1.83509 11.8134 2.08083 10.7485 2.57231 8.61875L2.85574 7.39057C3.26922 5.59881 3.47597 4.70292 4.08944 4.08944C4.70292 3.47597 5.59881 3.26922 7.39057 2.85574L8.61875 2.57231C10.7485 2.08083 11.8134 1.83509 12.816 2.12264C13.8186 2.41018 14.5914 3.18295 16.1369 4.72848L17.9665 6.55812C20.6555 9.24711 22 10.5916 22 12.2623C22 13.933 20.6555 15.2775 17.9665 17.9665C15.2775 20.6555 13.933 22 12.2623 22C10.5916 22 9.24711 20.6555 6.55812 17.9665L4.72848 16.1369Z" stroke="currentColor" stroke-width="1.5"></path>
                                                            <circle opacity="0.5" cx="8.60699" cy="8.87891" r="2" transform="rotate(-45 8.60699 8.87891)" stroke="currentColor" stroke-width="1.5"></circle>
                                                            <path opacity="0.5" d="M11.5417 18.5L18.5208 11.5208" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                                        </svg>
                                                        <span class="ltr:ml-2 rtl:mr-2">No Tags</span>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center font-medium hover:text-primary">
                                                    <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 ltr:mr-3 rtl:ml-3">
                                                        <path d="M2 12C2 8.22876 2 6.34315 3.17157 5.17157C4.34315 4 6.22876 4 10 4H14C17.7712 4 19.6569 4 20.8284 5.17157C22 6.34315 22 8.22876 22 12V14C22 17.7712 22 19.6569 20.8284 20.8284C19.6569 22 17.7712 22 14 22H10C6.22876 22 4.34315 22 3.17157 20.8284C2 19.6569 2 17.7712 2 14V12Z" stroke="currentColor" stroke-width="1.5"></path>
                                                        <path opacity="0.5" d="M7 4V2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                                        <path opacity="0.5" d="M17 4V2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                                        <path opacity="0.5" d="M2 9H22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                                    </svg>
                                                    <span x-text="task.date"></span>
                                                </div>
                                                <div class="flex items-center">
                                                    <button type="button" class="hover:text-info" @click="addEditTask(project.id, task)">
                                                        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ltr:mr-3 rtl:ml-3">
                                                            <path opacity="0.5" d="M22 10.5V12C22 16.714 22 19.0711 20.5355 20.5355C19.0711 22 16.714 22 12 22C7.28595 22 4.92893 22 3.46447 20.5355C2 19.0711 2 16.714 2 12C2 7.28595 2 4.92893 3.46447 3.46447C4.92893 2 7.28595 2 12 2H13.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                                            <path d="M17.3009 2.80624L16.652 3.45506L10.6872 9.41993C10.2832 9.82394 10.0812 10.0259 9.90743 10.2487C9.70249 10.5114 9.52679 10.7957 9.38344 11.0965C9.26191 11.3515 9.17157 11.6225 8.99089 12.1646L8.41242 13.9L8.03811 15.0229C7.9492 15.2897 8.01862 15.5837 8.21744 15.7826C8.41626 15.9814 8.71035 16.0508 8.97709 15.9619L10.1 15.5876L11.8354 15.0091C12.3775 14.8284 12.6485 14.7381 12.9035 14.6166C13.2043 14.4732 13.4886 14.2975 13.7513 14.0926C13.9741 13.9188 14.1761 13.7168 14.5801 13.3128L20.5449 7.34795L21.1938 6.69914C22.2687 5.62415 22.2687 3.88124 21.1938 2.80624C20.1188 1.73125 18.3759 1.73125 17.3009 2.80624Z" stroke="currentColor" stroke-width="1.5"></path>
                                                            <path opacity="0.5" d="M16.6522 3.45508C16.6522 3.45508 16.7333 4.83381 17.9499 6.05034C19.1664 7.26687 20.5451 7.34797 20.5451 7.34797M10.1002 15.5876L8.4126 13.9" stroke="currentColor" stroke-width="1.5"></path>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="hover:text-danger" @click="deleteConfirmModal(project.id, task)">
                                                        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5">
                                                            <path opacity="0.5" d="M9.17065 4C9.58249 2.83481 10.6937 2 11.9999 2C13.3062 2 14.4174 2.83481 14.8292 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                                            <path d="M20.5001 6H3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                                            <path d="M18.8334 8.5L18.3735 15.3991C18.1965 18.054 18.108 19.3815 17.243 20.1907C16.378 21 15.0476 21 12.3868 21H11.6134C8.9526 21 7.6222 21 6.75719 20.1907C5.89218 19.3815 5.80368 18.054 5.62669 15.3991L5.16675 8.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                                            <path opacity="0.5" d="M9.5 11L10 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                                            <path opacity="0.5" d="M14.5 11L14 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div class="pt-3">
                                    <button type="button" class="btn btn-primary mx-auto" @click="addEditTask(project.id)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        Add Task
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <!-- add project modal -->
            <div class="fixed inset-0 z-[999] hidden overflow-y-auto bg-[black]/60 px-4" :class="isAddProjectModal && '!block'">
                <div class="flex min-h-screen items-center justify-center">
                    <div x-show="isAddProjectModal" x-transition="" x-transition.duration.300="" @click.outside="isAddProjectModal = false" class="panel my-8 w-[90%] max-w-lg overflow-hidden rounded-lg border-0 p-0 md:w-full">
                        <button type="button" class="absolute top-4 text-white-dark hover:text-dark ltr:right-4 rtl:left-4" @click="isAddProjectModal = false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                        <div class="bg-[#fbfbfb] py-3 text-lg font-medium ltr:pl-5 ltr:pr-[50px] rtl:pr-5 rtl:pl-[50px] dark:bg-[#121c2c]" x-text="params.id ? 'Edit Project' : 'Add Project'"></div>
                        <div class="p-5">
                            <form @submit.prevent="saveProject">
                                <div class="grid gap-5">
                                    <div>
                                        <label for="title">Name</label>
                                        <input id="title" x-model="params.title" type="text" class="form-input mt-1" placeholder="Enter Name">
                                    </div>
                                </div>

                                <div class="mt-8 flex items-center justify-end">
                                    <button type="button" class="btn btn-outline-danger" @click="isAddProjectModal = false">Cancel</button>
                                    <button type="submit" class="btn btn-primary ltr:ml-4 rtl:mr-4" x-text="params.id ? 'Update' : 'Add'"></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- add task modal -->
            <div class="fixed inset-0 z-[999] hidden overflow-y-auto bg-[black]/60" :class="isAddTaskModal && '!block'">
                <div class="flex min-h-screen items-center justify-center px-4" @click.self="isAddTaskModal = false">
                    <div x-show="isAddTaskModal" x-transition="" x-transition.duration.300="" class="panel my-8 w-[90%] max-w-lg overflow-hidden rounded-lg border-0 p-0 md:w-full">
                        <button type="button" class="absolute top-4 text-white-dark hover:text-dark ltr:right-4 rtl:left-4" @click="isAddTaskModal = false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                        <div x-text="paramsTask.id ? 'Edit Task' : 'Add Task'" class="bg-[#fbfbfb] py-3 text-lg font-medium ltr:pl-5 ltr:pr-[50px] rtl:pr-5 rtl:pl-[50px] dark:bg-[#121c2c]"></div>
                        <div class="p-5">
                            <form @submit.prevent="saveTask">
                                <div class="grid gap-5">
                                    <div>
                                        <label for="taskTitle">Name</label>
                                        <input id="taskTitle" x-model="paramsTask.title" type="text" class="form-input" placeholder="Enter Name">
                                    </div>

                                    <div>
                                        <label for="taskTag">Tag</label>
                                        <input id="taskTag" x-model="paramsTask.tags" type="text" class="form-input" placeholder="Enter Tag">
                                    </div>
                                    <div>
                                        <label for="taskdesc">Description</label>
                                        <textarea id="taskdesc" x-model="paramsTask.description" class="form-textarea min-h-[130px]" placeholder="Enter Description"></textarea>
                                    </div>
                                </div>

                                <div class="mt-8 flex items-center justify-end">
                                    <button type="button" class="btn btn-outline-danger" @click="isAddTaskModal = false">Cancel</button>
                                    <button type="submit" class="btn btn-primary ltr:ml-4 rtl:mr-4" x-text="paramsTask.id ? 'Update' : 'Add'"></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- delete task modal -->
            <div class="fixed inset-0 z-[999] hidden overflow-y-auto bg-[black]/60" :class="isDeleteModal && '!block'">
                <div class="flex min-h-screen items-center justify-center px-4" @click.self="isDeleteModal = false">
                    <div x-show="isDeleteModal" x-transition="" x-transition.duration.300="" class="panel my-8 w-[90%] max-w-lg overflow-hidden rounded-lg border-0 p-0 md:w-full">
                        <button type="button" class="absolute top-4 text-white-dark ltr:right-4 rtl:left-4" @click="isDeleteModal = false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                        <div class="bg-[#fbfbfb] py-3 text-lg font-medium ltr:pl-5 ltr:pr-[50px] rtl:pr-5 rtl:pl-[50px] dark:bg-[#121c2c]">
                            Delete Task
                        </div>
                        <div class="p-5 text-center">
                            <div class="mx-auto w-fit rounded-full bg-danger p-4 text-white ring-4 ring-danger/30">
                                <svg width="24" height="24" viewbox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5">
                                    <path opacity="0.5" d="M9.17065 4C9.58249 2.83481 10.6937 2 11.9999 2C13.3062 2 14.4174 2.83481 14.8292 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    <path d="M20.5001 6H3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    <path d="M18.8334 8.5L18.3735 15.3991C18.1965 18.054 18.108 19.3815 17.243 20.1907C16.378 21 15.0476 21 12.3868 21H11.6134C8.9526 21 7.6222 21 6.75719 20.1907C5.89218 19.3815 5.80368 18.054 5.62669 15.3991L5.16675 8.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    <path opacity="0.5" d="M9.5 11L10 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    <path opacity="0.5" d="M14.5 11L14 16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                </svg>
                            </div>
                            <div class="mx-auto mt-5 text-base sm:w-3/4">Are you sure you want to delete Task?</div>

                            <div class="mt-8 flex items-center justify-center">
                                <button type="button" class="btn btn-outline-danger" @click="isDeleteModal = false">Cancel</button>
                                <button type="button" class="btn btn-primary ltr:ml-4 rtl:mr-4" @click="deleteTask">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end main content section -->
    </div>
    @section('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                //scrumboard
                Alpine.data('scrumboard', () => ({
                    params: {
                        id: null,
                        title: '',
                    },
                    paramsTask: {
                        projectId: null,
                        id: null,
                        title: '',
                        description: '',
                        tags: '',
                    },
                    selectedTask: null,
                    isAddProjectModal: false,
                    isAddTaskModal: false,
                    isDeleteModal: false,
                    projectList: [
                        {
                            id: 1,
                            title: 'In Progress',
                            tasks: [
                                {
                                    projectId: 1,
                                    id: 1,
                                    title: 'Creating a new Portfolio on Dribble',
                                    description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.',
                                    image: true,
                                    date: ' 08 Aug, 2020',
                                    tags: ['designing'],
                                },
                                {
                                    projectId: 1,
                                    id: 2,
                                    title: 'Singapore Team Meet',
                                    description: 'Lorem ipsum dolor sit amet, consectetur adipisicing elit.',
                                    date: ' 09 Aug, 2020',
                                    tags: ['meeting'],
                                },
                            ],
                        },
                        {
                            id: 2,
                            title: 'Pending',
                            tasks: [
                                {
                                    projectId: 2,
                                    id: 1,
                                    title: 'Plan a trip to another country',
                                    description: '',
                                    date: ' 10 Sep, 2020',
                                },
                            ],
                        },
                        {
                            id: 3,
                            title: 'Complete',
                            tasks: [
                                {
                                    projectId: 3,
                                    id: 1,
                                    title: 'Dinner with Kelly Young',
                                    description: '',
                                    date: ' 08 Aug, 2020',
                                },
                                {
                                    projectId: 3,
                                    id: 2,
                                    title: 'Launch New SEO Wordpress Theme ',
                                    description:
                                        'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                                    date: ' 09 Aug, 2020',
                                },
                            ],
                        },
                        {
                            id: 4,
                            title: 'Working',
                            tasks: [],
                        },
                    ],

                    init() {
                        this.initializeSortable();
                        // this.$watch('projectList', value => {
                        //     this.initializeSortable();
                        // });
                    },

                    initializeSortable() {
                        setTimeout(() => {
                            //sortable js
                            const sortable = document.querySelectorAll('.sortable-list');
                            for (let i = 0; i < sortable.length; i++) {
                                Sortable.create(sortable[i], {
                                    animation: 200,
                                    group: 'name',
                                    ghostClass: 'sortable-ghost',
                                    dragClass: 'sortable-drag',
                                });
                            }
                        });
                    },

                    addEditProject(project) {
                        setTimeout(() => {
                            this.params = {
                                id: null,
                                title: '',
                            };
                            if (project) {
                                this.params = JSON.parse(JSON.stringify(project));
                            }

                            this.isAddProjectModal = true;
                        });
                    },

                    saveProject() {
                        if (!this.params.title) {
                            this.showMessage('Title is required.', 'error');
                            return false;
                        }

                        if (this.params.id) {
                            //update project
                            const project = this.projectList.find((d) => d.id === this.params.id);
                            project.title = this.params.title;
                        } else {
                            //add project
                            const lastId = this.projectList.length
                                ? this.projectList.reduce((max, obj) => (obj.id > max ? obj.id : max), this.projectList[0].id)
                                : 0;

                            const project = {
                                id: lastId + 1,
                                title: this.params.title,
                                tasks: [],
                            };
                            this.projectList.push(project);
                        }

                        this.initializeSortable();
                        this.showMessage('Project has been saved successfully.');
                        this.isAddProjectModal = false;
                    },

                    deleteProject(project) {
                        this.projectList = this.projectList.filter((d) => d.id != project.id);
                        this.showMessage('Project has been deleted successfully.');
                    },

                    clearProjects(project) {
                        project.tasks = [];
                    },

                    // task
                    addEditTask(projectId, task) {
                        this.paramsTask = {
                            projectId: null,
                            id: null,
                            title: '',
                            description: '',
                            tags: '',
                        };
                        if (task) {
                            this.paramsTask = JSON.parse(JSON.stringify(task));
                            this.paramsTask.tags = this.paramsTask.tags ? this.paramsTask.tags.toString() : '';
                        }
                        this.paramsTask.projectId = projectId;
                        this.isAddTaskModal = true;
                    },

                    saveTask() {
                        if (!this.paramsTask.title) {
                            this.showMessage('Title is required.', 'error');
                            return false;
                        }

                        const project = this.projectList.find((d) => d.id === this.paramsTask.projectId);
                        if (this.paramsTask.id) {
                            //update task
                            const task = project.tasks.find((d) => d.id === this.paramsTask.id);
                            task.title = this.paramsTask.title;
                            task.description = this.paramsTask.description;
                            task.tags = this.paramsTask.tags?.length > 0 ? this.paramsTask.tags.split(',') : [];
                        } else {
                            //add task
                            let maxid = 0;
                            if (project.tasks?.length) {
                                maxid = project.tasks.reduce((max, obj) => (obj.id > max ? obj.id : max), project.tasks[0].id);
                            }

                            const today = new Date();
                            const dd = String(today.getDate()).padStart(2, '0');
                            const mm = String(today.getMonth()); //January is 0!
                            const yyyy = today.getFullYear();
                            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                            const task = {
                                projectId: this.paramsTask.projectId,
                                id: maxid + 1,
                                title: this.paramsTask.title,
                                description: this.paramsTask.description,
                                date: dd + ' ' + monthNames[mm] + ', ' + yyyy,
                                tags: this.paramsTask.tags?.length > 0 ? this.paramsTask.tags.split(',') : [],
                            };

                            project.tasks.push(task);
                        }

                        this.showMessage('Task has been saved successfully.');
                        this.isAddTaskModal = false;
                    },

                    deleteConfirmModal(projectId, task) {
                        this.selectedTask = task;
                        setTimeout(() => {
                            this.isDeleteModal = true;
                        }, 10);
                    },

                    deleteTask() {
                        let project = this.projectList.find((d) => d.id === this.selectedTask.projectId);
                        project.tasks = project.tasks.filter((d) => d.id != this.selectedTask.id);

                        this.showMessage('Task has been deleted successfully.');
                        this.isDeleteModal = false;
                    },

                    showMessage(msg = '', type = 'success') {
                        const toast = window.Swal.mixin({
                            toast: true,
                            position: 'top',
                            showConfirmButton: false,
                            timer: 3000,
                        });
                        toast.fire({
                            icon: type,
                            title: msg,
                            padding: '10px 20px',
                        });
                    },
                }));
            });
        </script>
    @endsection
@endsection