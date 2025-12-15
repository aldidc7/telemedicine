# 📬 Messaging System Documentation

## Overview

Messaging system memungkinkan komunikasi real-time antara Pasien dan Dokter dalam aplikasi telemedicine.

### Features:
- ✅ Create/get conversations antara 2 user
- ✅ Send messages dengan optional attachments
- ✅ Message read status tracking
- ✅ Unread message count
- ✅ Message history dengan pagination
- ✅ Delete conversations
- ✅ Search conversations

---

## Database Schema

### Conversations Table
```sql
- id (primary key)
- user_1_id (foreign key → users)
- user_2_id (foreign key → users)
- last_message_at (timestamp)
- last_message_preview (string)
- created_at, updated_at
```

### Messages Table
```sql
- id (primary key)
- conversation_id (foreign key → conversations)
- sender_id (foreign key → users)
- content (text)
- attachment_path (string, nullable)
- attachment_type (enum: image/file/document)
- read_at (timestamp, nullable)
- created_at, updated_at
```

---

## API Endpoints

### 1. GET /api/v1/messages/conversations
Ambil semua conversations untuk user yang login.

**Query Parameters:**
```
- search: string (cari berdasarkan nama user lain) - optional
- page: integer (default: 1)
- per_page: integer (default: 20, max: 50)
```

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Response:**
```json
{
  "success": true,
  "message": "Conversations berhasil diambil",
  "data": [
    {
      "id": 1,
      "other_user": {
        "id": 5,
        "name": "Dr. Budi Santoso",
        "role": "dokter",
        "avatar": null
      },
      "last_message_at": "2025-12-15T10:30:00Z",
      "last_message_preview": "Silakan ambil obat sesuai resep yang diberikan",
      "unread_count": 0
    }
  ],
  "pagination": {
    "total": 5,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

### 2. POST /api/v1/messages/conversations
Create conversation atau get existing conversation dengan user lain.

**Request Body:**
```json
{
  "with_user_id": 5
}
```

**Response:**
```json
{
  "success": true,
  "message": "Conversation berhasil dibuat",
  "data": {
    "id": 1,
    "other_user": {
      "id": 5,
      "name": "Dr. Budi Santoso",
      "role": "dokter"
    }
  }
}
```

---

### 3. GET /api/v1/messages/conversations/{id}
Ambil detail conversation.

**Response:**
```json
{
  "success": true,
  "message": "Conversation detail berhasil diambil",
  "data": {
    "id": 1,
    "other_user": {
      "id": 5,
      "name": "Dr. Budi Santoso",
      "role": "dokter",
      "avatar": null
    },
    "last_message_at": "2025-12-15T10:30:00Z",
    "created_at": "2025-12-14T15:20:00Z"
  }
}
```

---

### 4. GET /api/v1/messages/conversations/{id}/messages
Ambil messages dalam conversation dengan pagination (reverse order - terbaru di bawah).

**Query Parameters:**
```
- page: integer (default: 1)
- per_page: integer (default: 20, max: 100)
```

**Response:**
```json
{
  "success": true,
  "message": "Messages berhasil diambil",
  "data": [
    {
      "id": 1,
      "conversation_id": 1,
      "sender": {
        "id": 2,
        "name": "Ahmad Zaki",
        "role": "pasien"
      },
      "content": "Halo dokter, anak saya demam tinggi",
      "attachment_path": null,
      "attachment_type": null,
      "read_at": "2025-12-15T10:31:00Z",
      "created_at": "2025-12-15T10:30:00Z"
    },
    {
      "id": 2,
      "conversation_id": 1,
      "sender": {
        "id": 5,
        "name": "Dr. Budi Santoso",
        "role": "dokter"
      },
      "content": "Berapa suhu badannya? Sudah berapa hari?",
      "attachment_path": null,
      "attachment_type": null,
      "read_at": null,
      "created_at": "2025-12-15T10:31:30Z"
    }
  ],
  "pagination": {
    "total": 2,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

### 5. POST /api/v1/messages/conversations/{id}/send
Kirim message dalam conversation.

**Request Body:**
```json
{
  "content": "Suhunya 39 derajat, sudah 2 hari demam",
  "attachment_path": "uploads/photo.jpg",
  "attachment_type": "image"
}
```

**Required:**
- `content`: string (1-5000 characters)

**Optional:**
- `attachment_path`: string - path ke file storage
- `attachment_type`: enum (image/file/document)

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Message berhasil dikirim",
  "data": {
    "id": 2,
    "conversation_id": 1,
    "sender_id": 2,
    "sender": {
      "id": 2,
      "name": "Ahmad Zaki",
      "role": "pasien"
    },
    "content": "Suhunya 39 derajat, sudah 2 hari demam",
    "attachment_path": "uploads/photo.jpg",
    "attachment_type": "image",
    "read_at": null,
    "created_at": "2025-12-15T10:35:00Z",
    "updated_at": "2025-12-15T10:35:00Z"
  }
}
```

---

### 6. POST /api/v1/messages/conversations/{id}/read
Mark conversation sebagai sudah dibaca untuk user tertentu.

**Response:**
```json
{
  "success": true,
  "message": "Conversation ditandai sebagai dibaca",
  "data": null
}
```

---

### 7. GET /api/v1/messages/unread-count
Ambil total unread message count untuk user.

**Response:**
```json
{
  "success": true,
  "message": "Unread count berhasil diambil",
  "data": {
    "total_unread": 3
  }
}
```

---

### 8. DELETE /api/v1/messages/conversations/{id}
Delete conversation dan semua messages-nya.

**Response:**
```json
{
  "success": true,
  "message": "Conversation berhasil dihapus",
  "data": null
}
```

---

## Usage Examples

### Contoh 1: Pasien membuat conversation dengan dokter
```bash
# 1. Login pasien
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "pasien@example.com",
    "password": "Password123!"
  }'

