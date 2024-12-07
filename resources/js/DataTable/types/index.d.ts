export type Multiselect<T> = Record<string, number>;

export interface ColumnRelation {
    relation: string[];
    relationColumn: string;
    relationString: string;
    relationTable: string;
    relationWithColumn: string[];
    relationsArray: string[];
}

export interface Column {
    [key: string]: {
        exactMatch: boolean;
        label: string;
        orderable: boolean;
        relation: ColumnRelation;
        searchable: boolean;
    };
}

export interface FilterValues extends Record<string, unknown> {
    filter: {
        columns: {
            [key: string]: string;
        };
    };
}
interface LengthAwarePaginator {
    url(start: number): string;
    appends(key: string | string[], value?: string | null): this;
    fragment(fragment?: string | null): this | string | null;
    nextPageUrl(): string | null;
    previousPageUrl(): string | null;
    items(): any[];
    firstItem(): number | null;
    lastItem(): number | null;
    perPage(): number;
    currentPage(): number;
    hasPages(): boolean;
    hasMorePages(): boolean;
    path(): string | null;
    isEmpty(): boolean;
    isNotEmpty(): boolean;
    render(view?: string | null, data?: any[]): string;
    getUrlRange(start: number, end: number): string[];
    total(): number;
    lastPage(): number;
}

interface Paginator {
    paginator: LengthAwarePaginator;
    itemsLength: number;
    perPage: number;
    links: any[];
    currentPage: number;
    lastPage: number;
    lastPageUrl: string;
    pagesRange: number;
    paginationLastPage: number;
}

export interface DataTable<Data> {
    data: Data[];
    paginator: Paginator;
    paginationLinks: string[];
    paginationLinksRange: number;
    paginationService: Paginator;
    columns: Record<string, any>;
}

interface FilterParams {
    trashed?: string;
}

interface RouteParams {
    filter?: FilterParams;
}

interface ThisRoute {
    params: RouteParams;
    query?: Record<string, any>;
}

export interface RadioToggleInput {
    name: string;
    value: boolean;
}
