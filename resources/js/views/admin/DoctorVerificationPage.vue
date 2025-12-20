<!-- 📁 resources/js/views/admin/DoctorVerificationPage.vue -->
<template>
    <div class="min-h-screen bg-linear-to-br from-gray-50 to-gray-100 p-6">
        <!-- Loading Spinner -->
        <LoadingSpinner
            :isLoading="isLoading"
            message="Memuat data dokter..."
            type="default"
        />

        <!-- Header -->
        <div class="max-w-7xl mx-auto mb-10">
            <div class="flex items-center gap-3 mb-2">
                <svg
                    class="w-11 h-11 text-blue-600"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
                    />
                </svg>
                <h1 class="text-4xl font-bold text-gray-900">
                    Verifikasi Dokter
                </h1>
            </div>
            <p class="text-gray-600">
                Kelola verifikasi dokter dan review dokumen izin praktik
            </p>
        </div>

        <!-- Error Message -->
        <ErrorMessage
            v-if="errorMessage"
            :message="errorMessage"
            @close="errorMessage = null"
        />

        <!-- Stats Cards -->
        <div
            class="max-w-7xl mx-auto mb-8 grid grid-cols-1 md:grid-cols-4 gap-4"
        >
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">
                            Total Pending
                        </p>
                        <p class="text-3xl font-bold text-yellow-600 mt-1">
                            {{ stats.pending }}
                        </p>
                    </div>
                    <svg
                        class="w-12 h-12 text-yellow-200"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"
                        />
                    </svg>
                </div>
            </div>

            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">
                            Approved
                        </p>
                        <p class="text-3xl font-bold text-green-600 mt-1">
                            {{ stats.approved }}
                        </p>
                    </div>
                    <svg
                        class="w-12 h-12 text-green-200"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"
                        />
                    </svg>
                </div>
            </div>

            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">
                            Rejected
                        </p>
                        <p class="text-3xl font-bold text-red-600 mt-1">
                            {{ stats.rejected }}
                        </p>
                    </div>
                    <svg
                        class="w-12 h-12 text-red-200"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"
                        />
                    </svg>
                </div>
            </div>

            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">
                            Auto-Verified
                        </p>
                        <p class="text-3xl font-bold text-blue-600 mt-1">
                            {{ stats.auto_verified }}
                        </p>
                    </div>
                    <svg
                        class="w-12 h-12 text-blue-200"
                        fill="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
                        />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Filter & Search -->
        <div class="max-w-7xl mx-auto mb-8">
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
            >
                <div class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Status Filter</label
                        >
                        <select
                            v-model="filterStatus"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 transition text-gray-700"
                        >
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="auto_verified">Auto-Verified</option>
                        </select>
                    </div>
                    <button
                        @click="clearFilters"
                        class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium hover:bg-gray-200 transition"
                    >
                        Clear Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Document List -->
        <div class="max-w-7xl mx-auto">
            <div
                v-if="documents.length === 0"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center"
            >
                <svg
                    class="w-16 h-16 text-gray-300 mx-auto mb-4"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-8-6z"
                    />
                </svg>
                <p class="text-gray-500 text-lg">
                    Tidak ada dokumen untuk ditampilkan
                </p>
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="doc in documents"
                    :key="doc.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition"
                >
                    <div
                        class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4"
                    >
                        <!-- Document Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                                >
                                    <svg
                                        class="w-6 h-6 text-blue-600"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-8-6z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ doc.dokter?.user?.name }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        {{ doc.document_type }} ·
                                        {{ formatDate(doc.created_at) }}
                                    </p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-3">
                                <span class="font-medium">File:</span>
                                {{ doc.file_name }}
                            </p>
                            <div class="flex gap-2 flex-wrap">
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-xs font-medium',
                                        getStatusBadgeClass(doc.status),
                                    ]"
                                >
                                    {{ formatStatus(doc.status) }}
                                </span>
                                <span
                                    v-if="doc.verified_at"
                                    class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium"
                                >
                                    Verified: {{ formatDate(doc.verified_at) }}
                                </span>
                            </div>
                            <p
                                v-if="doc.rejection_reason"
                                class="text-sm text-red-600 mt-2"
                            >
                                Reason: {{ doc.rejection_reason }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="w-full md:w-auto flex flex-col gap-2">
                            <a
                                :href="doc.file_path"
                                target="_blank"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition text-center"
                            >
                                View Document
                            </a>
                            <button
                                v-if="doc.status === 'pending'"
                                @click="approveDocument(doc.id)"
                                :disabled="approvingId === doc.id"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition disabled:opacity-50"
                            >
                                {{
                                    approvingId === doc.id
                                        ? "Approving..."
                                        : "Approve"
                                }}
                            </button>
                            <button
                                v-if="doc.status === 'pending'"
                                @click="showRejectForm(doc.id)"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition"
                            >
                                Reject
                            </button>
                        </div>
                    </div>

                    <!-- Rejection Form -->
                    <div
                        v-if="rejectingId === doc.id"
                        class="mt-4 pt-4 border-t border-gray-200"
                    >
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Rejection Reason</label
                        >
                        <textarea
                            v-model="rejectionReason"
                            placeholder="Explain why this document is rejected..."
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:ring-red-500 transition"
                            rows="3"
                        ></textarea>
                        <div class="flex gap-2 mt-3">
                            <button
                                @click="submitReject(doc.id)"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition"
                            >
                                Confirm Reject
                            </button>
                            <button
                                @click="
                                    rejectingId = null;
                                    rejectionReason = '';
                                "
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";
import LoadingSpinner from "@/components/LoadingSpinner.vue";
import ErrorMessage from "@/components/ErrorMessage.vue";

const isLoading = ref(false);
const documents = ref([]);
const filterStatus = ref("");
const errorMessage = ref(null);
const approvingId = ref(null);
const rejectingId = ref(null);
const rejectionReason = ref("");

const stats = computed(() => ({
    pending: documents.value.filter((d) => d.status === "pending").length,
    approved: documents.value.filter((d) => d.status === "approved").length,
    rejected: documents.value.filter((d) => d.status === "rejected").length,
    auto_verified: documents.value.filter((d) => d.status === "auto_verified")
        .length,
}));

const filteredDocuments = computed(() => {
    if (!filterStatus.value) return documents.value;
    return documents.value.filter((d) => d.status === filterStatus.value);
});

onMounted(() => {
    loadDocuments();
});

const loadDocuments = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get("/api/doctor-verification-documents", {
            params: { status: filterStatus.value || undefined },
        });
        documents.value = response.data.data || [];
    } catch (error) {
        errorMessage.value = "Failed to load verification documents";
        console.error("Error loading documents:", error);
    } finally {
        isLoading.value = false;
    }
};

