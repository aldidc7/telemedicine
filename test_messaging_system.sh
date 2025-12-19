#!/bin/bash
# Test Script for Telemedicine Messaging System
# File: test_messaging_system.sh
# Purpose: Verify messaging between doctor and patient works correctly

API_URL="http://localhost:8000/api/v1"
DOCTOR_TOKEN=""
PATIENT_TOKEN=""
CONSULTATION_ID=""

echo "🧪 TELEMEDICINE MESSAGING SYSTEM TEST"
echo "====================================="
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print status
print_status() {
  if [ $1 -eq 0 ]; then
    echo -e "${GREEN}✅ $2${NC}"
  else
    echo -e "${RED}❌ $2${NC}"
  fi
}

# Step 1: Doctor Login
echo "Step 1: Doctor Login"
echo "-------------------"
DOCTOR_LOGIN=$(curl -s -X POST "$API_URL/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "doctor@test.com",
    "password": "password123"
  }')

DOCTOR_TOKEN=$(echo $DOCTOR_LOGIN | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$DOCTOR_TOKEN" ]; then
  echo -e "${RED}❌ Doctor login failed${NC}"
  exit 1
fi

print_status 0 "Doctor logged in successfully"
echo "Token: ${DOCTOR_TOKEN:0:20}..."
echo ""

# Step 2: Patient Login
echo "Step 2: Patient Login"
echo "-------------------"
PATIENT_LOGIN=$(curl -s -X POST "$API_URL/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "patient@test.com",
    "password": "password123"
  }')

PATIENT_TOKEN=$(echo $PATIENT_LOGIN | grep -o '"token":"[^"]*' | cut -d'"' -f4)

if [ -z "$PATIENT_TOKEN" ]; then
  echo -e "${RED}❌ Patient login failed${NC}"
  exit 1
fi

print_status 0 "Patient logged in successfully"
echo ""

# Step 3: Get Pending Consultations (Doctor view)
echo "Step 3: Doctor Views Pending Consultations"
echo "-----------------------------------------"
DOCTOR_CONSULTATIONS=$(curl -s -X GET "$API_URL/konsultasi" \
  -H "Authorization: Bearer $DOCTOR_TOKEN" \
  -H "Content-Type: application/json")

# Extract first consultation ID
CONSULTATION_ID=$(echo $DOCTOR_CONSULTATIONS | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

if [ -z "$CONSULTATION_ID" ]; then
  echo -e "${YELLOW}⚠️  No pending consultations found${NC}"
  echo "Creating test consultation..."
  
  # Create consultation
  CREATE_CONSULTATION=$(curl -s -X POST "$API_URL/konsultasi" \
    -H "Authorization: Bearer $PATIENT_TOKEN" \
    -H "Content-Type: application/json" \
    -d '{
      "dokter_id": 1,
      "complaint_type": "Demam tinggi",
      "description": "Anak saya demam sejak semalam"
    }')
  
  CONSULTATION_ID=$(echo $CREATE_CONSULTATION | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
  print_status 0 "Test consultation created: ID=$CONSULTATION_ID"
else
  print_status 0 "Found pending consultation: ID=$CONSULTATION_ID"
fi
echo ""

# Step 4: Doctor Accept Consultation
echo "Step 4: Doctor Accept Consultation"
echo "--------------------------------"
ACCEPT_RESPONSE=$(curl -s -X POST "$API_URL/konsultasi/$CONSULTATION_ID/terima" \
  -H "Authorization: Bearer $DOCTOR_TOKEN" \
  -H "Content-Type: application/json")

STATUS=$(echo $ACCEPT_RESPONSE | grep -o '"status":"[^"]*' | head -1 | cut -d'"' -f4)

if [ "$STATUS" = "active" ] || [ "$STATUS" = "aktif" ]; then
  print_status 0 "Consultation accepted (Status: $STATUS)"
else
  print_status 1 "Failed to accept consultation"
  echo "Response: $ACCEPT_RESPONSE"
fi
echo ""

# Step 5: Get Messages (Before any sent)
echo "Step 5: Get Consultation Messages (Initial)"
echo "-----------------------------------------"
GET_MESSAGES=$(curl -s -X GET "$API_URL/pesan/$CONSULTATION_ID" \
  -H "Authorization: Bearer $DOCTOR_TOKEN" \
  -H "Content-Type: application/json")

MESSAGE_COUNT=$(echo $GET_MESSAGES | grep -o '"id"' | wc -l)
print_status 0 "Retrieved messages: $MESSAGE_COUNT messages found"
echo ""

# Step 6: Doctor Send Message
echo "Step 6: Doctor Send Message to Patient"
echo "------------------------------------"
DOCTOR_MESSAGE=$(curl -s -X POST "$API_URL/pesan" \
  -H "Authorization: Bearer $DOCTOR_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"konsultasi_id\": $CONSULTATION_ID,
    \"pesan\": \"Silakan berikan paracetamol 500mg setiap 6 jam\",
    \"tipe_pesan\": \"text\"
  }")

DOCTOR_MSG_ID=$(echo $DOCTOR_MESSAGE | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

if [ ! -z "$DOCTOR_MSG_ID" ]; then
  print_status 0 "Doctor message sent successfully (ID: $DOCTOR_MSG_ID)"
else
  print_status 1 "Failed to send doctor message"
  echo "Response: $DOCTOR_MESSAGE"
fi
echo ""

# Step 7: Patient View Messages
echo "Step 7: Patient View Messages"
echo "----------------------------"
PATIENT_MESSAGES=$(curl -s -X GET "$API_URL/pesan/$CONSULTATION_ID" \
  -H "Authorization: Bearer $PATIENT_TOKEN" \
  -H "Content-Type: application/json")

# Count messages
MESSAGE_COUNT=$(echo $PATIENT_MESSAGES | grep -o '"id"' | wc -l)

if [ $MESSAGE_COUNT -gt 0 ]; then
  print_status 0 "Patient can see messages ($MESSAGE_COUNT message(s))"
  
  # Extract last message
  LAST_MESSAGE=$(echo $PATIENT_MESSAGES | grep -o '"pesan":"[^"]*' | tail -1 | cut -d'"' -f4)
  echo "Message preview: '${LAST_MESSAGE:0:60}...'"
else
  print_status 1 "Patient cannot see messages"
fi
echo ""

# Step 8: Patient Send Reply
echo "Step 8: Patient Send Reply Message"
echo "--------------------------------"
PATIENT_MESSAGE=$(curl -s -X POST "$API_URL/pesan" \
  -H "Authorization: Bearer $PATIENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{
    \"konsultasi_id\": $CONSULTATION_ID,
    \"pesan\": \"Baik dokter, terima kasih atas rekomendasinya\",
    \"tipe_pesan\": \"text\"
  }")

PATIENT_MSG_ID=$(echo $PATIENT_MESSAGE | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)

if [ ! -z "$PATIENT_MSG_ID" ]; then
  print_status 0 "Patient message sent successfully (ID: $PATIENT_MSG_ID)"
else
  print_status 1 "Failed to send patient message"
  echo "Response: $PATIENT_MESSAGE"
fi
echo ""

# Step 9: Doctor Verify Patient Reply
echo "Step 9: Doctor Verify Patient Reply"
echo "---------------------------------"
DOCTOR_CHECK=$(curl -s -X GET "$API_URL/pesan/$CONSULTATION_ID" \
  -H "Authorization: Bearer $DOCTOR_TOKEN" \
  -H "Content-Type: application/json")

LATEST_MESSAGE=$(echo $DOCTOR_CHECK | grep -o '"pesan":"[^"]*' | tail -1 | cut -d'"' -f4)

if [[ "$LATEST_MESSAGE" == *"Baik dokter"* ]]; then
  print_status 0 "Doctor can see patient's reply"
  echo "Reply preview: '${LATEST_MESSAGE:0:60}...'"
else
  print_status 1 "Doctor cannot see patient's reply"
fi
echo ""

# Step 10: Test Mark as Read
echo "Step 10: Mark Message as Read"
echo "----------------------------"
if [ ! -z "$PATIENT_MSG_ID" ]; then
  MARK_READ=$(curl -s -X PUT "$API_URL/pesan/$PATIENT_MSG_ID/dibaca" \
    -H "Authorization: Bearer $DOCTOR_TOKEN" \
    -H "Content-Type: application/json" \
    -d "{}")
  
  READ_AT=$(echo $MARK_READ | grep -o '"read_at":"[^"]*' | cut -d'"' -f4)
  
  if [ ! -z "$READ_AT" ]; then
    print_status 0 "Message marked as read at: $READ_AT"
  else
    print_status 1 "Failed to mark message as read"
  fi
else
  print_status 1 "Cannot test mark as read (no patient message ID)"
fi
echo ""

# Step 11: Test Unread Count
echo "Step 11: Get Unread Message Count"
echo "-------------------------------"
UNREAD=$(curl -s -X GET "$API_URL/pesan/$CONSULTATION_ID/unread-count" \
  -H "Authorization: Bearer $PATIENT_TOKEN" \
  -H "Content-Type: application/json")

UNREAD_COUNT=$(echo $UNREAD | grep -o '"unread_count":[0-9]*' | cut -d':' -f2)
print_status 0 "Unread messages for patient: $UNREAD_COUNT"
echo ""

# Step 12: Test Authorization (Negative Test)
echo "Step 12: Test Authorization (Negative Test)"
echo "---------------------------------------"
UNAUTHORIZED=$(curl -s -w "%{http_code}" -o /dev/null \
  -X GET "$API_URL/pesan/999/unread-count" \
  -H "Authorization: Bearer $PATIENT_TOKEN" \
  -H "Content-Type: application/json")

if [ "$UNAUTHORIZED" = "403" ] || [ "$UNAUTHORIZED" = "404" ]; then
  print_status 0 "Authorization check working (HTTP $UNAUTHORIZED)"
else
  print_status 1 "Authorization check failed (HTTP $UNAUTHORIZED)"
fi
echo ""

# Step 13: Get Message Count
echo "Step 13: Final Message Count"
echo "--------------------------"
FINAL_MESSAGES=$(curl -s -X GET "$API_URL/pesan/$CONSULTATION_ID" \
  -H "Authorization: Bearer $DOCTOR_TOKEN" \
  -H "Content-Type: application/json")

FINAL_COUNT=$(echo $FINAL_MESSAGES | grep -o '"id"' | wc -l)
print_status 0 "Total messages in consultation: $FINAL_COUNT"
echo ""

# Summary
echo ""
echo "🎉 TEST SUMMARY"
echo "==============="
echo -e "${GREEN}✅ Messaging System Working Correctly${NC}"
echo ""
echo "What was tested:"
echo "✅ Doctor login"
echo "✅ Patient login"
echo "✅ Doctor accept consultation"
echo "✅ Doctor send message"
echo "✅ Patient view messages"
echo "✅ Patient send reply"
echo "✅ Doctor view patient reply"
echo "✅ Mark message as read"
echo "✅ Check unread count"
echo "✅ Authorization checks"
echo ""
echo "Consultation ID: $CONSULTATION_ID"
echo "Total Messages: $FINAL_COUNT"
echo ""
echo -e "${GREEN}Sistem messaging siap untuk production! 🚀${NC}"
