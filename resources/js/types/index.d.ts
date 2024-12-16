import {Warehouse} from "@/Enums/Warehouse";

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
        notificationsCount: number;
    };
};

export type FormMethod = {
    _method?: string;
};

export type Form = {
    [key: string]: any;
};

export type Enum<T> = T[keyof T];

export interface Option {
    name: string;
    value: string | number | null;
}

export interface SelectInput {
    name: string;
    value: number | number[];
}

export interface User {
    id: number;
    creator_id: number;
    name: string;
    email: string;
    warehouse: Enum<typeof Warehouse>;
    email_verified_at?: string;
    creator?: User;
}

export interface UserForm extends Omit<User, 'creator_id'>, Form, FormMethod {
}

export interface DatabaseImage {
    id: number;
    imageable_id: number;
    imageable_type: string;
    original_name: string;
    unique_name: string;
    path: string;
    order: number;
    size: number;
    created_at: Date;
    updated_at: Date;
}

export interface DeleteForm {
    id: number,
    name: string,
    created_at: Date,
}
