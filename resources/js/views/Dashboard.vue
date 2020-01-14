<template>
    <div>
        <ul>
            <li>
                <router-link :to="{ name: 'settings.password' }">Settings / Passwords</router-link>
            </li>
            <li>
                <router-link :to="{ name: 'admin.roles' }">Admin / Roles & Permissions</router-link>
            </li>
        </ul>
        <p>
            This is the homepage!
        </p>
    </div>
</template>
