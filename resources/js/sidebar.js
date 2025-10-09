import { createApp } from 'vue';

export default function initSidebar() {
    const app = createApp({
        data() {
            return {
                isSidebarOpen: true,
                isMobile: window.innerWidth < 1024,
                currentPage: localStorage.getItem('currentPage') || 'Dashboard', // load saved page
                pageIcons: {
                    // HOME Section
                    Dashboard: `<i class="fa-solid fa-grip"></i>`,
                    POS: `<i class="mr-1 fa-solid fa-computer"></i>`,

                    // BUSINESS INTELLIGENCE Section
                    "Reports": `<i class="mr-1 fa-solid fa-clipboard-list"></i>`,
                    "Analytics & Forecasting": `<i class="mr-1 fa-solid fa-robot"></i>`,

                    // MANAGEMENT Section
                    "My Hardware": `<i class="mr-1 fa-solid fa-warehouse"></i>`,
                    "My Inventory": `<i class="mr-2 text-xl fa-solid fa-box"></i>`,
                    "My Suppliers": `<i class="mr-1 text-lg fa-solid fa-truck"></i>`,
                    "My Employees": `<i class="mr-1 text-sm fa-solid fa-users"></i>`,
                    "My Customers": `<i class="mr-1 text-sm fa-solid fa-users-line"></i>`,

                    // Bottom
                    Settings: `<i class="mr-1 fa-solid fa-gear"></i>`,
                }
            };
        },
        methods: {
            toggleSidebar() {
                this.isSidebarOpen = !this.isSidebarOpen;
            },
            handleResize() {
                this.isMobile = window.innerWidth < 750;

                if (this.isMobile) {
                    this.isSidebarOpen = false; // auto-close on mobile
                } else {
                    this.isSidebarOpen = true; // always open on desktop
                }
            },
            changePage(pageName) {
                this.currentPage = pageName; // updates navbar title
                localStorage.setItem('currentPage', pageName); // save to storage
            }
        },
        mounted() {
            this.handleResize(); // run once on load
            window.addEventListener('resize', this.handleResize);
        },
        beforeUnmount() {
            window.removeEventListener('resize', this.handleResize);
        }
    });

    app.mount('#sidebar-app'); //mount sa sidebar element
}
