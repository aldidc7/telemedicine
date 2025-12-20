// Composable untuk Loading States dan Error Handling
// Menyediakan standardized UI patterns untuk loading dan error states

import { ref, computed } from 'vue'

export function useLoading(initialState = false) {
  const isLoading = ref(initialState)

  const start = () => {
    isLoading.value = true
  }

  const finish = () => {
    isLoading.value = false
  }

  return {
    isLoading,
    start,
    finish,
  }
}

export function useError() {
  const errors = ref({})
  const errorMessage = ref(null)

  const setError = (field, message) => {
    if (typeof field === 'object') {
      errors.value = field
    } else {
      errors.value[field] = message
    }
  }

  const setErrorMessage = (message) => {
    errorMessage.value = message
  }

  const getError = (field) => {
    return errors.value[field] || null
  }

  const hasError = (field) => {
    return !!errors.value[field]
  }

  const clearError = (field) => {
    if (field) {
      delete errors.value[field]
    } else {
      errors.value = {}
      errorMessage.value = null
    }
  }

  const hasErrors = computed(() => {
    return Object.keys(errors.value).length > 0 || errorMessage.value !== null
  })

  return {
    errors,
    errorMessage,
    setError,
    setErrorMessage,
    getError,
    hasError,
    clearError,
    hasErrors,
  }
}

export function useAsync(asyncFunction) {
  const { isLoading, start, finish } = useLoading()
  const { errorMessage, setErrorMessage, clearError } = useError()
  const data = ref(null)
  const isError = ref(false)

  const execute = async (...args) => {
    try {
      start()
      clearError()
      isError.value = false
      data.value = await asyncFunction(...args)
      return data.value
    } catch (error) {
      isError.value = true
      const message = error.response?.data?.error?.message ||
                     error.response?.data?.message ||
                     error.message ||
                     'An error occurred'
      setErrorMessage(message)
      throw error
    } finally {
      finish()
    }
  }

  return {
    data,
    isLoading,
    isError,
    errorMessage,
    execute,
  }
}

export function useForm(initialValues = {}) {
  const formData = ref({ ...initialValues })
  const { errors, setError, clearError, hasErrors } = useError()
  const { isLoading, start, finish } = useLoading()

  const setValue = (field, value) => {
    formData.value[field] = value
    if (errors.value[field]) {
      delete errors.value[field]
    }
  }

  const setValues = (values) => {
    Object.assign(formData.value, values)
  }

  const reset = () => {
    formData.value = { ...initialValues }
    clearError()
  }

  const validate = (validationRules) => {
    const newErrors = {}
    Object.entries(validationRules).forEach(([field, rules]) => {
      const value = formData.value[field]
      const ruleArray = Array.isArray(rules) ? rules : [rules]

      for (const rule of ruleArray) {
        const result = rule(value)
        if (result !== true) {
          newErrors[field] = result
          break
        }
      }
    })

    setError(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const submitAsync = async (asyncFunction, validationRules = {}) => {
    if (Object.keys(validationRules).length > 0) {
      if (!validate(validationRules)) {
        return
      }
    }

    try {
      start()
      const result = await asyncFunction(formData.value)
      return result
    } finally {
      finish()
    }
  }

  return {
    formData,
    errors,
    isLoading,
    hasErrors,
    setValue,
    setValues,
    reset,
    validate,
    setError,
    clearError,
    submitAsync,
  }
}

export function useNotification() {
  const notifications = ref([])

  const addNotification = (message, type = 'info', duration = 5000) => {
    const id = Date.now()
    const notification = {
      id,
      message,
      type, // success, error, warning, info
    }

    notifications.value.push(notification)

    if (duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, duration)
    }

    return id
  }

  const removeNotification = (id) => {
    notifications.value = notifications.value.filter(n => n.id !== id)
  }

  const success = (message, duration) => {
    return addNotification(message, 'success', duration)
  }

  const error = (message, duration) => {
    return addNotification(message, 'error', duration)
  }

  const warning = (message, duration) => {
    return addNotification(message, 'warning', duration)
  }

  const info = (message, duration) => {
    return addNotification(message, 'info', duration)
  }

  return {
    notifications,
    addNotification,
    removeNotification,
    success,
    error,
    warning,
    info,
  }
}

// Validation Rules
export const validationRules = {
  required: (message = 'This field is required') => (value) => {
    return (value && value.toString().trim() !== '') || message
  },

  email: (message = 'Invalid email address') => (value) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return !value || emailRegex.test(value) || message
  },

  minLength: (min, message = `Minimum ${min} characters`) => (value) => {
    return !value || value.length >= min || message
  },

  maxLength: (max, message = `Maximum ${max} characters`) => (value) => {
    return !value || value.length <= max || message
  },

  pattern: (regex, message = 'Invalid format') => (value) => {
    return !value || regex.test(value) || message
  },

  match: (fieldValue, message = 'Fields do not match') => (value) => {
    return value === fieldValue || message
  },

  custom: (validatorFunction) => validatorFunction,
}
