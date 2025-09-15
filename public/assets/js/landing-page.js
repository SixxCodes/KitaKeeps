// =================== FEATURES AND TESTIMONIALS ===================
new Vue({
    el: '#app',
    data: {
        scrolled: false,
        menuOpen: false,
        features: [
            {
                icon: 'fa-solid fa-warehouse',
                title: 'Inventory Management',
                description: 'Track every item in your store with real-time updates and low stock alerts.',
            },
            {
                icon: 'fa-solid fa-person',
                title: 'Customer Management',
                description: 'Record and track customer information, outstanding debts, and credited transactions.'
            },
            {
                icon: 'fas fa-box',
                title: 'Supplier Management',
                description: 'Manage all your suppliers, supplier records and schedules for restocking.',
            },
            {
                icon: 'fa-solid fa-person',
                title: 'Employee Management',
                description: 'Tracks attendance and automates payroll processing.',
            },
            {
                icon: 'fa-solid fa-cloud-arrow-up',
                title: 'Cloud Sharing',
                description: 'For centralized and secure data access across multiple branches.',
            },
            {
                icon: 'fa-solid fa-robot',
                title: 'AI Forecasting',
                description: 'Forecast product demand, track inventory, and compare branch performance.',
            },
        ],

        testimonials: [
            {
                name: 'Mae Rhie Bihisan',
                role: 'Branch Manager',
                company: 'Zyrile Hardware',
                content: 'This system cut our inventory discrepancies by 85% in the first month. It\'s transformed how we manage our stock.',
                rating: 5,
            },
            {
                name: 'Bee and Ohgay',
                role: 'Cashier',
                company: 'Zyrile Hardware',
                content: 'The real-time tracking has eliminated our stockouts. Our customers are happier and our sales have increased.',
                rating: 5,
            },
            {
                name: 'Ken Knee Madayuhh',
                role: 'CEO',
                company: 'Zyrile Hardware',
                content: 'The automated reordering feature saves us 10+ hours per week and ensures we never miss a replenishment.',
                rating: 5,
            },
        ],

        team: [
            {
                image: 'assets/images/team/merry-cat.jpg',
                name: 'Merry Fe A. Guisihan',
                role: 'Database Manager / Developer',
                description: 'Designs, implements, and maintains the system database to ensure smooth inventory management.',
            },
            {
                image: 'assets/images/team/john-cat.jpg',
                name: 'John Allen Latoza',
                role: 'UI/UX Designer',
                description: 'Creates intuitive, user-friendly interfaces and enhances overall user experience.',
            },
            {
                image: 'assets/images/team/kenny-cat.jpg',
                name: 'Zyrile Kenny C. Madayag',
                role: 'Project Manager / Developer',
                description: 'Oversees project development, coordinates the team, and contributes to coding.',
            },
            {
                image: 'assets/images/team/chied-cat.jpg',
                name: 'Chiedyusof Mapuro',
                role: 'UI/UX Designer',
                description: 'Creates intuitive, user-friendly interfaces and enhances overall user experience.',
            },
            {
                image: 'assets/images/team/jancee-cat.jpg',
                name: 'Jancee O. Nabre',
                role: 'Researcher / Documentation',
                description: 'Conducts research, gathers requirements, and documents project processes.',
            },
            {
                image: 'assets/images/team/vien-cat.jpg',
                name: 'Vien Justine E. Ugay',
                role: 'Researcher / Documentation',
                description: 'Conducts research, gathers requirements, and documents project processes.',
            },
        ],
    },
    methods: {
        handleScroll() {
            this.scrolled = window.scrollY > 10;
        },
        toggleMenu() {
            this.menuOpen = !this.menuOpen;
        },
        closeMenu() {
            this.menuOpen = false;
        }
    },
    mounted() {
        window.addEventListener('scroll', this.handleScroll);
        
        // Close menu when clicking on a link
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', this.closeMenu);
        });
    }
});