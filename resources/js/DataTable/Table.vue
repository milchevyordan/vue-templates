<script setup lang="ts">
import { router } from "@inertiajs/vue3";

import GlobalSearch from "./Components/GlobalSearch.vue";
import Pagination from "./Components/Pagination.vue";
import TrashedRecords from "./Components/TrashedRecords.vue";
import IconTableArrowDown from "./Icons/TableArrowDown.vue";
import IconTableArrowUp from "./Icons/TableArrowUp.vue";
import { limitCharacters, relationWithColumn } from "./js/utils.js";
import { DataTable, ThisRoute } from "./types";

import IconRestore from "@/Icons/Restore.vue";
import { replacePlaceholder } from "@/utils";

const props = withDefaults(
    defineProps<{
        dataTable: DataTable<any>;
        propName?: string; // By default table will reload 'dataTable' prop
        globalSearch?: boolean;
        showTrashed?: boolean;
        advancedFilters?: boolean;
        selectedRowIndexes?: Array<string | number>;
        selectedRowColumn?: string;
        rowClickLink?: string;
        perPageOptions?: number[];
    }>(),
    {
        propName: "dataTable",
    }
);

const thisRoute = route() as ThisRoute;

const isTrashed = () => {
    return (
        !!thisRoute.params?.filter?.trashed &&
        thisRoute.params.filter.trashed === "true"
    );
};

let previousOrderingKey: string = null!;
let previousOrderingDirection: string = null!;

const isRowClickLinkSet: boolean = props.rowClickLink
    ? props.rowClickLink.includes("?id")
    : false;

// Extract initial global search filter value from query parameters

const getRelationData = (
    rowData: any,
    relationTable: string,
    relationColumn: string
) => {
    const table = rowData[relationTable];

    if (!table) {
        return "";
    }

    if (table !== undefined && table[relationColumn] !== undefined) {
        return table[relationColumn] === "" ? "" : table[relationColumn];
    }

    return "Undefined relation";
};

const handleRowClick = (event: MouseEvent, dataId: number) => {
    if (
        typeof props.rowClickLink !== "undefined" &&
        !isTrashed() &&
        isRowClickLinkSet &&
        ["DIV", "TD"].includes((event.target as HTMLElement).tagName)
    ) {
        router.get(replacePlaceholder(props.rowClickLink, dataId));
    }
};

const handleSort = async (isOrderable: boolean, key: string) => {
    if (!isOrderable) {
        return;
    }

    let orderingDirection =
        (thisRoute.params as any)?.ordering?.direction || "desc";

    if (key === previousOrderingKey) {
        orderingDirection =
            previousOrderingDirection === "asc" ? "desc" : "asc";
    }

    await new Promise((resolve, reject) => {
        router.reload({
            data: {
                ordering: {
                    key: key,
                    direction: orderingDirection,
                },
            },
            only: [props.propName],
            onSuccess: resolve,
            onError: reject,
        });
    });

    previousOrderingKey = key;
    previousOrderingDirection = orderingDirection;
};

const handleRestoreRecord = async (id: number) => {
    await new Promise((resolve, reject) => {
        router.reload({
            data: { restore_id: id },
            only: [props.propName],
            onSuccess: resolve,
            onError: reject,
        });
    });

    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.delete("restore_id"); // Remove the restore_id parameter

    // Use router.replace to update the URL without reloading the page
    router.get(currentUrl.toString());
};
</script>

