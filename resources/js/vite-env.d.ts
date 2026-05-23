/// <reference types="vite/client" />

interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;
    readonly VITE_DEV_LOGIN_EMAIL?: string;
    readonly VITE_DEV_LOGIN_PASSWORD?: string;
    readonly VITE_DEV_LOGIN_REMEMBER?: string;
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
}
