# 📊 DATA HANDLER TRANSPARENCY REPORT

**Purpose**: Disclose all third-parties who handle patient data  
**Requirement**: Transparency required per telemedicine regulations  
**Updated**: 2025

---

## 📋 Overview

This document lists all vendors and third-parties who have access to patient data in the Telemedicine Application. Users have the right to know who handles their health information.

---

## 🏢 Primary Service Providers

### 1. Cloud Infrastructure Provider

**Who**: [Your Cloud Provider - AWS, DigitalOcean, Linode, etc.]

**What They Access**:
- Database servers (all patient data)
- Backup systems
- Log files
- Application servers

**Why**:
- Host the web application
- Store medical records database
- Provide backup/disaster recovery
- Ensure uptime and performance

**Protections**:
- Data center security (physical)
- Database encryption (if enabled)
- Regular security updates
- Compliance certifications

**Agreement**: Data Processing Agreement (DPA) signed ☑️
**Data Location**: [Specify - e.g., AWS US-EAST-1]
**Jurisdiction**: [Country where data is stored]

**Contact**: [Provider support email]

---

### 2. Real-Time Messaging Service

**Provider**: Pusher

**What They Access**:
- Real-time chat messages (doctor-patient)
- Message metadata (timestamp, users)
- Channel subscription data
- NOT: Message history (Pusher doesn't store messages permanently)

**Why**:
- Enable real-time chat notifications
- Deliver messages instantly
- Provide presence indicators (online/offline status)

**Protections**:
- TLS encryption for all connections
- No permanent message storage
- IP whitelisting available
- SOC 2 Type II compliant

**Agreement**: Pusher Terms of Service + Data Processing Addendum  
**Data Location**: Pusher EU or US servers  
**Retention**: Messages not stored by Pusher (app determines retention)

**Contact**: support@pusher.com  
**Privacy**: https://pusher.com/legal/privacy

---

### 3. Email Service Provider

**Provider**: [Your Email Provider - SendGrid, Mailgun, AWS SES, etc.]

**What They Access**:
- Patient email addresses
- Notification emails (password reset, appointment reminders)
- Support communications
- NOT: Medical content (clinical information not sent via email)

**Why**:
- Send account notifications
- Password reset links
- Appointment reminders
- Support responses

**Protections**:
- SMTP security (TLS encrypted)
- No medical information in email bodies
- Secure credential storage
- Compliance certifications (SOC 2, etc.)

**Agreement**: Data Processing Agreement signed ☑️  
**Data Location**: [Specify region]

**Contact**: [Provider support]  
**Privacy**: [Provider privacy policy link]

---

### 4. Payment Gateway (If Applicable)

**Provider**: [e.g., Stripe, PayPal, 2Checkout]

**What They Access**:
- Payment information (card details - tokenized)
- Billing name and address
- Transaction history
- NOT: Medical information

**Why**:
- Process consultation payments
- Manage billing and invoicing
- Handle refunds and disputes

**Protections**:
- PCI DSS Level 1 compliance (highest standard)
- Payment card tokenization (never store full card)
- Encrypted transmission
- Fraud detection systems

**Agreement**: Payment Processor Agreement signed ☑️  
**PCI Compliance**: YES - Level 1 certified

**Contact**: [Provider support]  
**Privacy**: [Provider privacy policy]

---

## 👥 Internal Users with Data Access

### Patient Data Access by Role

| Role | Access Level | What They See | Authorization |
|------|---|---|---|
| **Patient (Self)** | Own data only | Own medical records, consultations, messages | User ownership |
| **Assigned Doctor** | Assigned patients only | Medical history, consultation notes, messages, prescriptions | Doctor-patient relationship |
| **Other Doctors** | None | Cannot see patient data | Policy-enforced |
| **Admin** | All data (limited) | Can view any record for compliance/support | Admin verification + audit log |
| **Support Staff** | Minimal | Name, email, phone only (for contact) | Support role + case-by-case |

### Internal Access Controls

**Authentication**:
- ✅ Login required (2FA recommended for admin)
- ✅ Role-based access control (RBAC)
- ✅ Session timeout: 30 minutes
- ✅ Activity logging

**Authorization**:
- ✅ Laravel Policy classes enforce permissions
- ✅ Database-level access restrictions
- ✅ Field-level visibility controls
- ✅ API endpoint authorization checks

**Monitoring**:
- ✅ All access logged in audit_logs table
- ✅ Unusual access patterns trigger alerts
- ✅ Logs retained 7 years minimum
- ✅ Logs cannot be modified/deleted

---

## 🔐 Data Protection Standards

### What "Data Handler" Means

A data handler is any person or organization that:
- Has access to patient medical data
- Processes or stores the data
- Uses data for service delivery

**Your Rights**:
- Know who handles your data ✅ (This document)
- Know how they use it ✅ (Data usage policy)
- Ensure they protect it ✅ (Agreements signed)
- Limit who accesses it ✅ (Access controls)

---

## 📋 Data Processing Agreements (DPAs)

All third-party vendors who access patient data must sign a Data Processing Agreement (DPA).

**DPA Includes**:
- ✅ Purpose of data processing
- ✅ Duration of processing
- ✅ Nature and scope of processing
- ✅ Data subject categories
- ✅ Categories of personal data
- ✅ Security obligations
- ✅ Sub-processor disclosures
- ✅ Audit rights for compliance
- ✅ Data deletion/return procedures
- ✅ Breach notification obligations

**Status of Current Agreements**:

| Vendor | Service | DPA Signed | Last Updated |
|--------|---------|---|---|
| [Cloud Provider] | Infrastructure | ✅ | [Date] |
| Pusher | Real-time chat | ✅ | [Date] |
| [Email Provider] | Email delivery | ✅ | [Date] |
| [Payment Provider] | Payments | ✅ | [Date] |

---

## 🌍 International Data Transfers

**Where Your Data Goes**:

| Type | Location | Provider | Why |
|------|----------|----------|-----|
| Database | [Specify region] | [Cloud provider] | Application hosting |
| Backups | [Specify region] | [Cloud provider] | Disaster recovery |
| Messages | EU / US | Pusher | Real-time service |
| Emails | [Specify region] | [Email provider] | Notification delivery |
| Payments | [Specify region] | [Payment provider] | Payment processing |

**If Data Leaves Indonesia**:
- ⚠️ Only approved transfers to compliant countries
- ✅ Data Processing Agreements ensure protection
- ✅ Security measures equivalent to Indonesia standards
- ✅ User notified in Privacy Policy
- ✅ Encryption during transfer

---

## 🔒 Security Requirements for Data Handlers

All handlers must implement:

### Technical Safeguards
- ✅ Encryption for data in transit (TLS 1.2+)
- ✅ Encryption for data at rest (if storing)
- ✅ Access controls (authentication, authorization)
- ✅ Audit logging and monitoring
- ✅ Regular security updates
- ✅ Intrusion detection systems
- ✅ Backup and recovery procedures

### Organizational Safeguards
- ✅ Breach response plan
- ✅ Employee training on data protection
- ✅ Confidentiality agreements with staff
- ✅ Physical security of data centers
- ✅ Regular security audits
- ✅ Incident response procedures
- ✅ Business continuity plan

### Compliance
- ✅ Independent security certifications (SOC 2, ISO 27001, etc.)
- ✅ Compliance with applicable laws
- ✅ Regular compliance audits
- ✅ Breach notification capability
- ✅ Right of audit for client verification

---

## 📧 Sub-Processors

Some primary data handlers use sub-processors (companies that help them provide the service).

**Known Sub-Processors**:

| Primary Handler | Sub-Processor | Purpose |
|---|---|---|
| [Cloud Provider] | [CDN Provider] | Content delivery |
| [Cloud Provider] | [Monitoring Service] | Uptime monitoring |
| [Email Provider] | [Email Authentication] | DKIM/SPF verification |

**How We Ensure Sub-Processor Security**:
- ✅ Primary handler must list all sub-processors
- ✅ Primary handler assumes liability for sub-processor
- ✅ We have right to audit sub-processors
- ✅ Primary handler notifies us of sub-processor changes
- ✅ We can object to sub-processor changes
- ✅ Sub-processors must maintain same security standards

---

## 🚨 Breach Notification

If a data handler experiences a breach:

1. **Notification**: Vendor notifies us within 24 hours
2. **Investigation**: We assess impact
3. **Containment**: We take action to limit damage
4. **Communication**: We notify affected patients within 72 hours (if required)
5. **Documentation**: We document the incident for regulators

**What We Do**:
- ✅ Require vendor to provide detailed breach report
- ✅ Conduct forensic investigation
- ✅ Assess scope of exposure
- ✅ Determine notification requirements
- ✅ Provide patient notification and support
- ✅ Report to authorities (if required)

---

## 📞 Questions & Access Requests

### Questions About Data Handlers?

Email: **privacy@telemedicine-app.com**

We'll provide:
- ✅ Details about specific vendors
- ✅ Copies of Data Processing Agreements
- ✅ Information about data transfer locations
- ✅ Security measures being used
- ✅ Contact information for data handlers

**Response Time**: 5-10 business days

### Request to Know What Vendors Access Your Data?

Email: **privacy@telemedicine-app.com**

Include:
- Your email address
- Date range (optional)
- Specific type of data (optional)

We'll provide a report of:
- ✅ Vendor name
- ✅ What data they accessed
- ✅ When they accessed it
- ✅ Purpose of access
- ✅ Security certifications

---

## 🔄 When Data Handlers Change

**If We Change Vendors**:
1. ✅ We notify you beforehand (if practicable)
2. ✅ We ensure new vendor has same/better security
3. ✅ We execute Data Processing Agreement first
4. ✅ We ensure secure data transfer
5. ✅ We update this document

**Your Rights**:
- ✅ You can object to vendor change (where practical)
- ✅ We won't use lower security standards
- ✅ You have right to know about change
- ✅ You can request data deletion if unhappy

**How to Object**:
Email: **privacy@telemedicine-app.com** with subject "Vendor Objection"

---

## 📋 Vendor Certifications

All vendors should have security certifications proving their compliance:

| Certification | What It Means | Who Provides |
|---|---|---|
| **SOC 2 Type II** | Security audit by independent auditor | Audit firm |
| **ISO 27001** | Information security management system | International org |
| **PCI DSS Level 1** | Highest payment security standard | PCI Council |
| **HIPAA Compliant** | Health information security (US) | US HHS |
| **GDPR Compliant** | EU data protection compliance | EU |
| **ISO/IEC 27018** | Cloud privacy standard | International org |

**Current Vendor Certifications**:

| Vendor | Certifications | Link |
|--------|---|---|
| [Cloud Provider] | SOC 2 II, ISO 27001 | [URL] |
| Pusher | SOC 2 II | https://pusher.com/legal |
| [Email Provider] | SOC 2 II, ISO 27001 | [URL] |
| [Payment Provider] | PCI DSS Level 1 | [URL] |

---

## 🎓 Educational Resources

Learn more about data handling and your privacy rights:

- **How Data Protection Works**: [Link]
- **What is a Data Processing Agreement**: [Link]
- **Your Data Privacy Rights**: [Link]
- **Security Certifications Explained**: [Link]
- **How HTTPS Encryption Works**: [Link]

---

## ✅ Transparency Commitment

We are committed to:
- ✅ Keeping this document updated
- ✅ Answering all your data handling questions
- ✅ Being transparent about vendor changes
- ✅ Ensuring high security standards
- ✅ Respecting your privacy rights
- ✅ Following applicable laws

---

## 📝 Questions?

This document should answer most questions about who handles your data. If not:

**Privacy Questions**: privacy@telemedicine-app.com  
**Security Concerns**: security@telemedicine-app.com  
**Data Access Requests**: dpo@telemedicine-app.com

**Response Time**: 3-5 business days

---

**Version**: 1.0  
**Last Updated**: 2025  
**Next Review**: Quarterly (or when vendors change)  
**Contact**: privacy@telemedicine-app.com

