import {Warehouse} from "@/Enums/Warehouse";
import {computed} from "vue";

export function dateToLocaleString(date: Date | string | null | undefined) {
    if (date === null || date === undefined) {
        return;
    }

    if (date instanceof Date) {
        return date.toLocaleDateString("de-DE", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        });
    }

    return new Date(date).toLocaleDateString("de-DE", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
}

export function enumToOptions(
    enumObject: any,
    capitalize: boolean = false
): { name: string; value: number }[] {
    const options: { name: string; value: number }[] = [];

    for (const key in enumObject) {
        if (typeof enumObject[key] === "number") {
            options.push({
                name: capitalize ? capitalizeFirstLetter(key) : key,
                value: enumObject[key],
            });
        }
    }

    return options;
}

export function replaceEnumUnderscores(
    string: string | { [key: string]: number } | undefined
) {
    if (typeof string === "string") {
        return string.replace(/_/g, " ");
    }

    if (typeof string === "undefined") {
        return "";
    }

    const keys = Object.keys(string);
    return keys.map((key) => key.replace(/_/g, " ")).join(" ");
}

export function replaceUnderscores(object: { [key: string]: any }) {
    const newObject: { [key: string]: any } = {};

    for (let key in object) {
        const newKey = key.replace(/_/g, " ");
        newObject[newKey] = object[key];
    }

    return newObject;
}

export const downloadPdf = (path: string) => {
    const encodedPath = encodeURIComponent(path);
    location.replace(route("files.download", encodedPath as string));
}

export const today = computed(() => {
    const date = new Date();
    return date.toLocaleDateString('en-CA');
});

export const warehouses = Object.entries(Warehouse)
    .filter(([name]) => isNaN(Number(name)))
    .map(([name, value]) => ({
        name,
        value,
    }));

export function dateTimeToLocaleString(
    dateTime: Date | string | null
): string | undefined {
    if (dateTime === null) {
        return undefined;
    }

    let date: Date;

    if (dateTime instanceof Date) {
        date = dateTime;
    } else if (typeof dateTime === "string") {
        if (dateTime.includes("Z")) {
            date = new Date(dateTime);
        } else {
            const isoDateTime = new Date(dateTime + "Z");
            date = new Date(isoDateTime);
        }

        if (isNaN(date.getTime())) {
            console.error("Invalid date string:", dateTime);
            return undefined;
        }
    } else {
        console.error("Invalid input type:", typeof dateTime);
        return undefined;
    }

    return date.toLocaleDateString("de-DE", {
        hour: "2-digit",
        minute: "2-digit",
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
}

export function capitalizeFirstLetter(str: string): string {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

export function withFlash(only: Array<string> | undefined): string[] {
    return only ? [...only, "flash"] : [];
}

export function startsWith(
    inputString: string | undefined,
    searchString: string | string[]
): boolean {
    if (typeof inputString === "undefined") {
        return false;
    }

    if (Array.isArray(searchString)) {
        // If searchString is an array, check if inputString starts with any item in the array
        return searchString.some((str) => inputString.startsWith(str));
    } else {
        // If searchString is a single string, check if inputString starts with that string
        return inputString.startsWith(searchString);
    }
}

export function replacePlaceholder(
    inputString: string,
    replacement: number,
    placeholder: string = "?id"
): string {
    return inputString.replace(placeholder, replacement.toString());
}

export function findKeyByValue(
    data: { [key: string]: number } | null | undefined,
    id: null | number
): string | undefined {
    if (data === null || data === undefined) {
        return undefined;
    }

    const foundKey = Object.keys(data).find((key) => data[key] === id);
    return foundKey || undefined;
}

export function findEnumKeyByValue(
    searchEnum: any,
    value: number | null
): string | undefined {
    if (!value) {
        return undefined;
    }

    return searchEnum[value] ?? undefined;
}

type Callback = (...args: any[]) => void;

export function debounce(callback: Callback, delay = 300): Callback {
    let timer: ReturnType<typeof setTimeout> | undefined;

    return function (this: any, ...args: any[]) {
        if (timer) {
            clearTimeout(timer);
        }

        timer = setTimeout(() => {
            callback.apply(this, args);
        }, delay);
    };
}

export function isNotEmpty(value: Array<any> | object | unknown): boolean {
    return !isEmpty(value);
}

export function isEmpty(value: Array<any> | object | unknown): boolean {
    return Array.isArray(value)
        ? value.length === 0
        : !value || Object.keys(value as object).length === 0;
}