import { Ref } from "vue";
import { FilterValues } from "../types";

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

export function relationWithColumn(relationColumnArr: Array<string>): string {
    if (!Array.isArray(relationColumnArr)) {
        return relationColumnArr;
    }
    return relationColumnArr.join(".");
}

export const fillValues = (
    filterValues: Ref<any>,
    column: keyof any["filter"]["columns"],
    value: string
) => {
    const { filter } = filterValues.value;
    const { columns } = filter;

    filterValues.value = {
        filter: {
            columns: {
                ...columns,
                [column]: { [column]: value },
            },
        },
    };
};

export function getFilterColumnValuesFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);

    // Get all query parameters starting with 'filter[columns]'
    const filterColumnsParams = [...urlParams.keys()].filter((key) =>
        key.startsWith("filter[columns]")
    );

    // Create an object to store the parameter values
    const filterColumnsValues: Record<string, string> = {};

    // Iterate through the filterColumnsParams array and extract the last key and its value
    filterColumnsParams.forEach((key) => {
        const matches = key.match(/\[([^\[\]]+)\]$/); // Match the last key inside brackets
        if (matches && matches.length > 1) {
            const lastKey = matches[1];
            const value = urlParams.get(key);
            if (lastKey && value) {
                filterColumnsValues[lastKey] = value;
            }
        }
    });

    return filterColumnsValues;
}

export function camelCaseToSnakeCase(camelCaseString: string): string {
    return camelCaseString.replace(
        /[A-Z]/g,
        (match) => `_${match.toLowerCase()}`
    );
}

export const limitCharacters = (
    str: string | number | null | undefined,
    limit: number
) => {
    if (typeof str !== "string") {
        return str;
    }

    if (str.length > limit) {
        return str.substring(0, limit) + "...";
    }

    return str;
};
