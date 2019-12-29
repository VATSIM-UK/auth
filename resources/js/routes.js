import Passwords from './views/Passwords'
import NotFound from './views/errors/NotFound'
import Dashboard from './views/Dashboard'

export default [
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
];
