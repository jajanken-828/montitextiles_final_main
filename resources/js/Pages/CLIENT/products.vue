<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import {
    ShoppingBag, Plus, X, ChevronDown, MapPin, Building2, Phone, Mail,
    DollarSign, Truck, Calendar, FileText, Package, CheckCircle, AlertCircle,
    Loader2, Heart, Star, Clock, Tag, CreditCard, Zap, Search, Filter,
    Layers, Scissors, Droplet, ArrowRight, Eye, RefreshCw, Send, MessageSquare
} from 'lucide-vue-next';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    client: {
        type: Object,
        default: () => ({})
    },
});

// UI State
const showModal = ref(false);
const showQuickView = ref(false);
const selectedProduct = ref(null);
const activeSection = ref('header');
const isLoading = ref(false);
const viewMode = ref('grid'); // grid or list
const notification = ref({ show: false, type: 'success', message: '' });

// Filtering & Search
const searchQuery = ref('');
const selectedCategory = ref('All');
const priceRange = ref(10000); // Max price slider

// Derived categories based on products
const categories = computed(() => {
    const cats = new Set(props.products.map(p => p.category).filter(Boolean));
    return ['All', ...Array.from(cats)];
});

// Filtered Products
const filteredProducts = computed(() => {
    return props.products.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (product.sku && product.sku.toLowerCase().includes(searchQuery.value.toLowerCase()));
        const matchesCategory = selectedCategory.value === 'All' || product.category === selectedCategory.value;
        const matchesPrice = (product.selling_price || 0) <= priceRange.value;

        return matchesSearch && matchesCategory && matchesPrice;
    });
});

// Featured product (for the hero section)
const featuredProduct = computed(() => {
    if (props.products.length > 0) {
        // Just picking the first one with an image for demonstration, or default to first
        return props.products.find(p => p.images && p.images.length > 0) || props.products[0];
    }
    return null;
});

// Form for quotation
const form = useForm({
    rfq_reference: `RFQ-MONTI-${new Date().getFullYear()}-${Math.floor(Math.random() * 10000)}`,
    valid_until: '',
    billing_address: '',
    shipping_address: '',
    lead_time: '15-30 Days',
    incoterms: 'EXW',
    shipping_method: 'Ocean Freight',
    payment_terms: '50% DP, 50% Before Shipment',
    shipping_cost: 0,
    tax: 0,
    currency: 'PHP',
    terms_conditions: 'Standard Monti Textile Manufacturing terms apply. Fabric weight tolerance ±5%. Color variation within standard commercial limits.',
    custom_notes: '',
    items: [],
});

// Open Quotation Modal
const openModal = (product = null) => {
    if (product) {
        selectedProduct.value = product;
        form.items = [{
            product_id: product.id,
            quantity: 100, // Default to a B2B volume
            unit_price: product.selling_price,
            discount: 0,
            technical_specs: 'Standard Grade',
        }];
    } else {
        form.items = [];
        addItem();
    }

    if (props.client) {
        form.billing_address = props.client.company_address || '';
        form.shipping_address = props.client.company_address || '';
    }

    activeSection.value = 'header';
    showModal.value = true;
};

// Open Quick View
const openQuickView = (product) => {
    selectedProduct.value = product;
    showQuickView.value = true;
};

// Line Item Management
const addItem = () => {
    form.items.push({
        product_id: null,
        quantity: 100,
        unit_price: 0,
        discount: 0,
        technical_specs: '',
    });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const updateItemPrice = (index) => {
    const item = form.items[index];
    const product = props.products.find(p => p.id === item.product_id);
    if (product) {
        item.unit_price = product.selling_price;
    }
};

// Submit Quotation
const submitQuotation = () => {
    if (!form.valid_until || !form.billing_address || !form.shipping_address || !form.payment_terms) {
        showNotification('error', 'Please fill in all required fields (*)');
        return;
    }

    if (form.items.length === 0 || !form.items[0].product_id) {
        showNotification('error', 'Please add at least one valid product');
        return;
    }

    isLoading.value = true;
    form.post(route('client.quotations.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            isLoading.value = false;
            showNotification('success', 'Quotation sent successfully to Monti Textile sales team!');
            form.reset();
        },
        onError: (errors) => {
            isLoading.value = false;
            const errorMsg = Object.values(errors)[0] || 'Failed to send quotation.';
            showNotification('error', errorMsg);
        }
    });
};

