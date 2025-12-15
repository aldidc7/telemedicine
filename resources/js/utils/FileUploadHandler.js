/**
 * ============================================
 * FILE UPLOAD HANDLER
 * ============================================
 * 
 * Centralized file upload dengan validation
 */

import client from '@/api/client'
import { Validator } from '@/utils/Validation'

class FileUploadHandler {
  /**
   * Allowed file types
   */
  static ALLOWED_TYPES = {
    image: ['image/jpeg', 'image/png', 'image/webp'],
    document: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    audio: ['audio/mpeg', 'audio/wav', 'audio/ogg'],
  }

  /**
   * Max file sizes (in MB)
   */
  static MAX_SIZES = {
    image: 5,
    document: 10,
    audio: 20,
  }

  /**
   * Validate file
   */
  static validate(file, type = 'image') {
    // Check file size
    const maxSize = this.MAX_SIZES[type] || 5
    if (file.size > maxSize * 1024 * 1024) {
      return {
        valid: false,
        error: `File size exceeds ${maxSize}MB limit`,
      }
    }

    // Check file type
    const allowedTypes = this.ALLOWED_TYPES[type] || []
    if (!allowedTypes.includes(file.type)) {
      return {
        valid: false,
        error: `File type not allowed. Allowed types: ${allowedTypes.join(', ')}`,
      }
    }

    return { valid: true }
  }

  /**
   * Upload file
   */
  static async upload(file, endpoint, onProgress = null, type = 'image') {
    // Validate
    const validation = this.validate(file, type)
    if (!validation.valid) {
      throw new Error(validation.error)
    }

    // Create form data
    const formData = new FormData()
    formData.append('file', file)

    try {
      const response = await client.post(endpoint, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
        onUploadProgress: (progressEvent) => {
          if (onProgress) {
            const percentComplete = Math.round(
              (progressEvent.loaded * 100) / progressEvent.total
            )
            onProgress(percentComplete)
          }
        },
      })

      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.pesan || 'Upload failed')
    }
  }

  /**
   * Upload multiple files
   */
  static async uploadMultiple(files, endpoint, onProgress = null, type = 'image') {
    const results = []
    const total = files.length

    for (let i = 0; i < total; i++) {
      try {
        const result = await this.upload(
          files[i],
          endpoint,
          (percent) => {
            if (onProgress) {
              const overallPercent = Math.round(((i + percent / 100) / total) * 100)
              onProgress(overallPercent)
            }
          },
          type
        )
        results.push({ success: true, data: result })
      } catch (error) {
        results.push({ success: false, error: error.message })
      }
    }

    return results
  }

  /**
   * Create preview URL
   */
  static createPreview(file) {
    return URL.createObjectURL(file)
  }

  /**
   * Revoke preview URL
   */
  static revokePreview(url) {
    URL.revokeObjectURL(url)
  }
}

export default FileUploadHandler