# Response:
# {
#   "token": "eyJ0eXAi...",
#   "user": {
#     "id": 2,
#     "name": "Ahmad Zaki",
#     "role": "pasien"
#   }
# }

# 2. Cari dokter
curl http://127.0.0.1:8000/api/v1/dokter/search/advanced \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"

# 3. Create conversation dengan dokter
curl -X POST http://127.0.0.1:8000/api/v1/messages/conversations \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "with_user_id": 5
  }'

# Response:
# {
#   "data": {
#     "id": 1,
#     "other_user": {
#       "id": 5,
#       "name": "Dr. Budi Santoso",
#       "role": "dokter"
#     }
#   }
# }

# 4. Send message
curl -X POST http://127.0.0.1:8000/api/v1/messages/conversations/1/send \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Halo dokter, anak saya demam tinggi. Boleh konsultasi?"
  }'
```

### Contoh 2: Dokter menjawab message
```bash
# 1. Login dokter
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "dokter@example.com",
    "password": "Password123!"
  }'

# 2. Check unread messages
curl http://127.0.0.1:8000/api/v1/messages/unread-count \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"

# 3. Get conversations
curl http://127.0.0.1:8000/api/v1/messages/conversations \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"

# 4. Get messages dalam conversation
curl http://127.0.0.1:8000/api/v1/messages/conversations/1/messages \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"

# 5. Send reply
curl -X POST http://127.0.0.1:8000/api/v1/messages/conversations/1/send \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Baik, silakan deskripsi gejala dengan detail. Berapa suhu badannya saat ini?"
  }'

# 6. Mark conversation as read
curl -X POST http://127.0.0.1:8000/api/v1/messages/conversations/1/read \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

---

## Frontend Integration (Vue 3)

### Composable Example
```javascript
// composables/useMessaging.js
import { ref, computed } from 'vue'

export function useMessaging() {
  const conversations = ref([])
  const messages = ref([])
  const currentConversation = ref(null)
  const unreadCount = ref(0)
  const loading = ref(false)

  const API_URL = '/api/v1/messages'

  // Get conversations
  const fetchConversations = async () => {
    loading.value = true
    try {
      const response = await fetch(`${API_URL}/conversations`)
      const result = await response.json()
      conversations.value = result.data
    } finally {
      loading.value = false
    }
  }

  // Get messages dalam conversation
  const fetchMessages = async (conversationId) => {
    loading.value = true
    try {
      const response = await fetch(
        `${API_URL}/conversations/${conversationId}/messages`
      )
      const result = await response.json()
      messages.value = result.data
      currentConversation.value = conversationId
    } finally {
      loading.value = false
    }
  }

  // Send message
  const sendMessage = async (conversationId, content) => {
    try {
      const response = await fetch(
        `${API_URL}/conversations/${conversationId}/send`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ content })
        }
      )
      const result = await response.json()
      messages.value.push(result.data)
      return result.data
    } catch (error) {
      console.error('Error sending message:', error)
      throw error
    }
  }

  // Get unread count
  const fetchUnreadCount = async () => {
    try {
      const response = await fetch(`${API_URL}/unread-count`)
      const result = await response.json()
      unreadCount.value = result.data.total_unread
    } catch (error) {
      console.error('Error fetching unread count:', error)
    }
  }

  // Create conversation
  const createConversation = async (withUserId) => {
    try {
      const response = await fetch(`${API_URL}/conversations`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ with_user_id: withUserId })
      })
      const result = await response.json()
      return result.data
    } catch (error) {
      console.error('Error creating conversation:', error)
      throw error
    }
  }

  return {
    conversations,
    messages,
    currentConversation,
    unreadCount,
    loading,
    fetchConversations,
    fetchMessages,
    sendMessage,
    fetchUnreadCount,
    createConversation
  }
}
```