const showNotification = (type, message) => {
    notification.value = { show: true, type, message };
    setTimeout(() => notification.value.show = false, 4000);
};

// Calculations
const subtotal = computed(() => {
    return form.items.reduce((sum, item) => {
        const total = (item.quantity * item.unit_price) - (item.discount || 0);
        return sum + (isNaN(total) ? 0 : total);
    }, 0);
});

const grandTotal = computed(() => {
    return subtotal.value + (form.shipping_cost || 0) + (form.tax || 0);
});

// Formatting
const formatCurrency = (val) => '₱' + Number(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const stockStatus = (stock) => {
    if (stock <= 0) return { text: 'Out of Stock', class: 'text-rose-600 bg-rose-50 border-rose-200' };
    if (stock < 500) return { text: 'Low Volume', class: 'text-amber-600 bg-amber-50 border-amber-200' };
    return { text: 'High Volume Ready', class: 'text-emerald-600 bg-emerald-50 border-emerald-200' };
};

const scrollToSection = (section) => {
    activeSection.value = section;
    const element = document.getElementById(section);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};
</script>

<template>

    <Head title="Premium Fabric Catalog" />
    <AuthenticatedLayout>

        <Transition name="toast">
            <div v-if="notification.show"
                class="fixed top-6 right-6 z-[100] flex items-center gap-3 px-6 py-4 rounded-2xl shadow-2xl border backdrop-blur-md"
                :class="notification.type === 'success' ? 'bg-emerald-50/90 border-emerald-200 text-emerald-800' : 'bg-rose-50/90 border-rose-200 text-rose-800'">
                <component :is="notification.type === 'success' ? CheckCircle : AlertCircle"
                    class="w-6 h-6 flex-shrink-0" />
                <span class="font-medium">{{ notification.message }}</span>
                <button @click="notification.show = false" class="ml-4 opacity-50 hover:opacity-100 transition-opacity">
                    <X class="w-4 h-4" />
                </button>
            </div>
        </Transition>

        <div class="min-h-screen bg-[#F8F9FA] dark:bg-gray-950 pb-20">

            <div
                class="relative bg-gradient-to-br from-slate-900 to-indigo-950 overflow-hidden text-white pt-12 pb-24 px-4 sm:px-6 lg:px-8 shadow-inner">
                <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-20 pointer-events-none">
                    <div
                        class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob">
                    </div>
                    <div
                        class="absolute top-48 -left-24 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-2000">
                    </div>
                    <div
                        class="absolute -bottom-24 left-1/2 w-80 h-80 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob animation-delay-4000">
                    </div>
                    <div
                        class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiPgo8cmVjdCB3aWR0aD0iOCIgaGVpZ2h0PSI4IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMDUiLz4KPHBhdGggZD0iTTAgMEw4IDhaTTggMEwwIDhaIiBzdHJva2U9IiMwMDAiIHN0cm9rZS1vcGFjaXR5PSIwLjA1Ii8+Cjwvc3ZnPg==')] opacity-30">
                    </div>
                </div>

                <div
                    class="max-w-7xl mx-auto relative z-10 flex flex-col lg:flex-row items-center justify-between gap-12">

                    <div class="lg:w-1/2 space-y-8 text-center lg:text-left">
                        <div
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 backdrop-blur-md text-xs font-bold tracking-widest uppercase text-indigo-200">
                            <Layers class="w-4 h-4" />
                            Monti Textile B2B Portal
                        </div>
                        <h1 class="text-5xl lg:text-7xl font-black tracking-tight leading-[1.1]">
                            PREMIUM <br />
                            <span
                                class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-300 italic pr-2">FABRICS</span>
                        </h1>
                        <p class="text-lg text-indigo-100/80 max-w-xl mx-auto lg:mx-0 font-medium">
                            Source high-quality textiles directly from our manufacturing floor. Explore our catalog and
                            generate instant RFQs for bulk orders.
                        </p>
                        <div class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start">
                            <button @click="scrollToSection('catalog-grid')"
                                class="px-8 py-4 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl font-bold tracking-wide shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-1 flex items-center gap-2">
                                Browse Collection
                                <ArrowRight class="w-5 h-5" />
                            </button>
                            <button @click="openModal(null)"
                                class="px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/20 text-white rounded-xl font-bold tracking-wide backdrop-blur-md transition-all flex items-center gap-2">
                                <FileText class="w-5 h-5" /> Custom Request
                            </button>
                        </div>
                    </div>

                    <div v-if="featuredProduct" class="lg:w-1/2 relative hidden md:block">
                        <div class="relative w-full max-w-md mx-auto floating-element">
                            <div
                                class="absolute inset-0 bg-gradient-to-tr from-indigo-500 to-cyan-400 rounded-full filter blur-2xl opacity-40 scale-90">
                            </div>

                            <div
                                class="relative bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-2xl overflow-hidden">
                                <div
                                    class="absolute top-4 right-4 bg-rose-500 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wider z-20 shadow-lg">
                                    Top Seller
                                </div>
                                <div class="aspect-square rounded-2xl overflow-hidden bg-gray-100 mb-6 relative group">
                                    <img v-if="featuredProduct.images && featuredProduct.images[0]"
                                        :src="featuredProduct.images[0]" alt="Featured Fabric"
                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                                    <div v-else
                                        class="w-full h-full flex items-center justify-center bg-gray-800 text-gray-500">
                                        <Layers class="w-20 h-20" />
                                    </div>
                                    <div
                                        class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                                        <button @click="openQuickView(featuredProduct)"
                                            class="px-6 py-3 bg-white text-gray-900 rounded-full font-bold flex items-center gap-2 transform translate-y-4 group-hover:translate-y-0 transition-all">
                                            <Eye class="w-4 h-4" /> Inspect Weave
                                        </button>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <h3 class="text-2xl font-black text-white mb-1">{{ featuredProduct.name }}</h3>
                                    <p class="text-indigo-200 text-sm mb-4 font-mono">{{ featuredProduct.sku }}</p>
                                    <div
                                        class="flex justify-between items-center px-4 py-3 bg-white/10 rounded-xl border border-white/10">
                                        <span class="text-sm font-bold text-gray-300">Bulk Rate</span>
                                        <span class="text-xl font-black text-cyan-300">{{
                                            formatCurrency(featuredProduct.selling_price) }} /yd</span>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="absolute -right-8 top-1/4 bg-white text-gray-900 p-3 rounded-2xl shadow-xl flex items-center gap-3 animate-float-slow">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <Droplet class="w-5 h-5 text-indigo-600" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Property</p>
                                    <p class="text-sm font-black">Water Resistant</p>
                                </div>
                            </div>
                            <div
                                class="absolute -left-8 bottom-1/4 bg-white text-gray-900 p-3 rounded-2xl shadow-xl flex items-center gap-3 animate-float-delayed">
                                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                    <Zap class="w-5 h-5 text-emerald-600" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Availability</p>
                                    <p class="text-sm font-black">High Volume</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="catalog-grid" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-20">

                <div
                    class="bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-lg mb-8 flex flex-col md:flex-row gap-4 items-center justify-between">

                    <div class="relative w-full md:w-96 flex-shrink-0">
                        <Search class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
                        <input v-model="searchQuery" type="text" placeholder="Search fabrics by name or SKU..."
                            class="w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border-none rounded-xl focus:ring-2 focus:ring-indigo-500 text-sm font-medium transition-shadow" />
                    </div>

                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                        <div
                            class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 px-4 py-2 rounded-xl border border-gray-100 dark:border-gray-700">
                            <Filter class="w-4 h-4 text-gray-500" />
                            <select v-model="selectedCategory"
                                class="bg-transparent border-none text-sm font-medium focus:ring-0 cursor-pointer py-1 pr-8">
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>

                        <div class="hidden sm:flex items-center gap-2 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
                            <button @click="viewMode = 'grid'"
                                :class="viewMode === 'grid' ? 'bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                                class="p-2 rounded-lg transition-all">
                                <Layers class="w-5 h-5" />
                            </button>
                            <button @click="viewMode = 'list'"
                                :class="viewMode === 'list' ? 'bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                                class="p-2 rounded-lg transition-all">
                                <List class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="filteredProducts.length === 0"
                    class="text-center py-24 bg-white dark:bg-gray-900 rounded-3xl border border-dashed border-gray-300 dark:border-gray-700">
                    <Scissors class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No fabrics found</h3>
                    <p class="text-gray-500 max-w-sm mx-auto">We couldn't find any textiles matching your current
                        filters. Try adjusting your search criteria.</p>
                    <button @click="searchQuery = ''; selectedCategory = 'All'"
                        class="mt-6 text-indigo-600 font-bold hover:underline">Clear all filters</button>
                </div>

                <div v-else
                    :class="viewMode === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6' : 'flex flex-col gap-4'">

                    <div v-for="product in filteredProducts" :key="product.id"
                        class="group bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden hover:shadow-2xl hover:shadow-indigo-500/10 hover:-translate-y-1 transition-all duration-500 flex flex-col">

                        <div class="relative bg-gray-50 dark:bg-gray-800 overflow-hidden"
                            :class="viewMode === 'grid' ? 'h-64' : 'h-48 md:h-auto md:w-64 flex-shrink-0'">
                            <img v-if="product.images && product.images[0]" :src="product.images[0]" :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                            <div v-else
                                class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-100 dark:bg-gray-800">
                                <Layers class="h-12 w-12 opacity-50" />
                            </div>

                            <div
                                class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3 backdrop-blur-[2px]">
                                <button @click="openQuickView(product)"
                                    class="p-3 bg-white text-gray-900 rounded-full hover:bg-indigo-50 transition-colors transform translate-y-4 group-hover:translate-y-0 duration-300"
                                    title="Quick View">
                                    <Eye class="w-5 h-5" />
                                </button>
                                <button @click="openModal(product)"
                                    class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-full hover:bg-indigo-700 transition-colors transform translate-y-4 group-hover:translate-y-0 duration-300 delay-75 shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                                    <FileText class="w-4 h-4" /> Quote
                                </button>
                            </div>

                            <div class="absolute top-4 left-4 flex flex-col gap-2">
                                <span :class="stockStatus(product.stock_on_hand).class"
                                    class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-sm backdrop-blur-md">
                                    {{ stockStatus(product.stock_on_hand).text }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 flex flex-col flex-1"
                            :class="viewMode === 'list' ? 'md:flex-row md:items-center md:gap-8' : ''">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span
                                        class="font-mono text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded border border-gray-200 dark:border-gray-700">
                                        {{ product.sku }}
                                    </span>
                                    <span
                                        class="text-xs font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 px-2.5 py-1 rounded-full border border-indigo-100 dark:border-indigo-800">
                                        {{ product.category || 'Textile' }}
                                    </span>
                                </div>
                                <h3
                                    class="text-lg font-black text-gray-900 dark:text-white leading-tight group-hover:text-indigo-600 transition-colors mb-2">
                                    {{ product.name }}
                                </h3>
                                <p v-if="product.description"
                                    class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-4 leading-relaxed">
                                    {{ product.description }}
                                </p>

                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span v-if="product.width"
                                        class="flex items-center gap-1 text-[11px] font-bold text-gray-600 bg-gray-50 px-2 py-1 rounded">
                                        <ArrowsLeftRight class="w-3 h-3" /> {{ product.width }} Width
                                    </span>
                                    <span v-if="product.weight"
                                        class="flex items-center gap-1 text-[11px] font-bold text-gray-600 bg-gray-50 px-2 py-1 rounded">
                                        <Scale class="w-3 h-3" /> {{ product.weight }} GSM
                                    </span>
                                </div>
                            </div>

                            <div
                                :class="viewMode === 'grid' ? 'mt-auto pt-4 border-t border-gray-100 dark:border-gray-800' : 'md:text-right md:w-48'">
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Wholesale Price
                                </p>
                                <div class="flex items-baseline gap-1"
                                    :class="viewMode === 'list' ? 'md:justify-end' : ''">
                                    <span class="text-2xl font-black text-gray-900 dark:text-white">{{
                                        formatCurrency(product.selling_price) }}</span>
                                    <span class="text-sm text-gray-500 font-medium">/yard</span>
                                </div>

                                <button v-if="viewMode === 'list'" @click="openModal(product)"
                                    class="mt-4 w-full px-4 py-2.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-200 hover:border-indigo-600 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2">
                                    <Plus class="h-4 w-4" /> Add to Quote
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <Teleport to="body">
                <Transition name="fade">
                    <div v-if="showModal"
                        class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-gray-900/70 backdrop-blur-sm"
                        @click.self="showModal = false">
                        <div
                            class="bg-white dark:bg-gray-900 w-full max-w-6xl rounded-[2rem] shadow-2xl overflow-hidden max-h-[95vh] flex flex-col border border-gray-200 dark:border-gray-800 transform transition-all">

                            <div
                                class="px-8 py-5 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 flex justify-between items-center z-10 sticky top-0">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 rounded-2xl flex items-center justify-center border border-indigo-100 dark:border-indigo-800">
                                        <FileText class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                                    </div>
                                    <div>
                                        <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">RFQ
                                            Builder</h2>
                                        <p class="text-sm text-gray-500 font-medium">Monti Textile Manufacturing
                                            Corporation</p>
                                    </div>
                                </div>
                                <button @click="showModal = false"
                                    class="p-2.5 bg-gray-50 hover:bg-rose-50 text-gray-500 hover:text-rose-600 rounded-xl transition-colors">
                                    <X class="h-5 w-5" />
                                </button>
                            </div>

                            <div
                                class="flex-1 overflow-hidden flex flex-col lg:flex-row bg-gray-50 dark:bg-gray-900/50">

                                <div class="flex-1 overflow-y-auto p-8 space-y-10 custom-scrollbar">
                                    <form @submit.prevent="submitQuotation" id="quotationForm" class="space-y-10">

                                        <div
                                            class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                                            <h3
                                                class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest mb-6 flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Request Details
                                            </h3>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                                <div>
                                                    <label
                                                        class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">RFQ
                                                        Reference</label>
                                                    <div class="relative">
                                                        <input v-model="form.rfq_reference" type="text" readonly
                                                            class="w-full pl-3 pr-10 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-600 font-mono text-sm focus:ring-0" />
                                                        <RefreshCw
                                                            class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Valid
                                                        Until <span class="text-rose-500">*</span></label>
                                                    <input v-model="form.valid_until" type="date" required
                                                        class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm" />
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Incoterms</label>
                                                    <select v-model="form.incoterms"
                                                        class="w-full px-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 shadow-sm">
                                                        <option>EXW (Ex Works)</option>
                                                        <option>FOB (Free on Board)</option>
                                                        <option>CIF (Cost, Insurance, Freight)</option>
                                                        <option>DDP (Delivered Duty Paid)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <div class="flex items-center justify-between mb-4">
                                                <h3
                                                    class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span> Fabric
                                                    Selection
                                                </h3>
                                                <button type="button" @click="addItem"
                                                    class="text-sm px-4 py-2 bg-indigo-50 text-indigo-700 font-bold rounded-lg hover:bg-indigo-100 transition-colors flex items-center gap-2">
                                                    <Plus class="w-4 h-4" /> Add Fabric
                                                </button>
                                            </div>

                                            <div class="space-y-4">
                                                <div v-for="(item, idx) in form.items" :key="idx"
                                                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm relative group transition-all hover:border-indigo-300">

                                                    <button type="button" @click="removeItem(idx)"
                                                        class="absolute -right-2 -top-2 bg-rose-100 text-rose-600 p-1.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity shadow-sm hover:bg-rose-500 hover:text-white">
                                                        <X class="h-4 w-4" />
                                                    </button>

                                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                                                        <div class="md:col-span-4">
                                                            <label
                                                                class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Select
                                                                Material</label>
                                                            <select v-model="item.product_id"
                                                                @change="updateItemPrice(idx)"
                                                                class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl font-medium focus:ring-2 focus:ring-indigo-500">
                                                                <option value="" disabled>Choose a fabric...</option>
                                                                <option v-for="p in products" :key="p.id" :value="p.id">
                                                                    {{ p.name }} ({{ p.sku }})</option>
                                                            </select>
                                                        </div>
                                                        <div class="md:col-span-2">
                                                            <label
                                                                class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Yardage
                                                                (Qty)</label>
                                                            <input v-model.number="item.quantity" type="number" min="1"
                                                                class="w-full px-3 py-2.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500" />
                                                        </div>
                                                        <div class="md:col-span-2">
                                                            <label
                                                                class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Rate/Yd
                                                                (₱)</label>
                                                            <input v-model.number="item.unit_price" type="number"
                                                                step="0.01"
                                                                class="w-full px-3 py-2.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500" />
                                                        </div>
                                                        <div class="md:col-span-4">
                                                            <label
                                                                class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Specs/Finishing</label>
                                                            <input v-model="item.technical_specs" type="text"
                                                                placeholder="e.g., Pre-shrunk, Anti-pilling"
                                                                class="w-full px-3 py-2.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div
                                                class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                                                <h3
                                                    class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                                                    <Building2 class="w-4 h-4 text-indigo-500" /> Billing
                                                </h3>
                                                <textarea v-model="form.billing_address" rows="3" required
                                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 resize-none"
                                                    placeholder="Corporate billing address..."></textarea>
                                            </div>
                                            <div
                                                class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
                                                <h3
                                                    class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                                                    <Truck class="w-4 h-4 text-indigo-500" /> Delivery
                                                </h3>
                                                <textarea v-model="form.shipping_address" rows="3" required
                                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 resize-none"
                                                    placeholder="Warehouse delivery address..."></textarea>
                                            </div>
                                        </div>

                                    </form>
                                </div>

                                <div
                                    class="w-full lg:w-80 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 p-8 flex flex-col justify-between">
                                    <div>
                                        <h3
                                            class="text-lg font-black text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                                            <DollarSign class="h-5 w-5 text-emerald-500" /> Cost Summary
                                        </h3>

                                        <div class="space-y-4 mb-6">
                                            <div
                                                class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-700">
                                                <span class="text-sm text-gray-500 font-medium">Material Subtotal</span>
                                                <span class="font-bold text-gray-900 dark:text-white">{{
                                                    formatCurrency(subtotal) }}</span>
                                            </div>

                                            <div class="space-y-3">
                                                <div>
                                                    <label
                                                        class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Est.
                                                        Logistics (₱)</label>
                                                    <input v-model.number="form.shipping_cost" type="number"
                                                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-right" />
                                                </div>
                                                <div>
                                                    <label
                                                        class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tax
                                                        Allocation (₱)</label>
                                                    <input v-model.number="form.tax" type="number"
                                                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-right" />
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="bg-indigo-50 dark:bg-indigo-900/20 p-5 rounded-2xl border border-indigo-100 dark:border-indigo-800/50 mb-6">
                                            <p
                                                class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-1">
                                                Estimated Total</p>
                                            <p
                                                class="text-3xl font-black text-indigo-900 dark:text-white tracking-tight">
                                                {{ formatCurrency(grandTotal) }}</p>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Payment
                                                Terms *</label>
                                            <input v-model="form.payment_terms" type="text" required
                                                class="w-full px-3 py-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm mb-4" />
                                        </div>
                                    </div>

                                    <button type="submit" form="quotationForm" :disabled="isLoading"
                                        class="w-full py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black uppercase tracking-wider text-sm transition-all shadow-xl shadow-indigo-600/20 flex items-center justify-center gap-2 disabled:opacity-70">
                                        <Loader2 v-if="isLoading" class="h-5 w-5 animate-spin" />
                                        <Send v-else class="h-5 w-5" />
                                        {{ isLoading ? 'Processing...' : 'Submit RFQ' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Typography Enhancements */
.font-black {
    font-weight: 900;
}

/* Truncation */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom Scrollbar for Modal */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #CBD5E1;
    border-radius: 20px;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #475569;
}

/* Floating Animations for Hero Section */
@keyframes float-slow {

    0%,
    100% {
        transform: translateY(0) rotate(0);
    }

    50% {
        transform: translateY(-15px) rotate(2deg);
    }
}

@keyframes float-delayed {

    0%,
    100% {
        transform: translateY(0) rotate(0);
    }

    50% {
        transform: translateY(-10px) rotate(-2deg);
    }
}

@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }

    33% {
        transform: translate(30px, -50px) scale(1.1);
    }

    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }

    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-float-slow {
    animation: float-slow 6s ease-in-out infinite;
}

.animate-float-delayed {
    animation: float-delayed 7s ease-in-out infinite 2s;
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

/* Base floating element wrapper */
.floating-element {
    animation: float-slow 8s ease-in-out infinite;
}

/* Vue Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
}

.toast-enter-active,
.toast-leave-active {
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(50px) scale(0.9);
}
</style>