<template>
    <!-- Filters  -->
    <div
        v-if="
            globalSearch || $slots.advancedFilters || $slots.additionalContent
        "
        class="bg-white dark:bg-gray-800 border border-[#E9E7E7] dark:border-gray-700 rounded-lg p-4 mb-4 sm:flex items-center justify-between shadow space-y-2 sm:space-y-0"
    >
        <!-- GlobalFilter -->
        <GlobalSearch
            v-if="globalSearch"
            :prop-name="propName"
        />
        <!-- / GlobalFilter -->

        <!-- Trashed records -->
        <TrashedRecords
            v-if="showTrashed"
            :prop-name="propName"
        />
        <!-- / Trashed records -->

        <!-- AdvancedFilters -->
        <div
            v-if="$slots.advancedFilters || $slots.additionalContent"
            class="sm:flex items-center gap-2"
        >
            <slot :name="'advancedFilters'" />
            <slot name="additionalContent" />
        </div>
        <!-- / AdvancedFilters -->
    </div>
    <!-- / Filters  -->

    <div class="table-container max-w-full overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs uppercase text-black dark:text-gray-200 bg-[#F0F0F0] dark:bg-gray-700">
            <tr>
                <th
                    v-for="(column, key) in dataTable.columns"
                    scope="col"
                    class="px-6 py-3 border-r dark:border-gray-600"
                    :class="
                            column.orderable
                                ? 'cursor-pointer'
                                : 'cursor-default'
                        "
                    @click="handleSort(column.orderable, String(key))"
                >
                    <div class="w-full flex items-center gap-1.5">
                        <span>{{ column.label }}</span>

                        <span v-if="column.orderable">
                                <IconTableArrowUp />
                                <IconTableArrowDown />
                            </span>
                    </div>
                </th>
            </tr>
            </thead>

            <tbody>
            <tr
                v-for="(rowData, rowIndex) in dataTable.data"
                v-if="dataTable.data.length > 0"
                :class="[
                        rowIndex !== dataTable.data.length - 1
                            ? 'border-b dark:border-gray-600'
                            : '',
                        selectedRowIndexes &&
                            selectedRowColumn &&
                            (selectedRowIndexes.includes(
                                Number(rowData[selectedRowColumn])
                            ) ||
                                selectedRowIndexes.includes(
                                    rowData[selectedRowColumn]
                                ))
                            ? 'bg-indigo-400 dark:bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-800',
                        { 'cursor-pointer': isRowClickLinkSet && !isTrashed() },
                    ]"
                @click="handleRowClick($event, rowData.id)"
            >
                <td
                    v-for="(column, key, index) in dataTable.columns"
                    class="whitespace-nowrap px-6 py-3.5"
                >
                    <div
                        v-if="
                                column.relation &&
                                    $slots[
                                        `cell(${relationWithColumn(
                                            column.relationWithColumn
                                        )})`
                                    ]
                            "
                    >
                        <slot
                            :name="`cell(${relationWithColumn(
                                    column.relationWithColumn
                                )})`"
                            :value="rowData[column.relation]"
                            :item="rowData"
                        />
                    </div>

                    <div
                        v-else-if="
                                column.relation &&
                                    !$slots[`cell(${String(key)})`]
                            "
                    >
                        {{
                            getRelationData(
                                rowData,
                                column.relation.relationTable,
                                column.relation.relationColumn
                            )
                        }}
                        <!-- {{
                        rowData[column.relationTable][column.relationColumn]
                    }} -->
                        <!-- {{ getNestedData(rowData, column.relation) }} -->
                    </div>

                    <div
                        v-if="
                                $slots[`cell(${String(key)})`] && !isTrashed()
                            "
                    >
                        <slot
                            :name="`cell(${String(key)})`"
                            :value="rowData[key]"
                            :item="rowData"
                        />
                    </div>

                    <div
                        v-else-if="
                                $slots[`cell(${String(key)})`] &&
                                    isTrashed() &&
                                    key !== 'action'
                            "
                    >
                        <slot
                            :name="`cell(${String(key)})`"
                            :value="rowData[key]"
                            :item="rowData"
                        />
                    </div>

                    <div
                        v-else-if="
                                $slots[`cell(${String(key)})`] &&
                                    isTrashed() &&
                                    key === 'action'
                            "
                    >
                        <button
                            class="border border-[#E9E7E7] rounded-md p-1 active:scale-90 transition bg-blue-100 text-blue-500 duration-300 ease-in-out hover:bg-blue-200 dark:border-gray-700 dark:bg-blue-900 dark:text-blue-300 dark:hover:bg-blue-800"
                            :title="'Restore'"
                            @click="handleRestoreRecord(rowData['id'])"
                        >
                            <IconRestore classes="w-4 h-4" />
                        </button>
                    </div>

                    <div v-else>
                        {{ limitCharacters(rowData[key], 35) }}
                    </div>
                </td>
            </tr>

            <tr v-else>
                <td
                    class="bg-white dark:bg-gray-800 text-center py-5 text-lg font-semibold text-gray-900 dark:text-gray-100"
                    :colspan="Object.keys(props.dataTable.columns).length"
                >
                    No found data
                </td>
            </tr>
            </tbody>

            <tfoot>
            <td
                class="bg-[#F0F0F0] dark:bg-gray-700"
                :colspan="Object.keys(props.dataTable.columns).length"
            >
                <Pagination
                    v-if="
                            dataTable.data.length > 1 ||
                                dataTable.paginator.currentPage > 1
                        "
                    :paginator="dataTable.paginator"
                    :prop-name="propName"
                    :per-page-options="perPageOptions"
                />
            </td>
            </tfoot>
        </table>
    </div>
</template>
