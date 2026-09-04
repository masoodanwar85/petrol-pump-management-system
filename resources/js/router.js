import { createRouter, createWebHistory } from 'vue-router';
import { isLoggedIn } from './auth';
import LoginView from './views/LoginView.vue';
import DashboardView from './views/DashboardView.vue';
import ShiftView from './views/ShiftView.vue';
import TanksView from './views/TanksView.vue';
import TankDetailView from './views/TankDetailView.vue';
import RatesView from './views/RatesView.vue';
import CustomersView from './views/CustomersView.vue';
import CustomerDetailView from './views/CustomerDetailView.vue';
import ProductsView from './views/ProductsView.vue';
import ExpensesView from './views/ExpensesView.vue';
import ReportsView from './views/ReportsView.vue';
import AuditView from './views/AuditView.vue';
import MoreView from './views/MoreView.vue';

const router = createRouter({
    history: createWebHistory('/admin'),
    routes: [
        { path: '/login', name: 'login', component: LoginView, meta: { guest: true } },
        { path: '/', name: 'dashboard', component: DashboardView, meta: { auth: true, title: 'Dashboard' } },
        { path: '/shift', name: 'shift', component: ShiftView, meta: { auth: true, title: 'Shift' } },
        { path: '/tanks', name: 'tanks', component: TanksView, meta: { auth: true, title: 'Tanks' } },
        { path: '/tanks/:id', name: 'tank-detail', component: TankDetailView, meta: { auth: true, title: 'Tank' } },
        { path: '/rates', name: 'rates', component: RatesView, meta: { auth: true, title: 'Fuel rates' } },
        { path: '/customers', name: 'customers', component: CustomersView, meta: { auth: true, title: 'Customers' } },
        { path: '/customers/:id', name: 'customer-detail', component: CustomerDetailView, meta: { auth: true, title: 'Customer' } },
        { path: '/products', name: 'products', component: ProductsView, meta: { auth: true, title: 'Products' } },
        { path: '/expenses', name: 'expenses', component: ExpensesView, meta: { auth: true, title: 'Expenses' } },
        { path: '/reports', name: 'reports', component: ReportsView, meta: { auth: true, title: 'Reports' } },
        { path: '/audit', name: 'audit', component: AuditView, meta: { auth: true, title: 'Audit log' } },
        { path: '/more', name: 'more', component: MoreView, meta: { auth: true, title: 'More' } },
    ],
});

router.beforeEach((to) => {
    const signedIn = isLoggedIn();

    if (to.meta.auth && !signedIn) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.guest && signedIn) {
        return { name: 'dashboard' };
    }

    return true;
});

export default router;
