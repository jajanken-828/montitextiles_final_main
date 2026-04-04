<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Warehouse,
    Plus,
    Trash2,
    X,
    Edit2,
    Save,
    Grid,
    Layers,
    Package,
    Box,
    ArrowRight,
    Send,
    MapPin,
    Shelf,
    AlertCircle,
} from 'lucide-vue-next';

const props = defineProps({
    warehouse: Object,
    sections: Array,
    unassignedStock: Array,
    auth: Object,
});

// ─── Layout Builder State ──────────────────────────────────────────────
const editMode = ref(false);
const rows = ref(3);
const cols = ref(3);
const sectionsList = ref(props.sections ? [...props.sections] : []);
const selectedSection = ref(null);
const newSectionName = ref('');
const showSectionModal = ref(false);
const showShelfModal = ref(false);
const editingSection = ref(null);
const editingShelfSection = ref(null);
const newShelfNumber = ref('');
const shelvesList = ref({}); // key: sectionId, value: array of shelf objects

// Initialize shelves from sections data
onMounted(() => {
    if (props.sections) {
        props.sections.forEach(section => {
            shelvesList.value[section.id] = section.shelves || [];
        });
    }
});

// Grid template
const gridTemplate = computed(() => {
    return {
        gridTemplateRows: `repeat(${rows.value}, minmax(100px, auto))`,
        gridTemplateColumns: `repeat(${cols.value}, minmax(120px, 1fr))`,
    };
});

// Generate grid cells
const gridCells = computed(() => {
    const cells = [];
    for (let r = 0; r < rows.value; r++) {
        for (let c = 0; c < cols.value; c++) {
            const existing = sectionsList.value.find(s => s.grid_row === r && s.grid_col === c);
            cells.push({ row: r, col: c, section: existing || null });
        }
    }
    return cells;
});

// Add/Edit Section Modal
const openAddSectionModal = (row, col) => {
    selectedSection.value = { row, col, section: null };
    newSectionName.value = '';
    showSectionModal.value = true;
};

const openEditSectionModal = (section) => {
    editingSection.value = section;
    newSectionName.value = section.name;
    showSectionModal.value = true;
};

const saveSection = () => {
    if (!newSectionName.value.trim()) return;
    if (editingSection.value) {
        // Update existing
        const idx = sectionsList.value.findIndex(s => s.id === editingSection.value.id);
        if (idx !== -1) sectionsList.value[idx].name = newSectionName.value;
        editingSection.value = null;
    } else if (selectedSection.value) {
        // Create new
        const newId = Date.now();
        sectionsList.value.push({
            id: newId,
            name: newSectionName.value,
            grid_row: selectedSection.value.row,
            grid_col: selectedSection.value.col,
            capacity: null,
            shelves: [],
        });
        shelvesList.value[newId] = [];
        selectedSection.value = null;
    }
    showSectionModal.value = false;
    newSectionName.value = '';
};

const deleteSection = (sectionId) => {
    if (!confirm('Remove this section? All shelves and stock assignments will be lost.')) return;
    sectionsList.value = sectionsList.value.filter(s => s.id !== sectionId);
    delete shelvesList.value[sectionId];
};

// Manage Shelves
const openManageShelves = (section) => {
    editingShelfSection.value = section;
    showShelfModal.value = true;
};

const addShelf = () => {
    if (!newShelfNumber.value.trim()) return;
    const newShelf = {
        id: Date.now(),
        shelf_number: newShelfNumber.value,
    };
    if (!shelvesList.value[editingShelfSection.value.id]) {
        shelvesList.value[editingShelfSection.value.id] = [];
    }
    shelvesList.value[editingShelfSection.value.id].push(newShelf);
    newShelfNumber.value = '';
};

const removeShelf = (sectionId, shelfId) => {
    shelvesList.value[sectionId] = shelvesList.value[sectionId].filter(s => s.id !== shelfId);
};

