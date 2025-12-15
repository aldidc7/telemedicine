/**
 * Bootstrap WebSocket Configuration
 * 
 * Initialize Echo/Pusher for real-time features
 */

import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Configure Pusher globally
window.Pusher = Pusher

// Note: Echo instance is created dynamically in useWebSocket composable
// This allows proper error handling and lazy initialization

// Log WebSocket availability
const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY
if (pusherKey) {
  console.log('✅ Pusher WebSocket configured')
  console.log('   App Key:', pusherKey)
  console.log('   Cluster:', import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1')
} else {
  console.warn('⚠️ Pusher App Key not configured. WebSocket disabled.')
  console.warn('   Add VITE_PUSHER_APP_KEY to your .env file')
}
