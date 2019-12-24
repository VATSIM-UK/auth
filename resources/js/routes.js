import Passwords from './views/Passwords'
import NotFound from './views/errors/NotFound'
import Dashboard from './views/Dashboard'
import VueRouter from "vue-router/types/router";

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'dashboard',
            component: Dashboard
        },
        {
            path: '/settings/password',
            name: 'settings.password',
            component: Passwords,
        },
        {
            path: '*',
            component: NotFound,
        }
    ],
});