const closeShelfModal = () => {
    showShelfModal.value = false;
    editingShelfSection.value = null;
    newShelfNumber.value = '';
};

// Save layout to backend
const saveLayout = () => {
    const payload = {
        grid_rows: rows.value,
        grid_cols: cols.value,
        sections: sectionsList.value.map(s => ({
            id: s.id,
            name: s.name,
            row: s.grid_row,
            col: s.grid_col,
            capacity: s.capacity,
            shelves: shelvesList.value[s.id]?.map(sh => sh.shelf_number) || [],
        })),
    };
    router.post(route('warehouse.monitor.layout', props.warehouse.id), payload, {
        preserveScroll: true,
        onSuccess: () => {
            editMode.value = false;
        },
    });
};

// ─── Drag & Drop ───────────────────────────────────────────────────────
const dragStart = (event, stockItem) => {
    event.dataTransfer.setData('text/plain', JSON.stringify(stockItem));
    event.dataTransfer.effectAllowed = 'move';
};

const onDrop = (event, shelfId) => {
    event.preventDefault();
    const rawData = event.dataTransfer.getData('text/plain');
    if (!rawData) return;
    const stockItem = JSON.parse(rawData);
    // Assign stock item to this shelf
    router.post(route('warehouse.monitor.assign'), {
        stock_item_id: stockItem.id,
        shelf_id: shelfId,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Refresh page or update local state (we'll rely on Inertia reload)
        },
    });
};

const allowDrop = (event) => {
    event.preventDefault();
};

// ─── Use Material Modal ────────────────────────────────────────────────
const showUseModal = ref(false);
const selectedStockItem = ref(null);
const useForm = useForm({
    quantity: 0,
    manufacturing_department: '',
});

const openUseModal = (stockItem) => {
    selectedStockItem.value = stockItem;
    useForm.quantity = stockItem.quantity;
    useForm.manufacturing_department = '';
    showUseModal.value = true;
};

const submitUse = () => {
    if (!useForm.quantity || useForm.quantity <= 0) return;
    if (useForm.quantity > selectedStockItem.value.quantity) {
        alert('Quantity exceeds available stock.');
        return;
    }
    useForm.post(route('warehouse.monitor.use', selectedStockItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showUseModal.value = false;
            selectedStockItem.value = null;
            useForm.reset();
        },
    });
};

// Helper to get stock items on a shelf
const getStockOnShelf = (shelfId) => {
    // This should come from props. We'll assume each shelf object has a `stock_items` array from backend.
    // For now, we look through unassignedStock? Actually assigned stock is not in unassignedStock.
    // We'll rely on the data passed: each shelf should have stock_items in its object.
    // Since we rebuild shelves from props, we need to merge.
    const section = sectionsList.value.find(s => s.id === editingShelfSection.value?.id);
    if (!section) return [];
    const shelf = section.shelves?.find(sh => sh.id === shelfId);
    return shelf?.stock_items || [];
};

// Since props.sections already includes shelves and stock_items, we can directly use them in the grid.
// We'll display each shelf's stock items list.
</script>

