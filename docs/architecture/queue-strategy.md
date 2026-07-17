Queues Planned:

- default
- emails
- notifications
- billing
- activity
- reports
- exports

Goals:

- isolate heavy workloads
- prevent notification spam
- improve scalability
- enable future realtime features


# Worker Allocation Strategy

TaskForge separates queues by responsibility.

## Queue Priority

1. Emails
2. Notifications
3. Activities

Reason:

- Emails directly affect user trust.
- Notifications affect collaboration.
- Activities are internal auditing and may be delayed.

---

## Production Worker Allocation

### Small VPS (2GB)

| Queue | Workers | Memory |
|--------|----------|---------|
| emails | 2 | 256MB |
| notifications | 2 | 128MB |
| activities | 1 | 64MB |

Total Workers: 5

---

## Scaling Strategy

4GB VPS:

emails: 3
notifications: 3
activities: 2

8GB VPS:

emails: 5
notifications: 5
activities: 3

# Retry Policies

Emails:

tries: 5
backoff: [60,300,600]

Notifications:

tries: 3
backoff: [10,30,60]

Activities:

tries: 2
backoff: [30,60]

Future Queue Separation

billing
exports
reports
webhooks
search-indexing