const approveDocument = async (docId) => {
    approvingId.value = docId;
    try {
        await axios.post(`/api/doctor-verification-documents/${docId}/approve`);
        await loadDocuments();
    } catch (error) {
        errorMessage.value = "Failed to approve document";
        console.error("Error approving:", error);
    } finally {
        approvingId.value = null;
    }
};

const showRejectForm = (docId) => {
    rejectingId.value = docId;
    rejectionReason.value = "";
};

const submitReject = async (docId) => {
    if (!rejectionReason.value.trim()) {
        errorMessage.value = "Please provide a rejection reason";
        return;
    }

    try {
        await axios.post(`/api/doctor-verification-documents/${docId}/reject`, {
            rejection_reason: rejectionReason.value,
        });
        await loadDocuments();
        rejectingId.value = null;
    } catch (error) {
        errorMessage.value = "Failed to reject document";
        console.error("Error rejecting:", error);
    }
};

const clearFilters = async () => {
    filterStatus.value = "";
    await loadDocuments();
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("id-ID", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatStatus = (status) => {
    const statuses = {
        pending: "Pending Review",
        approved: "Approved",
        rejected: "Rejected",
        auto_verified: "Auto-Verified",
    };
    return statuses[status] || status;
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: "bg-yellow-100 text-yellow-800",
        approved: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
        auto_verified: "bg-blue-100 text-blue-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

// Re-load when filter changes
watch(
    filterStatus,
    () => {
        loadDocuments();
    },
    { immediate: false }
);
</script>

<style scoped>
.loading-container {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
</style>
