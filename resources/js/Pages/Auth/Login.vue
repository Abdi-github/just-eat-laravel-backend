<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';
import Divider from 'primevue/divider';

const { t } = useI18n();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const demoAccounts = [
    { label: 'Super Admin',   email: 'admin@just-eat-clone.ch',   color: '#d32f2f' },
    { label: 'Support Agent', email: 'support@just-eat-clone.ch', color: '#1565c0' },
];

function fillDemoAccount(account: { email: string }) {
    form.email = account.email;
    form.password = 'password';
}

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900">
        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-500 rounded-full mb-4">
                    <i class="pi pi-utensils text-white text-2xl" />
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ t('auth.adminPanel') }}
                </h1>
                <p class="text-gray-500 mt-1">{{ t('auth.welcomeBack') }}</p>
            </div>

            <!-- Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email -->
                    <div class="flex flex-col gap-1">
                        <label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ t('auth.email') }}
                        </label>
                        <InputText
                            id="email"
                            v-model="form.email"
                            type="email"
                            :placeholder="t('auth.email')"
                            autocomplete="email"
                            :invalid="!!form.errors.email"
                            class="w-full"
                        />
                        <small v-if="form.errors.email" class="text-red-500 text-xs">
                            {{ form.errors.email }}
                        </small>
                    </div>

                    <!-- Password -->
                    <div class="flex flex-col gap-1">
                        <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ t('auth.password') }}
                        </label>
                        <Password
                            id="password"
                            v-model="form.password"
                            :placeholder="t('auth.password')"
                            :feedback="false"
                            toggle-mask
                            :invalid="!!form.errors.password"
                            input-class="w-full"
                            autocomplete="current-password"
                        />
                        <small v-if="form.errors.password" class="text-red-500 text-xs">
                            {{ form.errors.password }}
                        </small>
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center gap-2">
                        <Checkbox v-model="form.remember" binary input-id="remember" />
                        <label for="remember" class="text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                            {{ t('auth.remember') }}
                        </label>
                    </div>

                    <!-- Submit -->
                    <Button
                        type="submit"
                        :label="t('auth.signIn')"
                        icon="pi pi-sign-in"
                        :loading="form.processing"
                        class="w-full"
                        severity="warning"
                    />
                </form>

                <!-- Demo Accounts -->
                <Divider align="center">
                    <span class="text-xs text-gray-400 uppercase tracking-wider">Demo Accounts</span>
                </Divider>
                <div class="flex flex-wrap gap-2 justify-center">
                    <button
                        v-for="account in demoAccounts"
                        :key="account.email"
                        type="button"
                        @click="fillDemoAccount(account)"
                        :style="{ backgroundColor: account.color }"
                        class="px-3 py-1.5 rounded-lg text-white text-xs font-semibold hover:opacity-90 transition-opacity cursor-pointer"
                    >
                        {{ account.label }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
