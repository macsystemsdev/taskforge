# Redis Integration

## Motivation

The previous synchronous architecture caused expensive operations such as email delivery to execute inside HTTP requests.

This increases response latency and does not scale.

Redis was introduced as the application's queue broker.

---

## Current Architecture

HTTP Request
→ Laravel Application
→ Redis Queue
→ Queue Worker
→ Background Job Execution

---

## Benefits

- Faster request completion
- Better user experience
- Improved scalability
- Foundation for future realtime systems
- Enables Horizon monitoring

---

## Current Queue Consumers

### Emails

- OrganizationInvitationMail

---

## Future Consumers

### Notifications

- Billing reminders
- Subscription events
- Task reminders

### Activity Processing

- Activity aggregation
- Analytics processing

### Reports

- Dashboard metrics
- Exports

### Realtime

- Reverb broadcasting jobs

---

## Infrastructure

Docker Redis container:

redis:7-alpine

Port:

6379

Queue Driver:

redis