<template>
    <Head :title="`Monitor: ${warehouse.name} | Monti Textile`" />
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                            Warehouse Monitor
                        </h1>
                        <p class="text-slate-500 text-sm mt-0.5">
                            {{ warehouse.name }} · {{ warehouse.location }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            v-if="!editMode"
                            @click="editMode = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-bold rounded-xl hover:opacity-80 transition shadow-sm"
                        >
                            <Edit2 class="w-4 h-4" />
                            Edit Layout
                        </button>
                        <button
                            v-if="editMode"
                            @click="saveLayout"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white text-sm font-bold rounded-xl hover:bg-emerald-700 transition shadow-sm"
                        >
                            <Save class="w-4 h-4" />
                            Save Layout
                        </button>
                        <button
                            v-if="editMode"
                            @click="editMode = false"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-600 text-white text-sm font-bold rounded-xl hover:bg-slate-700 transition"
                        >
                            Cancel
                        </button>
                    </div>
                </div>

                <!-- Grid Builder Controls (visible only in edit mode) -->
                <div v-if="editMode" class="mb-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <div class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Rows</label>
                            <input type="number" v-model.number="rows" min="1" max="8" class="mt-1 w-24 px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl" />
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Columns</label>
                            <input type="number" v-model.number="cols" min="1" max="8" class="mt-1 w-24 px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl" />
                        </div>
                        <div class="text-xs text-slate-500">Adjust grid size, then add/remove sections.</div>
                    </div>
                </div>

                <!-- Unassigned Stock Panel (drag source) -->
                <div v-if="unassignedStock.length > 0" class="mb-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white mb-3 flex items-center gap-2">
                        <Package class="w-4 h-4" /> Unassigned Stock
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <div
                            v-for="item in unassignedStock"
                            :key="item.id"
                            draggable="true"
                            @dragstart="dragStart($event, item)"
                            class="cursor-move bg-slate-100 dark:bg-slate-800 rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition inline-flex items-center gap-2"
                        >
                            <Package class="w-3.5 h-3.5 text-slate-400" />
                            {{ item.material.name }} ({{ item.quantity }} {{ item.unit }})
                            <span class="text-[10px] text-slate-400">{{ item.control_number }}</span>
                        </div>
                    </div>
                </div>

                <!-- CSS Grid Layout -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden p-4">
                    <div class="grid gap-3" :style="gridTemplate">
                        <div
                            v-for="cell in gridCells"
                            :key="`${cell.row}-${cell.col}`"
                            class="relative border border-slate-200 dark:border-slate-700 rounded-xl min-h-[120px] bg-slate-50 dark:bg-slate-800/30"
                        >
                            <!-- Section exists -->
                            <div v-if="cell.section" class="h-full flex flex-col p-2">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-black text-slate-600 dark:text-slate-300">{{ cell.section.name }}</span>
                                    <div v-if="editMode" class="flex gap-1">
                                        <button @click="openEditSectionModal(cell.section)" class="p-1 rounded text-slate-400 hover:text-blue-600"><Edit2 class="w-3 h-3" /></button>
                                        <button @click="deleteSection(cell.section.id)" class="p-1 rounded text-slate-400 hover:text-red-600"><Trash2 class="w-3 h-3" /></button>
                                        <button @click="openManageShelves(cell.section)" class="p-1 rounded text-slate-400 hover:text-emerald-600"><Shelf class="w-3 h-3" /></button>
                                    </div>
                                </div>
                                <!-- Shelves list -->
                                <div class="flex-1 overflow-y-auto space-y-2">
                                    <div
                                        v-for="shelf in (cell.section.shelves || [])"
                                        :key="shelf.id"
                                        @drop="onDrop($event, shelf.id)"
                                        @dragover="allowDrop"
                                        class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-2"
                                    >
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[10px] font-bold text-slate-500">Shelf {{ shelf.shelf_number }}</span>
                                            <button
                                                v-if="editMode"
                                                @click="removeShelf(cell.section.id, shelf.id)"
                                                class="text-red-400 hover:text-red-600"
                                            >
                                                <Trash2 class="w-3 h-3" />
                                            </button>
                                        </div>
                                        <!-- Stock items on this shelf -->
                                        <div v-if="shelf.stock_items && shelf.stock_items.length" class="space-y-1">
                                            <div v-for="stock in shelf.stock_items" :key="stock.id" class="flex items-center justify-between text-xs">
                                                <span class="truncate">{{ stock.material.name }}</span>
                                                <span class="font-mono">{{ stock.quantity }} {{ stock.unit }}</span>
                                                <button
                                                    @click="openUseModal(stock)"
                                                    class="text-emerald-600 hover:text-emerald-800 text-[10px] font-bold"
                                                >
                                                    Use
                                                </button>
                                            </div>
                                        </div>
                                        <div v-else class="text-[10px] text-slate-400 text-center py-1 italic">
                                            Drop stock here
                                        </div>
                                    </div>
                                    <div v-if="editMode && (!cell.section.shelves || cell.section.shelves.length === 0)" class="text-center text-[10px] text-slate-400 py-2">
                                        No shelves. Click shelf icon to add.
                                    </div>
                                </div>
                            </div>

                            <!-- Empty cell (add section button) -->
                            <div v-else class="h-full flex items-center justify-center">
                                <button
                                    v-if="editMode"
                                    @click="openAddSectionModal(cell.row, cell.col)"
                                    class="p-2 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-500 hover:bg-blue-100 hover:text-blue-600 transition"
                                >
                                    <Plus class="w-5 h-5" />
                                </button>
                                <span v-else class="text-[10px] text-slate-300">Empty</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Modal (Add/Edit) -->
        <Teleport to="body">
            <div v-if="showSectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showSectionModal = false">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-md p-6">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mb-4">{{ editingSection ? 'Edit Section' : 'New Section' }}</h3>
                    <input v-model="newSectionName" type="text" placeholder="Section name (e.g., A1, Yarn Storage)" class="w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20" />
                    <div class="mt-6 flex gap-3">
                        <button @click="showSectionModal = false" class="flex-1 py-2.5 text-sm font-bold rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Cancel</button>
                        <button @click="saveSection" class="flex-1 py-2.5 text-sm font-bold rounded-xl bg-blue-600 text-white hover:bg-blue-700 transition">Save</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Manage Shelves Modal -->
        <Teleport to="body">
            <div v-if="showShelfModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="closeShelfModal">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-md p-6">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mb-4">Shelves in {{ editingShelfSection?.name }}</h3>
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        <div v-for="shelf in (shelvesList[editingShelfSection?.id] || [])" :key="shelf.id" class="flex items-center justify-between p-2 bg-slate-50 dark:bg-slate-800 rounded-lg">
                            <span>{{ shelf.shelf_number }}</span>
                            <button @click="removeShelf(editingShelfSection.id, shelf.id)" class="text-red-500 hover:text-red-700"><Trash2 class="w-4 h-4" /></button>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <input v-model="newShelfNumber" type="text" placeholder="Shelf number (e.g., R1-S1)" class="flex-1 px-3 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl" />
                        <button @click="addShelf" class="px-4 py-2 bg-emerald-600 text-white rounded-xl font-bold text-sm">Add</button>
                    </div>
                    <div class="mt-6">
                        <button @click="closeShelfModal" class="w-full py-2.5 text-sm font-bold rounded-xl bg-slate-600 text-white hover:bg-slate-700 transition">Done</button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Use Material Modal -->
        <Teleport to="body">
            <div v-if="showUseModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showUseModal = false">
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-md p-6">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mb-2">Use Material</h3>
                    <p class="text-sm text-slate-500 mb-4">{{ selectedStockItem?.material?.name }} (Available: {{ selectedStockItem?.quantity }} {{ selectedStockItem?.unit }})</p>
                    <div class="space-y-4">
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Quantity to Use</label>
                            <input v-model="useForm.quantity" type="number" min="0" :max="selectedStockItem?.quantity" step="0.01" class="mt-1 w-full px-3 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl" />
                        </div>
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Manufacturing Department</label>
                            <select v-model="useForm.manufacturing_department" class="mt-1 w-full appearance-none pl-3 pr-8 py-2.5 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl">
                                <option value="">Select department...</option>
                                <option value="knitting">Knitting</option>
                                <option value="dyeing">Dyeing</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="packaging">Packaging</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-3">
                        <button @click="showUseModal = false" class="flex-1 py-2.5 text-sm font-bold rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Cancel</button>
                        <button @click="submitUse" :disabled="useForm.processing" class="flex-1 py-2.5 text-sm font-bold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 transition">Send to Production</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
.grid {
    display: grid;
}
</style>