### Component Example
```vue
<template>
  <div class="messaging-container">
    <!-- Conversations List -->
    <div class="conversations-list">
      <div class="unread-badge" v-if="unreadCount > 0">
        {{ unreadCount }} pesan baru
      </div>
      
      <div 
        v-for="conv in conversations"
        :key="conv.id"
        class="conversation-item"
        @click="selectConversation(conv.id)"
        :class="{ active: currentConversation === conv.id }"
      >
        <div class="user-info">
          <h4>{{ conv.other_user.name }}</h4>
          <p class="last-message">{{ conv.last_message_preview }}</p>
        </div>
        <div class="meta">
          <span class="time">{{ formatTime(conv.last_message_at) }}</span>
          <span v-if="conv.unread_count > 0" class="unread-badge">
            {{ conv.unread_count }}
          </span>
        </div>
      </div>
    </div>

    <!-- Messages View -->
    <div class="messages-view" v-if="currentConversation">
      <!-- Messages -->
      <div class="messages">
        <div 
          v-for="message in messages"
          :key="message.id"
          class="message"
          :class="{ 'own-message': isOwnMessage(message) }"
        >
          <div class="message-content">
            <p class="sender" v-if="!isOwnMessage(message)">
              {{ message.sender.name }}
            </p>
            <p class="text">{{ message.content }}</p>
            <span class="time">{{ formatTime(message.created_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Message Input -->
      <div class="input-area">
        <input 
          v-model="newMessage"
          type="text"
          placeholder="Tulis pesan..."
          @keyup.enter="send"
        />
        <button @click="send" :disabled="!newMessage.trim()">
          Kirim
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useMessaging } from '@/composables/useMessaging'

const { 
  conversations, 
  messages, 
  currentConversation, 
  unreadCount,
  fetchConversations,
  fetchMessages,
  sendMessage,
  fetchUnreadCount
} = useMessaging()

const newMessage = ref('')

onMounted(() => {
  fetchConversations()
  fetchUnreadCount()
})

const selectConversation = async (convId) => {
  await fetchMessages(convId)
}

const send = async () => {
  if (!newMessage.value.trim()) return
  
  try {
    await sendMessage(currentConversation.value, newMessage.value)
    newMessage.value = ''
  } catch (error) {
    console.error('Error sending message:', error)
  }
}

const isOwnMessage = (message) => {
  // Compare dengan current user ID
  return message.sender_id === currentUser.id
}

const formatTime = (datetime) => {
  return new Date(datetime).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

<style scoped>
.messaging-container {
  display: flex;
  height: 600px;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
}

.conversations-list {
  width: 300px;
  border-right: 1px solid #ddd;
  overflow-y: auto;
}

.conversation-item {
  padding: 12px;
  border-bottom: 1px solid #eee;
  cursor: pointer;
  transition: background 0.2s;
}

.conversation-item:hover,
.conversation-item.active {
  background: #f5f5f5;
}

.messages-view {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.messages {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}

.message {
  margin-bottom: 12px;
  display: flex;
}

.message.own-message {
  justify-content: flex-end;
}

.message-content {
  background: #e3f2fd;
  padding: 8px 12px;
  border-radius: 8px;
  max-width: 70%;
}

.message.own-message .message-content {
  background: #4caf50;
  color: white;
}

.input-area {
  display: flex;
  gap: 8px;
  padding: 12px;
  border-top: 1px solid #ddd;
}

.input-area input {
  flex: 1;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.input-area button {
  padding: 8px 16px;
  background: #2196f3;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.unread-badge {
  background: #ff9800;
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
}
</style>
```

---

## Performance Notes

- Messages dipaginasi (max 100 per request)
- Conversations diindex oleh user IDs untuk quick lookup
- Unread messages ditrack dengan `read_at` timestamp
- Attachment paths disimpan untuk future media support

---

## Next Features

- Real-time messaging dengan WebSockets
- File upload handling
- Message search/filtering
- Group conversations
- Message reactions/emoji
- Typing indicators
- Video call integration

---

## Error Responses

### 403 Forbidden - User tidak termasuk dalam conversation
```json
{
  "success": false,
  "message": "User tidak termasuk dalam conversation ini",
  "code": 403
}
```

### 404 Not Found - Conversation/Message tidak ditemukan
```json
{
  "success": false,
  "message": "Conversation tidak ditemukan",
  "code": 404
}
```

### 422 Unprocessable Entity - Validation error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "content": ["Content field is required"]
  },
  "code": 422
}
```

---

## Testing

Gunakan `test_messaging.php` untuk quick test:
```bash
php test_messaging.php
```

Atau gunakan Postman/Insomnia dengan collection endpoints di atas.
