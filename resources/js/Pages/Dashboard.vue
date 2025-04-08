<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900">
                        <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard</h1>
                        <p class="text-lg text-gray-600">Bienvenido al Sistema de Gestión Académica.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
