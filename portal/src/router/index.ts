import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '~/stores/auth';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    // Activation (public)
    { path: '/activate/:user', component: () => import('~/pages/ActivateAccount.vue'), meta: { public: true } },

    // Vendor
    { path: '/vendor/login', component: () => import('~/pages/vendor/VendorLogin.vue'), meta: { public: true, role: null } },
    {
      path: '/vendor',
      component: () => import('~/layouts/VendorLayout.vue'),
      meta: { role: 'vendor' },
      children: [
        { path: '', redirect: '/vendor/orders' },
        { path: 'orders', component: () => import('~/pages/vendor/VendorOrders.vue') },
        { path: 'menu',   component: () => import('~/pages/vendor/VendorMenu.vue') },
      ],
    },

    // Admin
    { path: '/admin/login', component: () => import('~/pages/admin/AdminLogin.vue'), meta: { public: true, role: null } },
    {
      path: '/admin',
      component: () => import('~/layouts/AdminLayout.vue'),
      meta: { role: 'admin' },
      children: [
        { path: '',        component: () => import('~/pages/admin/AdminIndex.vue') },
        { path: 'vendors', component: () => import('~/pages/admin/AdminVendors.vue') },
        { path: 'orders',  component: () => import('~/pages/admin/AdminOrders.vue') },
        { path: 'orders/:id', component: () => import('~/pages/admin/AdminOrderDetail.vue') },
        { path: 'riders',  component: () => import('~/pages/admin/AdminRiders.vue') },
        { path: 'leads',   component: () => import('~/pages/admin/AdminLeads.vue') },
      ],
    },

    // Rider
    { path: '/rider/login', component: () => import('~/pages/rider/RiderLogin.vue'), meta: { public: true, role: null } },
    {
      path: '/rider',
      component: () => import('~/layouts/RiderLayout.vue'),
      meta: { role: 'rider' },
      children: [
        { path: '', redirect: '/rider/jobs' },
        { path: 'jobs', component: () => import('~/pages/rider/RiderJobs.vue') },
      ],
    },

    { path: '/:pathMatch(.*)*', redirect: '/vendor/login' },
  ],
});

router.beforeEach(async (to) => {
  const auth = useAuthStore();
  await auth.init();

  if (to.meta.public) return true;

  const requiredRole = to.meta.role as string | undefined;
  if (!auth.token) {
    const loginMap: Record<string, string> = { vendor: '/vendor/login', admin: '/admin/login', rider: '/rider/login' };
    return loginMap[requiredRole ?? ''] ?? '/vendor/login';
  }

  if (requiredRole && auth.user?.role !== requiredRole) {
    return `/${auth.user?.role}/login`;
  }

  return true;
});

export default router;
