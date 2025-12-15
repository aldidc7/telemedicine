/**
 * ============================================
 * PAGINATION UTILITY
 * ============================================
 * 
 * Efficient pagination dengan lazy loading support
 */

import { ref, computed } from 'vue'

export function usePagination(fetchFunction) {
  const currentPage = ref(1)
  const perPage = ref(15)
  const items = ref([])
  const total = ref(0)
  const isLoading = ref(false)
  const error = ref(null)

  const totalPages = computed(() => Math.ceil(total.value / perPage.value))

  const hasNextPage = computed(() => currentPage.value < totalPages.value)
  const hasPrevPage = computed(() => currentPage.value > 1)

  /**
   * Fetch page data
   */
  const fetchPage = async (page = 1) => {
    isLoading.value = true
    error.value = null

    try {
      const response = await fetchFunction({
        page,
        per_page: perPage.value,
      })

      items.value = response.data || []
      currentPage.value = page
      total.value = response.pagination?.total || 0
    } catch (err) {
      error.value = err.message || 'Failed to fetch data'
      items.value = []
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Go to next page
   */
  const nextPage = async () => {
    if (hasNextPage.value) {
      await fetchPage(currentPage.value + 1)
    }
  }

  /**
   * Go to previous page
   */
  const prevPage = async () => {
    if (hasPrevPage.value) {
      await fetchPage(currentPage.value - 1)
    }
  }

  /**
   * Go to specific page
   */
  const goToPage = async (page) => {
    if (page >= 1 && page <= totalPages.value) {
      await fetchPage(page)
    }
  }

  /**
   * Change items per page
   */
  const changePerPage = async (newPerPage) => {
    perPage.value = newPerPage
    await fetchPage(1)
  }

  return {
    // State
    items,
    currentPage,
    perPage,
    total,
    isLoading,
    error,

    // Computed
    totalPages,
    hasNextPage,
    hasPrevPage,

    // Methods
    fetchPage,
    nextPage,
    prevPage,
    goToPage,
    changePerPage,
  }
}
