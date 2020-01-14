import Passwords from './views/Passwords'
import NotFound from './views/errors/NotFound'
import Dashboard from './views/Dashboard'
import Roles from './views/Roles'
import Role from "./views/Role";

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
        path: '/admin/roles',
        name: 'admin.roles',
        component: Roles,
    },
    {
        path: '/admin/role/:id',
        name: 'admin.role',
        component: Role,
    },
    {
        path: '*',
        component: NotFound,
    }
];

// Don't want the globally applied loading spinner on a route?
// add in the following meta field:

// meta: {
//     globalLoadState: false
// }
